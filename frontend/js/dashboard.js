/*
==================================================
ResumeIQ-X Candidate Dashboard Intelligence Engine
Production Grade Cognitive Rendering Controller
Backend Synced Version
==================================================
*/


/* ============================================
FETCH ANALYSIS FROM BACKEND API
============================================ */

async function fetchDashboardData(){

try{

const response = await fetch(

"backend_php/get_candidate_dashboard.php"

);

const data = await response.json();


/*
============================================
HANDLE WAIT STATES
============================================
*/

if(!data.status){

document.body.innerHTML =

`
<h2 style="text-align:center;margin-top:120px">

${data.message}

</h2>
`;

return null;

}


/*
============================================
STORE CACHE (LOCAL STORAGE)
============================================
*/

localStorage.setItem(

"resume_analysis",

JSON.stringify(data)

);


return data.analysis;

}


catch(error){

console.error("Dashboard API error:",error);

document.body.innerHTML =

"<h2 style='text-align:center;margin-top:100px'>Server connection failed</h2>";

return null;

}

}


/* ============================================
DOM REFERENCES
============================================ */

const resumeScoreEl =
document.getElementById("resumeScore");

const confidenceScoreEl =
document.getElementById("confidenceScore");

const readinessScoreEl =
document.getElementById("careerReadinessScore");

const talentScoreEl =
document.getElementById("talentScore");

const strengthBadgeEl =
document.getElementById("resumeStrengthBadge");

const domainTableEl =
document.getElementById("domainTable");

const recommendationTableEl =
document.getElementById("recommendationTable");

const gapContainerEl =
document.getElementById("skillGapContainer");

const executionTimeEl =
document.getElementById("executionTime");


/*
Cognition Layer DOM
(optional-safe rendering)
*/

const capabilityBox =
document.getElementById("capabilityVector");

const trajectoryBox =
document.getElementById("trajectoryPrediction");

const reasoningBox =
document.getElementById("reasoningSignals");

const latentSkillBox =
document.getElementById("latentSkills");


/* ============================================
UTILITY HELPERS
============================================ */

function safe(value,fallback="Not available"){

if(value===undefined || value===null)
return fallback;

return value;

}


function percent(value){

if(value===undefined || value===null)
return "0%";

return Math.round(value*100)+"%";

}


function createGapBadge(text){

return `<span class="gap-badge">${text}</span>`;

}


function renderJSON(container,data){

if(!container) return;

if(!data){

container.innerHTML="Not available";

return;

}

container.innerHTML =
`<pre>${JSON.stringify(data,null,2)}</pre>`;

}


/* ============================================
RENDER FUNCTIONS
============================================ */

function renderResumeScore(analysis){

resumeScoreEl.innerText =
safe(analysis.resume_score);

strengthBadgeEl.innerText =
safe(analysis.resume_strength);

}


function renderConfidenceScore(analysis){

confidenceScoreEl.innerText =

analysis.confidence_score
? percent(analysis.confidence_score)
: "Not available";

}


function renderCareerReadiness(analysis){

readinessScoreEl.innerText =
safe(analysis.career_readiness_score);

}


function renderTalentScore(analysis){

const score =
analysis.talent_score?.talent_score;

talentScoreEl.innerText =

score
? score.toFixed(2)
: "Not available";

}


function renderDomainDistribution(analysis){

const domains =
analysis.domain_distribution;

if(!domains ||
Object.keys(domains).length===0){

domainTableEl.innerHTML =
"<tr><td colspan='2'>Not available</td></tr>";

return;

}

domainTableEl.innerHTML =

Object.entries(domains)

.map(

([domain,value]) =>

`
<tr>
<td>${domain}</td>
<td>${percent(value)}</td>
</tr>
`

)

.join("");

}


function renderRecommendations(analysis){

const roles =
analysis.job_recommendations?.top_recommendations;

if(!roles ||
!Array.isArray(roles) ||
roles.length===0){

recommendationTableEl.innerHTML =
"<tr><td colspan='2'>Not available</td></tr>";

return;

}

recommendationTableEl.innerHTML =

roles.map(role =>

`
<tr>
<td>${role.job_role}</td>
<td>${percent(role.match_score)}</td>
</tr>
`

).join("");

}


function renderSkillGap(analysis){

const missingSkills =
analysis.skill_gap_analysis?.missing_skills;

if(!missingSkills ||
!Array.isArray(missingSkills)){

gapContainerEl.innerHTML =
"<span class='role-ready'>Not available</span>";

return;

}

if(missingSkills.length===0){

gapContainerEl.innerHTML =
"<span class='role-ready'>Role Ready ✅</span>";

return;

}

gapContainerEl.innerHTML =

missingSkills.map(skill =>
createGapBadge(skill)
).join("");

}


function renderExecutionTime(analysis){

executionTimeEl.innerText =

analysis.execution_time_seconds
? analysis.execution_time_seconds+" sec"
: "Not available";

}


/*
==================================================
COGNITION LAYER RENDERERS
==================================================
*/

function renderCapabilityVector(analysis){

renderJSON(
capabilityBox,
analysis.capability_vector
);

}


function renderTrajectoryPrediction(analysis){

renderJSON(
trajectoryBox,
analysis.trajectory_prediction
);

}


function renderReasoningSignals(analysis){

renderJSON(
reasoningBox,
analysis.reasoning_signals
);

}


function renderLatentSkills(analysis){

renderJSON(
latentSkillBox,
analysis.latent_skill_report
);

}


/* ============================================
MAIN INITIALIZER
============================================ */

async function initializeDashboard(){

console.log(

"ResumeIQ-X Cognitive Dashboard Initializing..."

);

const analysis =
await fetchDashboardData();

if(!analysis) return;


/*
Primary metrics
*/

renderResumeScore(analysis);

renderConfidenceScore(analysis);

renderCareerReadiness(analysis);

renderTalentScore(analysis);

renderDomainDistribution(analysis);

renderRecommendations(analysis);

renderSkillGap(analysis);

renderExecutionTime(analysis);


/*
Cognition layer metrics
*/

renderCapabilityVector(analysis);

renderTrajectoryPrediction(analysis);

renderReasoningSignals(analysis);

renderLatentSkills(analysis);


console.log(

"ResumeIQ-X Dashboard Loaded Successfully 🚀"

);

}


initializeDashboard();