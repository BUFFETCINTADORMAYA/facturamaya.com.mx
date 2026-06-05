// JavaScript del administrador
jQuery(document).ready(function($) {
    console.log('Yaxtun ERP initialized');
    console.log('AJAX URL:', YaxtunERP.ajax_url);
    console.log('User Role:', YaxtunERP.user_role);
});

// Funciones globales
function mostrarNotificacion(mensaje, tipo = 'success') {
    const div = document.createElement('div');
    div.className = 'notificacion notificacion-' + tipo;
    div.textContent = mensaje;
    document.body.appendChild(div);
    
    setTimeout(() => {
        div.remove();
    }, 3000);
}

function confirmarAccion(mensaje) {
    return confirm(mensaje);
}