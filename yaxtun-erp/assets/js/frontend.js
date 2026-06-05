// JavaScript del frontend
jQuery(document).ready(function($) {
    console.log('Yaxtun ERP Frontend loaded');
});

// Shortcode para panel de cliente
function iniciarSesionCliente() {
    const email = document.getElementById('cliente-email').value;
    const password = document.getElementById('cliente-password').value;
    
    if (!email || !password) {
        alert('Por favor completa todos los campos');
        return false;
    }
    
    return true;
}