// ================================
// VARIABLES GLOBALES
// ================================
let exportRows = [];

// ================================
// INICIALIZACIÓN
// ================================
document.addEventListener('DOMContentLoaded', function() {
    configurarEventos();
    calcular();
});

// ================================
// CONFIGURACIÓN DE EVENTOS
// ================================
function configurarEventos() {
    // Event listener para cambios en cantidad
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cantidad')) {
            calcular();
            resaltarFilaActiva(e.target);
        }
    });

    // Event listener para cambios en margen personalizado
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('margen-input')) {
            calcular();
            resaltarFilaActiva(e.target);
        }
    });

    // Event listener para cambios en margen global
    document.getElementById('margen').addEventListener('input', function() {
        document.getElementById('margen-display').textContent = this.value + '%';
        calcular();
    });

    // Configurar filtros
    document.querySelectorAll('.filter-input').forEach(filter => {
        filter.addEventListener('input', aplicarFiltros);
    });

    // Validaciones en tiempo real
    document.getElementById('cliente').addEventListener('input', validarCampo);
    document.getElementById('proyecto').addEventListener('input', validarCampo);

    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl + L para limpiar
        if (e.ctrlKey && e.key === 'l') {
            e.preventDefault();
            limpiarCotizacion();
        }
        
        // Ctrl + E para exportar
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            exportarCotizacion();
        }
        
        // Escape para limpiar filtros
        if (e.key === 'Escape') {
            limpiarFiltros();
        }
    });
}

// ================================
// FUNCIÓN PRINCIPAL DE CÁLCULO
// ================================
function calcular() {
    const margenGlobal = parseFloat(document.getElementById('margen').value) / 100;
    let totalCosto = 0;
    let totalVenta = 0;
    let itemsSeleccionados = 0;

    exportRows = [];

    document.querySelectorAll('.cantidad').forEach(input => {
        const cantidad = parseFloat(input.value) || 0;
        const costo = parseFloat(input.dataset.costo);
        const row = input.closest('tr');
        
        // Obtener margen personalizado o usar global
        const margenInput = row.querySelector('.margen-input');
        const margenPersonalizado = parseFloat(margenInput.value) || null;
        const margen = margenPersonalizado ? margenPersonalizado / 100 : margenGlobal;
        
        const subtotal = costo * cantidad;
        const precioVenta = subtotal / (1 - margen);

        // Actualizar la fila
        row.querySelector('.subtotal').textContent = `${subtotal.toFixed(2)}`;
        row.querySelector('.precio-venta').textContent = `${precioVenta.toFixed(2)}`;

        if (cantidad > 0) {
            totalCosto += subtotal;
            totalVenta += precioVenta;
            itemsSeleccionados++;

            // Agregar a exportación
            exportRows.push({
                tipo_costo: input.dataset.tipo,
                recurrencia: input.dataset.recurrencia,
                categoria: input.dataset.categoria,
                grupo: input.dataset.grupo,
                tipo_prod: input.dataset.producto,
                item: input.dataset.item,
                costoUSD: costo,
                cantidad: cantidad,
                subtotal: subtotal,
                precioVenta: precioVenta,
                margenPersonalizado: margenPersonalizado
            });

            // Destacar fila seleccionada
            row.style.backgroundColor = '#f0f9ff';
            row.style.borderLeft = '4px solid #2563eb';
        } else {
            row.style.backgroundColor = '';
            row.style.borderLeft = '';
        }
    });

    // Actualizar resumen
    document.getElementById('items-count').textContent = itemsSeleccionados;
    document.getElementById('costo-total').textContent = `${totalCosto.toFixed(2)}`;
    document.getElementById('total-cotizacion').textContent = `${totalVenta.toFixed(2)}`;

    // Actualizar datos de exportación
    actualizarDatosExportacion();
}

// ================================
// FILTROS DE TABLA
// ================================
function aplicarFiltros() {
    const filters = document.querySelectorAll('.filter-input');
    const rows = document.querySelectorAll('#tabla-costos tr.main-row');

    rows.forEach(row => {
        let visible = true;
        
        filters.forEach((filter, index) => {
            const filterValue = filter.value.toLowerCase().trim();
            if (filterValue) {
                const cellText = row.children[index].textContent.toLowerCase();
                if (!cellText.includes(filterValue)) {
                    visible = false;
                }
            }
        });

        row.style.display = visible ? '' : 'none';
    });

    // Actualizar contador de resultados
    const visibleRows = document.querySelectorAll('#tabla-costos tr.main-row:not([style*="display: none"])').length;
    mostrarNotificacion(`Mostrando ${visibleRows} de ${rows.length} items`, 'info');
}

function limpiarFiltros() {
    document.querySelectorAll('.filter-input').forEach(filter => {
        filter.value = '';
    });
    aplicarFiltros();
    mostrarNotificacion('Filtros limpiados', 'success');
}

// ================================
// GESTIÓN DE COTIZACIÓN
// ================================
function limpiarCotizacion() {
    if (confirm('¿Estás seguro de que quieres limpiar todas las cantidades?')) {
        document.querySelectorAll('.cantidad').forEach(input => {
            input.value = 0;
        });
        document.querySelectorAll('.margen-input').forEach(input => {
            input.value = '';
        });
        document.getElementById('cliente').value = '';
        document.getElementById('proyecto').value = '';
        calcular();
        
        // Animación de éxito
        document.querySelector('.controls-section').classList.add('success-flash');
        setTimeout(() => {
            document.querySelector('.controls-section').classList.remove('success-flash');
        }, 500);
        
        mostrarNotificacion('Cotización limpiada correctamente', 'success');
    }
}

function exportarCotizacion() {
    if (exportRows.length === 0) {
        mostrarNotificacion('No hay items seleccionados para exportar', 'error');
        return;
    }

    const cliente = document.getElementById('cliente').value.trim();
    const proyecto = document.getElementById('proyecto').value.trim();

    if (!cliente) {
        mostrarNotificacion('Por favor ingresa el nombre del cliente', 'error');
        document.getElementById('cliente').focus();
        return;
    }

    // Preparar datos para exportación con estructura mejorada
    const data = {
        cliente: cliente,
        proyecto: proyecto,
        margen: document.getElementById('margen').value,
        fecha: new Date().toLocaleDateString('es-ES'),
        hora: new Date().toLocaleTimeString('es-ES'),
        items: exportRows.map(item => ({
            tipo_costo: item.tipo_costo || '',
            recurrencia: item.recurrencia || 'Mensual',
            categoria: item.categoria || '',
            grupo: item.grupo || 'Sin Grupo',
            tipo_prod: item.tipo_prod || '',
            item: item.item || '',
            costoUSD: parseFloat(item.costoUSD) || 0,
            cantidad: parseInt(item.cantidad) || 0,
            subtotal: parseFloat(item.subtotal) || 0,
            precioVenta: parseFloat(item.precioVenta) || 0,
            margenPersonalizado: item.margenPersonalizado
        })),
        resumen: {
            itemsSeleccionados: exportRows.length,
            costoTotal: exportRows.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0),
            totalCotizacion: exportRows.reduce((sum, item) => sum + (parseFloat(item.precioVenta) || 0), 0)
        }
    };
    
    // Validar datos antes de enviar
    if (data.items.length === 0) {
        mostrarNotificacion('Error: No hay items válidos para exportar', 'error');
        return;
    }
    
    try {
        console.log('Datos a exportar:', data);
        document.getElementById('exportData').value = JSON.stringify(data);
        document.getElementById('exportForm').submit();
        mostrarNotificacion('Generando archivo Excel...', 'info');
    } catch (error) {
        console.error('Error al preparar exportación:', error);
        mostrarNotificacion('Error al preparar los datos para exportación', 'error');
    }
}

// ================================
// UTILIDADES Y VALIDACIONES
// ================================
function validarCampo(event) {
    const campo = event.target;
    const valor = campo.value.trim();
    
    // Remover clases de error anteriores
    campo.classList.remove('input-error');
    
    // Validaciones específicas
    if (campo.id === 'cliente' && valor.length > 0 && valor.length < 2) {
        campo.classList.add('input-error');
        mostrarTooltip(campo, 'El nombre del cliente debe tener al menos 2 caracteres');
    }
    
    if (campo.id === 'proyecto' && valor.length > 100) {
        campo.classList.add('input-error');
        mostrarTooltip(campo, 'El nombre del proyecto no puede exceder 100 caracteres');
    }
}

function resaltarFilaActiva(input) {
    // Remover resaltado anterior
    document.querySelectorAll('.main-row').forEach(row => {
        row.classList.remove('editing');
    });
    
    // Resaltar fila actual
    const row = input.closest('tr');
    row.classList.add('editing');
    
    // Remover resaltado después de un momento
    setTimeout(() => {
        row.classList.remove('editing');
    }, 1000);
}

function actualizarDatosExportacion() {
    const data = {
        cliente: document.getElementById('cliente').value,
        proyecto: document.getElementById('proyecto').value,
        margen: document.getElementById('margen').value,
        items: exportRows
    };
    
    document.getElementById('exportData').value = JSON.stringify(data);
}

// ================================
// SISTEMA DE NOTIFICACIONES
// ================================
function mostrarNotificacion(mensaje, tipo = 'info', duracion = 3000) {
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.className = `alert alert-${tipo}`;
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1001;
        min-width: 300px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        padding: 1rem;
        border-radius: 6px;
        color: white;
        font-weight: 500;
    `;

    // Colores según tipo
    const colores = {
        'success': '#10b981',
        'error': '#ef4444',
        'warning': '#f59e0b',
        'info': '#3b82f6'
    };
    
    notificacion.style.backgroundColor = colores[tipo] || colores['info'];
    notificacion.textContent = mensaje;

    // Agregar al DOM
    document.body.appendChild(notificacion);

    // Mostrar animación
    setTimeout(() => {
        notificacion.style.opacity = '1';
        notificacion.style.transform = 'translateX(0)';
    }, 100);

    // Ocultar después del tiempo especificado
    setTimeout(() => {
        notificacion.style.opacity = '0';
        notificacion.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            if (notificacion.parentNode) {
                notificacion.parentNode.removeChild(notificacion);
            }
        }, 300);
    }, duracion);
}

function mostrarTooltip(elemento, mensaje) {
    // Crear tooltip temporal
    const tooltip = document.createElement('div');
    tooltip.className = 'error-message';
    tooltip.textContent = mensaje;
    tooltip.style.cssText = `
        position: absolute;
        bottom: -25px;
        left: 0;
        font-size: 0.75rem;
        color: #ef4444;
        z-index: 1000;
        background: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    `;

    // Posicionar relative el contenedor padre
    elemento.style.position = 'relative';
    elemento.appendChild(tooltip);

    // Remover después de unos segundos
    setTimeout(() => {
        if (tooltip.parentNode) {
            tooltip.parentNode.removeChild(tooltip);
        }
    }, 3000);
}

// Auto-guardar cada 30 segundos si hay cambios
setInterval(() => {
    const hayDatos = document.getElementById('cliente').value || 
                    document.getElementById('proyecto').value ||
                    document.querySelector('.cantidad[value]:not([value="0"])');
    
    if (hayDatos) {
        // Auto-guardar implementación simplificada
        const data = {
            cliente: document.getElementById('cliente').value,
            proyecto: document.getElementById('proyecto').value,
            margen: document.getElementById('margen').value,
            timestamp: Date.now()
        };
        try {
            localStorage.setItem('cotizacion_borrador', JSON.stringify(data));
        } catch(e) {
            // Silenciar errores de localStorage
        }
    }
}, 30000);