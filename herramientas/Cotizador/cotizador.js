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

    // Event listener para cambios en margen
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
    const margen = parseFloat(document.getElementById('margen').value) / 100;
    let totalCosto = 0;
    let totalVenta = 0;
    let itemsSeleccionados = 0;

    exportRows = [];

    document.querySelectorAll('.cantidad').forEach(input => {
        const cantidad = parseFloat(input.value) || 0;
        const costo = parseFloat(input.dataset.costo);
        const row = input.closest('tr');
        
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
                tipo_prod: input.dataset.producto,
                item: input.dataset.item,
                costoUSD: costo,
                cantidad: cantidad,
                subtotal: subtotal,
                precioVenta: precioVenta
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

    // Preparar datos para exportación
    const data = {
        cliente: cliente,
        proyecto: proyecto,
        margen: document.getElementById('margen').value,
        fecha: new Date().toLocaleDateString('es-ES'),
        hora: new Date().toLocaleTimeString('es-ES'),
        items: exportRows,
        resumen: {
            itemsSeleccionados: exportRows.length,
            costoTotal: exportRows.reduce((sum, item) => sum + item.subtotal, 0),
            totalCotizacion: exportRows.reduce((sum, item) => sum + item.precioVenta, 0)
        }
    };
    
    document.getElementById('exportData').value = JSON.stringify(data);
    document.getElementById('exportForm').submit();
    
    mostrarNotificacion('Exportando cotización...', 'info');
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
    `;
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
        color: var(--danger-color);
        z-index: 1000;
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

// ================================
// FUNCIONES DE ANÁLISIS
// ================================
function analizarCotizacion() {
    const analisis = {
        itemsMasCaros: obtenerItemsMasCaros(5),
        distribucionPorCategoria: obtenerDistribucionCategoria(),
        margenPromedio: calcularMargenPromedio(),
        recomendaciones: generarRecomendaciones()
    };

    console.log('Análisis de Cotización:', analisis);
    return analisis;
}

function obtenerItemsMasCaros(limite = 5) {
    return exportRows
        .sort((a, b) => b.precioVenta - a.precioVenta)
        .slice(0, limite)
        .map(item => ({
            item: item.item,
            precio: item.precioVenta,
            porcentaje: (item.precioVenta / exportRows.reduce((sum, i) => sum + i.precioVenta, 0) * 100).toFixed(1)
        }));
}

function obtenerDistribucionCategoria() {
    const distribucion = {};
    
    exportRows.forEach(item => {
        if (!distribucion[item.categoria]) {
            distribucion[item.categoria] = {
                cantidad: 0,
                valor: 0
            };
        }
        distribucion[item.categoria].cantidad++;
        distribucion[item.categoria].valor += item.precioVenta;
    });

    return distribucion;
}

function calcularMargenPromedio() {
    if (exportRows.length === 0) return 0;
    
    const margenTotal = exportRows.reduce((sum, item) => {
        const margen = ((item.precioVenta - item.subtotal) / item.precioVenta) * 100;
        return sum + margen;
    }, 0);

    return (margenTotal / exportRows.length).toFixed(2);
}

function generarRecomendaciones() {
    const recomendaciones = [];
    const total = exportRows.reduce((sum, item) => sum + item.precioVenta, 0);
    
    // Verificar si hay items muy costosos
    const itemsCostosos = exportRows.filter(item => 
        (item.precioVenta / total) > 0.3
    );
    
    if (itemsCostosos.length > 0) {
        recomendaciones.push('Considerar revisar items de alto costo que representan más del 30% del total');
    }

    // Verificar distribución de categorías
    const categorias = obtenerDistribucionCategoria();
    const categoriasCount = Object.keys(categorias).length;
    
    if (categoriasCount === 1) {
        recomendaciones.push('Cotización enfocada en una sola categoría - considerar servicios complementarios');
    }

    // Verificar margen
    const margen = parseFloat(document.getElementById('margen').value);
    if (margen < 30) {
        recomendaciones.push('Margen inferior al 30% - evaluar viabilidad comercial');
    } else if (margen > 70) {
        recomendaciones.push('Margen superior al 70% - verificar competitividad en el mercado');
    }

    return recomendaciones;
}

// ================================
// FUNCIONES DE UTILIDAD
// ================================
function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(valor);
}

function formatearNumero(valor, decimales = 2) {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales
    }).format(valor);
}

function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        mostrarNotificacion('Copiado al portapapeles', 'success');
    }).catch(err => {
        console.error('Error al copiar:', err);
        mostrarNotificacion('Error al copiar al portapapeles', 'error');
    });
}

// ================================
// FUNCIONES DE EXPORTACIÓN ADICIONALES
// ================================
function exportarCSV() {
    if (exportRows.length === 0) {
        mostrarNotificacion('No hay datos para exportar', 'error');
        return;
    }

    const headers = ['Tipo', 'Recurrencia', 'Categoría', 'Producto', 'Item', 'Costo USD', 'Cantidad', 'Subtotal', 'Precio Venta'];
    const csvContent = [
        headers.join(','),
        ...exportRows.map(row => [
            `"${row.tipo_costo}"`,
            `"${row.recurrencia}"`,
            `"${row.categoria}"`,
            `"${row.tipo_prod}"`,
            `"${row.item}"`,
            row.costoUSD,
            row.cantidad,
            row.subtotal.toFixed(2),
            row.precioVenta.toFixed(2)
        ].join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `cotizacion_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarNotificacion('CSV exportado correctamente', 'success');
}

function imprimirCotizacion() {
    const ventanaImpresion = window.open('', '_blank');
    const cliente = document.getElementById('cliente').value || 'Cliente no especificado';
    const proyecto = document.getElementById('proyecto').value || 'Proyecto no especificado';
    
    ventanaImpresion.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Cotización - ${cliente}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .info { margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { font-weight: bold; background-color: #e8f4fd; }
                .right { text-align: right; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>💼 Cotización Profesional</h1>
                <h2>SkyTel</h2>
            </div>
            <div class="info">
                <p><strong>Cliente:</strong> ${cliente}</p>
                <p><strong>Proyecto:</strong> ${proyecto}</p>
                <p><strong>Fecha:</strong> ${new Date().toLocaleDateString('es-ES')}</p>
                <p><strong>Margen:</strong> ${document.getElementById('margen').value}%</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Costo Unit.</th>
                        <th>Subtotal</th>
                        <th>Precio Venta</th>
                    </tr>
                </thead>
                <tbody>
                    ${exportRows.map(row => `
                        <tr>
                            <td>${row.item}</td>
                            <td>${row.tipo_costo} - ${row.recurrencia}</td>
                            <td class="right">${row.cantidad}</td>
                            <td class="right">${row.costoUSD.toFixed(2)}</td>
                            <td class="right">${row.subtotal.toFixed(2)}</td>
                            <td class="right">${row.precioVenta.toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr class="total">
                        <td colspan="5"><strong>TOTAL COTIZACIÓN</strong></td>
                        <td class="right"><strong>${exportRows.reduce((sum, item) => sum + item.precioVenta, 0).toFixed(2)}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </body>
        </html>
    `);
    
    ventanaImpresion.document.close();
    ventanaImpresion.print();
}

// ================================
// AUTO-GUARDADO LOCAL (LocalStorage)
// ================================
function autoGuardar() {
    const cotizacion = {
        cliente: document.getElementById('cliente').value,
        proyecto: document.getElementById('proyecto').value,
        margen: document.getElementById('margen').value,
        cantidades: {},
        timestamp: Date.now()
    };

    // Guardar cantidades
    document.querySelectorAll('.cantidad').forEach(input => {
        if (input.value > 0) {
            cotizacion.cantidades[input.dataset.index] = input.value;
        }
    });

    localStorage.setItem('cotizacion_borrador', JSON.stringify(cotizacion));
}

function cargarBorrador() {
    const borrador = localStorage.getItem('cotizacion_borrador');
    if (borrador) {
        try {
            const cotizacion = JSON.parse(borrador);
            
            // Verificar si es reciente (menos de 24 horas)
            const horasTranscurridas = (Date.now() - cotizacion.timestamp) / (1000 * 60 * 60);
            
            if (horasTranscurridas < 24) {
                if (confirm('Se encontró un borrador reciente. ¿Deseas cargarlo?')) {
                    document.getElementById('cliente').value = cotizacion.cliente || '';
                    document.getElementById('proyecto').value = cotizacion.proyecto || '';
                    document.getElementById('margen').value = cotizacion.margen || 50;
                    
                    // Cargar cantidades
                    Object.keys(cotizacion.cantidades).forEach(index => {
                        const input = document.querySelector(`[data-index="${index}"]`);
                        if (input) {
                            input.value = cotizacion.cantidades[index];
                        }
                    });
                    
                    calcular();
                    mostrarNotificacion('Borrador cargado correctamente', 'success');
                }
            } else {
                localStorage.removeItem('cotizacion_borrador');
            }
        } catch (e) {
            console.error('Error al cargar borrador:', e);
            localStorage.removeItem('cotizacion_borrador');
        }
    }
}

// Auto-guardar cada 30 segundos si hay cambios
setInterval(() => {
    const hayDatos = document.getElementById('cliente').value || 
                    document.getElementById('proyecto').value ||
                    document.querySelector('.cantidad[value]:not([value="0"])');
    
    if (hayDatos) {
        autoGuardar();
    }
}, 30000);

// Cargar borrador al iniciar
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(cargarBorrador, 1000);
});