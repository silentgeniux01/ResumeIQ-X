"""
==================================================
ResumeIQ-X Persistent Embedding Store v3
Enterprise Hybrid Cache Engine
File Cache + DB Cache + Global Model Singleton
Ultra Low Latency Edition
==================================================
"""

import os
import json
import numpy as np
import mysql.connector
from threading import Lock
from sentence_transformers import SentenceTransformer


# ==================================================
# GLOBAL CONFIG
# ==================================================

MODEL_NAME = "sentence-transformers/all-MiniLM-L6-v2"

BASE_DIR = os.path.dirname(__file__)

EMBEDDING_DIR = os.path.join(BASE_DIR, "embeddings")

os.makedirs(EMBEDDING_DIR, exist_ok=True)


# ==================================================
# DATABASE CONFIG AUTO-DETECT
# ==================================================

DB_CONFIG = dict(

    host=os.getenv("MYSQLHOST", "localhost"),
    user=os.getenv("MYSQLUSER", "root"),
    password=os.getenv("MYSQLPASSWORD", ""),
    database=os.getenv("MYSQLDATABASE", "resumeiqx"),
    port=int(os.getenv("MYSQLPORT", 3306))

)


# ==================================================
# MODEL SINGLETON ENGINE
# ==================================================

MODEL = None
MODEL_LOCK = Lock()


def get_embedding_model():

    global MODEL

    if MODEL is None:

        with MODEL_LOCK:

            if MODEL is None:

                print("[EmbeddingStore] Loading embedding model once...")

                MODEL = SentenceTransformer(
                    MODEL_NAME,
                    device="cpu"
                )

                print("[EmbeddingStore] Model ready")

    return MODEL


# ==================================================
# DATABASE CONNECTION ENGINE
# ==================================================

def get_connection():

    return mysql.connector.connect(
        **DB_CONFIG,
        autocommit=True
    )


# ==================================================
# FILE CACHE PATH ENGINE
# ==================================================

def get_embedding_file(resume_id):

    return os.path.join(
        EMBEDDING_DIR,
        f"resume_{resume_id}.npy"
    )


# ==================================================
# FILE CACHE LOADER
# ==================================================

def load_from_file_cache(file_path):

    try:

        if os.path.exists(file_path):

            print("[EmbeddingStore] File cache hit")

            return np.load(file_path, mmap_mode="r")

    except Exception as e:

        print("[EmbeddingStore] File cache error:", e)

    return None


# ==================================================
# DATABASE CACHE LOADER
# ==================================================

def load_from_db_cache(resume_id):

    try:

        conn = get_connection()

        cursor = conn.cursor()

        cursor.execute(

            """
            SELECT embedding_vector
            FROM resume_embeddings
            WHERE resume_id=%s
            """,

            (resume_id,)

        )

        row = cursor.fetchone()

        cursor.close()
        conn.close()

        if row:

            print("[EmbeddingStore] DB cache hit")

            return np.array(json.loads(row[0]))

    except Exception as error:

        print("[EmbeddingStore] DB cache error:", error)

    return None


# ==================================================
# SAVE CACHE ENGINE
# ==================================================

def save_embedding(resume_id, embedding, file_path):

    try:

        np.save(file_path, embedding)

    except Exception as e:

        print("[EmbeddingStore] File save error:", e)


    try:

        conn = get_connection()

        cursor = conn.cursor()

        cursor.execute(

            """
            INSERT INTO resume_embeddings
            (resume_id, embedding_vector)

            VALUES (%s,%s)

            ON DUPLICATE KEY UPDATE
            embedding_vector=VALUES(embedding_vector)
            """,

            (

                resume_id,
                json.dumps(embedding.tolist())

            )

        )

        cursor.close()
        conn.close()

        print("[EmbeddingStore] DB cache saved")

    except Exception as e:

        print("[EmbeddingStore] DB save error:", e)


# ==================================================
# MAIN ENTRY ENGINE
# ==================================================

def get_or_create_embedding(resume_id, text):

    file_path = get_embedding_file(resume_id)


    # ----------------------------------------------
    # STEP 1: FILE CACHE
    # ----------------------------------------------

    embedding = load_from_file_cache(file_path)

    if embedding is not None:

        return embedding


    # ----------------------------------------------
    # STEP 2: DB CACHE
    # ----------------------------------------------

    embedding = load_from_db_cache(resume_id)

    if embedding is not None:

        save_embedding(resume_id, embedding, file_path)

        return embedding


    # ----------------------------------------------
    # STEP 3: COMPUTE EMBEDDING
    # ----------------------------------------------

    print("[EmbeddingStore] Computing embedding")

    model = get_embedding_model()

    embedding = model.encode(
        text,
        normalize_embeddings=True
    )


    # ----------------------------------------------
    # STEP 4: SAVE CACHE
    # ----------------------------------------------

    save_embedding(resume_id, embedding, file_path)

    return embedding