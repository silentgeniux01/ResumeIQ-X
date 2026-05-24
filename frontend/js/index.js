/*
==================================================
ResumeIQ-X Landing Page Intelligence Controller
System Health Monitor + Smart Entry UI Engine
Production Grade Frontend Logic Layer
==================================================
*/


/* ============================================
CONFIGURATION
============================================ */

const API_HEALTH_ENDPOINT =
"http://localhost:5000/";



/* ============================================
DOM REFERENCES
============================================ */

const systemStatusIndicator =
document.getElementById("systemStatus");

const startAnalysisBtn =
document.getElementById("startAnalysisBtn");

const statusMessage =
document.getElementById("statusMessage");



/* ============================================
SYSTEM STATUS CHECK ENGINE
============================================ */

async function checkSystemHealth(){

try{

const response = await fetch(

API_HEALTH_ENDPOINT

);


if(!response.ok){

throw new Error();

}


/* ============================================
SYSTEM ONLINE STATE
============================================ */

systemStatusIndicator.innerText =

"🟢 AI Engine Online";

systemStatusIndicator.style.color =

"#16a34a";


if(startAnalysisBtn){

startAnalysisBtn.disabled = false;

}


statusMessage.innerText =

"System ready for resume intelligence analysis.";


}


catch(error){


/* ============================================
SYSTEM OFFLINE STATE
============================================ */

systemStatusIndicator.innerText =

"🔴 AI Engine Offline";

systemStatusIndicator.style.color =

"#dc2626";


if(startAnalysisBtn){

startAnalysisBtn.disabled = true;

}


statusMessage.innerText =

"Backend not detected. Start Node server first.";


}


}



/* ============================================
SMART CTA BUTTON HANDLER
============================================ */

function attachStartButtonHandler(){

if(!startAnalysisBtn) return;


startAnalysisBtn.addEventListener(

"click",

()=>{

window.location.href =

"upload_resume.html";

}

);

}



/* ============================================
AUTO REFRESH SYSTEM STATUS
Runs every 5 seconds
============================================ */

function startHealthMonitoring(){

checkSystemHealth();


setInterval(

checkSystemHealth,

5000

);

}



/* ============================================
INITIALIZATION
============================================ */

function initializeLandingPage(){

attachStartButtonHandler();

startHealthMonitoring();

}


initializeLandingPage();