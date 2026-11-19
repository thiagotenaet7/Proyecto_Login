// auto.js - Validaciones compartidas y comportamiento UX

function isGmail(email){
    if(!email) return false;
    // Chequeo básico y dominio @gmail.com (acepta subdominios comunes también)
    const re = /^[A-Za-z0-9._%+-]+@gmail\.com$/i;
    return re.test(email.trim());
}

function strongPassword(pw){
    // mínimo 8, al menos 1 mayúscula, 1 minúscula, 1 número, 1 símbolo
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;
    return re.test(pw);
}

function sanitizeInput(value){
    // reemplaza caracteres potencialmente peligrosos en la UI (no sustituye al backend)
    return value.replace(/[<>]/g, '');
}

function showError(id, msg){
    const el = document.getElementById(id);
    if(el) el.textContent = msg;
}

function clearErrors(ids){
    ids.forEach(id => {
        const el = document.getElementById(id);
        if(el) el.textContent = '';
    });
}
