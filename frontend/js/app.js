/*
==================================================
ResumeIQ-X Express Application Bootstrap Layer
Production Grade App Configuration Engine
==================================================
*/

const express = require("express");

const cors = require("cors");

const helmet = require("helmet");

const morgan = require("morgan");

const path = require("path");
const authRoutes = require("./routes/authRoute");

app.use("/api/auth", authRoutes);

/*
==================================================
IMPORT ROUTES
==================================================
*/

const uploadRoutes = require("./routes/uploadRoute");

const jobRoutes = require("./routes/jobRoute");

const authRoutes = require("./routes/authRoute");


/*
==================================================
INITIALIZE APP
==================================================
*/

const app = express();


/*
==================================================
GLOBAL SECURITY MIDDLEWARE
==================================================
*/

app.use(

helmet()

);


/*
==================================================
ENABLE CORS
==================================================
*/

app.use(

cors({

origin:"*",

methods:["GET","POST","PUT","DELETE"],

allowedHeaders:["Content-Type","Authorization"]

})

);


/*
==================================================
REQUEST LOGGING ENGINE
==================================================
*/

app.use(

morgan("dev")

);


/*
==================================================
BODY PARSERS
==================================================
*/

app.use(

express.json({

limit:"10mb"

})

);


app.use(

express.urlencoded({

extended:true

})

);


/*
==================================================
STATIC FILE SERVING
Uploads directory exposure
==================================================
*/

app.use(

"/uploads",

express.static(

path.join(

__dirname,

"..",

"uploads"

)

)

);


/*
==================================================
API ROUTES REGISTRATION
==================================================
*/

app.use(

"/api",

uploadRoutes

);


app.use(

"/api",

jobRoutes

);


app.use(

"/api/auth",

authRoutes

);


/*
==================================================
SYSTEM HEALTH CHECK ROUTE
==================================================
*/

app.get(

"/",

(req,res)=>{

res.json({

service:

"ResumeIQ-X API Gateway",

status:

"ACTIVE",

version:

"1.0.0",

ai_pipeline:

"CONNECTED"

});

}

);


/*
==================================================
GLOBAL ERROR HANDLER
==================================================
*/

app.use(

(err,req,res,next)=>{

console.error(

"Server error:",

err.message

);


res.status(500).json({

error:

"Internal server error",

details:

err.message

});

}

);


/*
==================================================
EXPORT APPLICATION
==================================================
*/

module.exports = app;