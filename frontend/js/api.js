/**
 * ResumeIQ-X — API URL helper
 * Resolves backend PHP paths relative to the project root,
 * regardless of folder name case or deployment environment.
 *
 * Works for:
 *   http://localhost/ResumeIQ-X/frontend/page.html
 *   http://localhost/resumeiq-x/frontend/page.html
 *   https://myapp.up.railway.app/frontend/page.html
 */
function apiUrl(script) {
    // Railway deployment - use absolute path
    if (window.location.hostname.includes('railway.app')) {
        return window.location.origin + '/backend_php/' + script;
    }
    
    // Local development - relative path
    // pathname example: /ResumeIQ-X/frontend/user_login.html
    const parts = window.location.pathname.split('/');
    parts.pop();       // remove filename  (user_login.html)
    parts.pop();       // remove 'frontend'
    const root = parts.join('/'); // e.g. /ResumeIQ-X  or  ''
    return window.location.origin + root + '/backend_php/' + script;
}
