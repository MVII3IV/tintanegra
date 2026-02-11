// --- FUNCIONES AUXILIARES ---
function generarLinkWhatsApp(p) {
    if (!p.telefono) return '#'; 
    const telLimpio = p.telefono.replace(/\D/g, '');
    const host = window.location.hostname === 'localhost' ? 'http://localhost:8080' : 'https://www.tintanegra.mx';
    const urlPedido = `${host}/showOrder.php?id=${p.id}`;
    const saldo = (p.status === 'Entregada') ? 0 : (parseFloat(p.costo || 0) - parseFloat(p.anticipo || 0));
    
    const mensaje = `Hola *${p.nombre}*, te saludamos de Tinta Negra.\n\nTu pedido *${p.id}* ha cambiado a: *${p.status.toUpperCase()}*.\n\nSaldo: ${fmtMoney(saldo)}.\nDetalles: ${urlPedido}`;
    return `https://wa.me/52${telLimpio}?text=${encodeURIComponent(mensaje)}`;
}

