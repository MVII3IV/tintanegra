const fmtMoney = (n) => n.toLocaleString("es-MX", { style: "currency", currency: "MXN" });
const LIMIT_MB = 10; 

function validarPesoArchivo(input) {
    const LIMIT_MB = 10;
    if (input.files && input.files.length > 0) {
        for (const file of input.files) {
            const sizeMB = file.size / (1024 * 1024);
            if (sizeMB > LIMIT_MB) {
                document.getElementById('fileSizeName').textContent = file.name;
                document.getElementById('fileSizeActual').textContent = sizeMB.toFixed(2) + ' MB';
                document.getElementById('fileSizeLimit').textContent = LIMIT_MB + ' MB';

                const modalEl = document.getElementById('fileSizeModal');
                if (modalEl) new bootstrap.Modal(modalEl).show();
                
                input.value = ''; 
                return false;
            }
        }
    }
    return true;
}

function calcularTotalPiezas() {
    let total = 0;
    document.querySelectorAll('input[name="cantidad[]"]').forEach(i => total += parseInt(i.value) || 0);
    document.getElementById('totalPiezasAdmin').innerText = total;
}

function actualizarBotonLista() {
    const checks = document.querySelectorAll('.check-pedido:checked');
    const btn = document.getElementById('btnGenerarLista');
    const contador = document.getElementById('contadorSeleccionados');
    
    if (btn) {
        if (checks.length > 0) {
            btn.style.display = 'inline-flex';
            contador.innerText = checks.length;
        } else {
            btn.style.display = 'none';
        }
    }
}

function obtenerNombreColor(hexInput) {
    // Si no hay input o es inválido, regresamos tal cual
    if (!hexInput || !hexInput.startsWith('#')) return hexInput;

    // 1. Función interna para convertir Hex a RGB
    const hexToRgb = (hex) => {
        const bigint = parseInt(hex.slice(1), 16);
        return { r: (bigint >> 16) & 255, g: (bigint >> 8) & 255, b: bigint & 255 };
    };

    // 2. Paleta Maestra de Colores (Agrega aquí todos los que quieras reconocer)
    const baseColors = [
{ hex: "#000000", name: "Negro" },
        { hex: "#ffffff", name: "Blanco" },
        { hex: "#ff0000", name: "Rojo" },
        { hex: "#dc143c", name: "Rojo Carmesí" },
        { hex: "#800000", name: "Vino" },
        { hex: "#0000ff", name: "Azul Rey" },
        { hex: "#000080", name: "Azul Marino" },
        { hex: "#87ceeb", name: "Azul Cielo" },
        { hex: "#ffff00", name: "Amarillo" },
        { hex: "#008000", name: "Verde" },
        { hex: "#006400", name: "Verde Botella" },
        { hex: "#808080", name: "Gris" },
        { hex: "#d3d3d3", name: "Gris Jaspe" },
        { hex: "#ffa500", name: "Naranja" },
        { hex: "#800080", name: "Morado" },
        { hex: "#ffc0cb", name: "Rosa" },
        { hex: "#ff1493", name: "Rosa Mexicano" },
        { hex: "#f5f5dc", name: "Beige" },       // Beige claro
        { hex: "#e3dac9", name: "Beige" },       // <--- AGREGADO: Tono "Arena" o "Hueso" (atrapa los que se iban a Gris)
        { hex: "#c2b280", name: "Arena" },       // <--- AGREGADO: Tono Arena más oscuro (Khaki)
        { hex: "#a52a2a", name: "Café" },
        { hex: "#40e0d0", name: "Turquesa" }
    ];

    // 3. Algoritmo de distancia (Busca el "vecino" más cercano)
    const inputRgb = hexToRgb(hexInput);
    let closestName = hexInput;
    let minDistance = Infinity;

    baseColors.forEach(base => {
        const baseRgb = hexToRgb(base.hex);
        // Distancia Euclidiana: Raíz cuadrada de la suma de las diferencias al cuadrado
        const dist = Math.sqrt(
            Math.pow(inputRgb.r - baseRgb.r, 2) +
            Math.pow(inputRgb.g - baseRgb.g, 2) +
            Math.pow(inputRgb.b - baseRgb.b, 2)
        );

        if (dist < minDistance) {
            minDistance = dist;
            closestName = base.name;
        }
    });

    // Opcional: Si quieres mostrar también el código original para referencia
    // return `${closestName} <span style="font-size:0.8em; opacity:0.5">(${hexInput})</span>`;
    
    return closestName;
}


