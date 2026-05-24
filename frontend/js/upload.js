/*
==================================================
ResumeIQ-X Upload Intelligence Controller
Enterprise PHP Pipeline Upload Version
SESSION-SAFE BUILD (FIXED)
==================================================
*/


/* ============================================
DOM REFERENCES
============================================ */

const uploadForm = document.getElementById("uploadForm");

const fileInput = document.getElementById("resumeFile");

const fileNameDisplay = document.getElementById("fileName");

const statusText = document.getElementById("status");

const progressBar = document.querySelector(".progress");

const progressFill = document.getElementById("progressFill");


/* ============================================
CONFIGURATION
============================================ */

const API_ENDPOINT =
"../backend_php/upload_resume.php";

const MAX_FILE_SIZE_MB = 5;


/* ============================================
DISPLAY SELECTED FILE NAME
============================================ */

if(fileInput){

fileInput.addEventListener("change",()=>{

if(fileInput.files.length > 0){

fileNameDisplay.innerText =
"📄 Attached: " + fileInput.files[0].name;

}

});

}


/* ============================================
FILE VALIDATION ENGINE
============================================ */

function validateFile(file){

if(!file){

return {
valid:false,
message:"No file selected"
};

}

const allowedExtensions =
["txt","pdf","docx","png","jpg","jpeg"];

const extension =
file.name.split(".").pop().toLowerCase();

if(!allowedExtensions.includes(extension)){

return {
valid:false,
message:"Unsupported file format"
};

}

const fileSizeMB =
file.size / (1024 * 1024);

if(fileSizeMB > MAX_FILE_SIZE_MB){

return {
valid:false,
message:"File exceeds 5MB limit"
};

}

return { valid:true };

}


/* ============================================
PROGRESS BAR CONTROLLER
============================================ */

function updateProgress(value){

if(progressBar){

progressBar.style.display = "block";

}

if(progressFill){

progressFill.style.width = value + "%";

}

}


/* ============================================
STATUS MESSAGE CONTROLLER
============================================ */

function updateStatus(message){

if(statusText){

statusText.innerText = message;

}

}


/* ============================================
UPLOAD ENGINE (SESSION SAFE)
============================================ */

async function uploadResume(file){

try{

updateProgress(15);

updateStatus("Uploading resume...");


const formData = new FormData();

formData.append("resume", file);


updateProgress(35);


/*
==================================================
CRITICAL FIX HERE
SEND SESSION COOKIE WITH REQUEST
==================================================
*/

const response = await fetch(API_ENDPOINT,{
method:"POST",
body:formData,
credentials:"include"
});


updateProgress(60);


/*
==================================================
HANDLE AUTH FAILURE CLEANLY
==================================================
*/

if(response.status === 401){

throw new Error("Session expired. Please login again.");

}


const result = await response.json();


updateProgress(90);


if(!result.status){

throw new Error(result.message);

}


updateProgress(100);


updateStatus(
"✅ Resume uploaded successfully"
);


/*
==================================================
REDIRECT TO DASHBOARD
==================================================
*/

setTimeout(()=>{

window.location.href = "dashboard.php";

},1200);


}catch(error){

console.error(error);

if(progressBar){

progressBar.style.display = "none";

}

updateStatus(
"❌ Upload failed: " + error.message
);

}

}


/* ============================================
FORM SUBMIT HANDLER
============================================ */

if(uploadForm){

uploadForm.addEventListener("submit",function(event){

event.preventDefault();

const file = fileInput.files[0];

const validation = validateFile(file);

if(!validation.valid){

updateStatus("⚠ " + validation.message);

return;

}

uploadResume(file);

});

}