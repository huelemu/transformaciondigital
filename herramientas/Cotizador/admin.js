// ================================
// ADMIN.JS - VERSIÓN CON GRUPOS
// ================================

// Variables globales
let editingItemId = null;
let margenGlobal = 50;
let itemsData = [];
let filteredData = [];
let seleccionMasivaActiva = false;
let itemsSeleccionados = [];

// ================================
// INICIALIZACIÓN
// ================================
document.addEventListener('DOMContentLoaded', function() {
    inicializarAdmin();
    configurarEventListeners();
    cargarDatosItems();
});

function inicializarAdmin() {
    console.log('🚀 Inicializando Admin con soporte para grupos...');
    
    // Configurar tabs
    configurarTabs();
    
    // Configurar filtros
    configurarFiltros();
    
    // Auto-ocultar alertas
    configurarAlertas();
}

function configurarEventListeners() {
    // Filtros en tiempo real
    document.getElementById('search-items')?.addEventListener('input', aplicarFiltros);
    document.getElementById('filter-tipo')?.addEventListener('change', aplicarFiltros);
    document.getElementById('filter-categoria')?.addEventListener('change', aplicarFiltros);
    document.getElementById('filter-grupo')?.addEventListener('change', aplicarFiltros);
    
    // Checkboxes para selección múltiple
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-checkbox')) {
            actualizarItemsSeleccionados();
        }
    });
    
    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            openModal('item-modal');
        }
        if (e.key === 'Escape') {
            cerrarModalActivo();
        }
    });
}

function cargarDatosItems() {
    // Esta función cargará los datos desde el PHP
    // Por ahora es placeholder para mantener compatibilidad
    itemsData = []; // Se llenaría con datos del servidor
    console.log('📊 Datos de items cargados');
}

// ================================
// GESTIÓN DE TABS
// ================================
function cambiarTab(tabName) {
    // Ocultar todas las pestañas
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Quitar clase active de todos los tabs
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Mostrar la pestaña seleccionada
    const tabContent = document.getElementById('tab-' + tabName);
    const tabButton = event.target;
    
    if (tabContent) {
        tabContent.classList.add('active');
    }
    
    if (tabButton) {
        tabButton.classList.add('active');
    }
    
    console.log(`📄 Cambiando a tab: ${tabName}`);
}

function configurarTabs() {
    // Configurar comportamiento inicial de tabs
    const activeTab = document.querySelector('.tab.active');
    if (activeTab) {
        const tabName = activeTab.textContent.toLowerCase().replace(' ', '-');
        console.log(`📄 Tab inicial: ${tabName}`);
    }
}

// ================================
// SISTEMA DE FILTROS
// ================================
function aplicarFiltros() {
    const searchTerm = document.getElementById('search-items')?.value.toLowerCase() || '';
    const tipoFilter = document.getElementById('filter-tipo')?.value || '';
    const categoriaFilter = document.getElementById('filter-categoria')?.value || '';
    const grupoFilter = document.getElementById('filter-grupo')?.value || '';

    const rows = document.querySelectorAll('#items-table .item-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const tipo = row.dataset.tipo || '';
        const categoria = row.dataset.categoria || '';
        const grupo = row.dataset.grupo || '';
        const itemText = row.textContent.toLowerCase();

        let visible = true;

        // Filtro de búsqueda
        if (searchTerm && !itemText.includes(searchTerm)) {
            visible = false;
        }

        // Filtros de selección
        if (tipoFilter && tipo !== tipoFilter) visible = false;
        if (categoriaFilter && categoria !== categoriaFilter) visible = false;
        if (grupoFilter && grupo !== grupoFilter) visible = false;

        // Mostrar/ocultar fila
        row.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
    });

    console.log(`🔍 Filtros aplicados: ${visibleCount} items visibles`);
    
    // Actualizar contador si existe
    const contador = document.getElementById('items-count');
    if (contador) {
        contador.textContent = `${visibleCount} items`;
    }
}

function limpiarFiltros() {
    const filtros = ['search-items', 'filter-tipo', 'filter-categoria', 'filter-grupo'];
    
    filtros.forEach(filtroId => {
        const elemento = document.getElementById(filtroId);
        if (elemento) {
            elemento.value = '';
        }
    });
    
    aplicarFiltros();
    mostrarNotificacion('Filtros limpiados', 'success');
}

function configurarFiltros() {
    // Configurar autocompletado y sugerencias
    const searchInput = document.getElementById('search-items');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(aplicarFiltros, 300); // Debounce
        });
    }
}

// ================================
// GESTIÓN DE GRUPOS
// ================================
function manejarCambioGrupo() {
    const select = document.getElementById('grupo');
    const inputNuevo = document.getElementById('nuevo-grupo');
    
    if (!select || !inputNuevo) return;
    
    if (select.value === 'nuevo') {
        inputNuevo.style.display = 'block';
        inputNuevo.focus();
        inputNuevo.placeholder = 'Ej: Setup Inicial, Costos Variables...';
    } else {
        inputNuevo.style.display = 'none';
        inputNuevo.value = '';
    }
}

function confirmarNuevoGrupo() {
    const inputNuevo = document.getElementById('nuevo-grupo');
    const select = document.getElementById('grupo');
    
    if (!inputNuevo || !select) return;
    
    const nuevoGrupo = inputNuevo.value.trim();
    
    if (nuevoGrupo) {
        // Validar nombre del grupo
        if (nuevoGrupo.length < 2) {
            mostrarNotificacion('El nombre del grupo debe tener al menos 2 caracteres', 'error');
            return;
        }
        
        if (nuevoGrupo.length > 50) {
            mostrarNotificacion('El nombre del grupo no puede exceder 50 caracteres', 'error');
            return;
        }
        
        // Crear nueva opción
        const option = document.createElement('option');
        option.value = nuevoGrupo;
        option.textContent = nuevoGrupo;
        option.selected = true;
        
        // Insertar antes de la opción "nuevo"
        const ultimaOpcion = select.lastElementChild;
        select.insertBefore(option, ultimaOpcion);
        
        inputNuevo.style.display = 'none';
        inputNuevo.value = '';
        
        mostrarNotificacion(`Grupo "${nuevoGrupo}" creado`, 'success');
    } else {
        select.value = '';
        inputNuevo.style.display = 'none';
    }
}

function toggleGrupo(grupo) {
    const section = document.querySelector(`[data-grupo="${grupo}"]`);
    if (!section) return;
    
    const items = section.querySelector('.grupo-items');
    const icon = section.querySelector('.toggle-icon');
    
    if (!items || !icon) return;
    
    const isVisible = items.style.display !== 'none';
    
    if (isVisible) {
        items.style.display = 'none';
        icon.textContent = '▶';
    } else {
        items.style.display = 'block';
        icon.textContent = '▼';
    }
    
    console.log(`📁 Grupo "${grupo}" ${isVisible ? 'contraído' : 'expandido'}`);
}

function expandirTodosGrupos() {
    document.querySelectorAll('.grupo-items').forEach(items => {
        items.style.display = 'block';
    });
    document.querySelectorAll('.toggle-icon').forEach(icon => {
        icon.textContent = '▼';
    });
    mostrarNotificacion('Todos los grupos expandidos', 'info');
}

function contraerTodosGrupos() {
    document.querySelectorAll('.grupo-items').forEach(items => {
        items.style.display = 'none';
    });
    document.querySelectorAll('.toggle-icon').forEach(icon => {
        icon.textContent = '▶';
    });
    mostrarNotificacion('Todos los grupos contraídos', 'info');
}

function mostrarGestionGrupos() {
    openModal('grupos-modal');
}

function crearNuevoGrupo() {
    const input = document.getElementById('nuevo-grupo-nombre');
    if (!input) return;
    
    const nombre = input.value.trim();
    
    if (!nombre) {
        mostrarNotificacion('Ingresa un nombre para el grupo', 'error');
        input.focus();
        return;
    }
    
    if (nombre.length < 2) {
        mostrarNotificacion('El nombre debe tener al menos 2 caracteres', 'error');
        return;
    }
    
    // Aquí iría la llamada AJAX para crear el grupo
    console.log('🏷️ Creando grupo:', nombre);
    
    // Simular creación exitosa
    mostrarNotificacion(`Grupo "${nombre}" creado correctamente`, 'success');
    input.value = '';
    
    // En implementación real, recargar la página o actualizar dinámicamente
}

function eliminarGrupo(grupo) {
    if (!confirm(`¿Eliminar el grupo "${grupo}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }
    
    // Aquí iría la llamada AJAX para eliminar el grupo
    console.log('🗑️ Eliminando grupo:', grupo);
    
    // En implementación real, verificar que no tenga items
    mostrarNotificacion(`Grupo "${grupo}" eliminado`, 'success');
}

// ================================
// SELECCIÓN MÚLTIPLE
// ================================
function toggleSeleccionMasiva() {
    seleccionMasivaActiva = !seleccionMasivaActiva;
    
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAll = document.getElementById('select-all');
    const bulkActions = document.getElementById('bulk-actions');
    const btnSeleccion = document.getElementById('btn-seleccion');
    
    if (seleccionMasivaActiva) {
        checkboxes.forEach(cb => cb.style.display = 'block');
        if (selectAll) selectAll.style.display = 'block';
        if (bulkActions) bulkActions.style.display = 'block';
        if (btnSeleccion) btnSeleccion.innerHTML = '<span>❌</span> Cancelar Selección';
        
        mostrarNotificacion('Modo selección múltiple activado', 'info');
    } else {
        checkboxes.forEach(cb => {
            cb.style.display = 'none';
            cb.checked = false;
        });
        if (selectAll) {
            selectAll.style.display = 'none';
            selectAll.checked = false;
        }
        if (bulkActions) bulkActions.style.display = 'none';
        if (btnSeleccion) btnSeleccion.innerHTML = '<span>☑️</span> Selección Múltiple';
        
        itemsSeleccionados = [];
        actualizarContadorSeleccionados();
    }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.item-checkbox:not([style*="display: none"])');
    
    if (!selectAll) return;
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    actualizarItemsSeleccionados();
}

function actualizarItemsSeleccionados() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    itemsSeleccionados = Array.from(checkboxes).map(cb => cb.value);
    actualizarContadorSeleccionados();
}

function actualizarContadorSeleccionados() {
    const contador = document.getElementById('selected-count');
    if (contador) {
        contador.textContent = `${itemsSeleccionados.length} items seleccionados`;
    }
}

function aplicarCambiosMasivos() {
    if (itemsSeleccionados.length === 0) {
        mostrarNotificacion('Selecciona al menos un item', 'error');
        return;
    }
    
    const nuevoGrupo = document.getElementById('bulk-grupo')?.value;
    if (!nuevoGrupo) {
        mostrarNotificacion('Selecciona un grupo', 'error');
        return;
    }
    
    const mensaje = `¿Cambiar ${itemsSeleccionados.length} items al grupo "${nuevoGrupo}"?`;
    
    if (confirm(mensaje)) {
        document.getElementById('bulk-selected-items').value = JSON.stringify(itemsSeleccionados);
        document.getElementById('bulk-nuevo-grupo').value = nuevoGrupo;
        document.getElementById('bulk-form').submit();
    }
}

function cancelarSeleccionMasiva() {
    toggleSeleccionMasiva();
}

// ================================
// CRUD DE ITEMS
// ================================
function editarItem(id) {
    console.log('✏️ Editando item:', id);
    editingItemId = id;
    
    // Buscar datos del item en la tabla
    const row = document.querySelector(`[data-id="${id}"]`);
    if (!row) {
        mostrarNotificacion('Item no encontrado', 'error');
        return;
    }

    // Llenar formulario con datos existentes
    const tipo = row.dataset.tipo;
    const categoria = row.dataset.categoria;
    const grupo = row.dataset.grupo;
    
    // Llenar campos del formulario
    document.getElementById('edit-item-id').value = id;
    
    const tipoSelect = document.getElementById('tipo_costo');
    if (tipoSelect) tipoSelect.value = tipo;
    
    const categoriaSelect = document.getElementById('categoria');
    if (categoriaSelect) categoriaSelect.value = categoria;
    
    const grupoSelect = document.getElementById('grupo');
    if (grupoSelect) grupoSelect.value = grupo;
    
    // Extraer otros valores de las celdas (implementación simplificada)
    const cells = row.querySelectorAll('td');
    
    // El item está en la 6ta columna (índice 5)
    const itemText = cells[5]?.querySelector('.text-truncate')?.textContent?.trim();
    if (itemText) {
        const itemInput = document.getElementById('item');
        if (itemInput) itemInput.value = itemText;
    }
    
    // El costo está en la 7ma columna (índice 6)
    const costoText = cells[6]?.textContent?.replace('$', '').replace(',', '').trim();
    if (costoText) {
        const costoInput = document.getElementById('costoUSD');
        if (costoInput) costoInput.value = parseFloat(costoText);
    }
    
    document.getElementById('item-modal-title').textContent = 'Editar Item';
    openModal('item-modal');
}

function eliminarItem(id) {
    const row = document.querySelector(`[data-id="${id}"]`);
    const itemName = row?.querySelector('.text-truncate')?.textContent?.trim() || 'este item';
    
    const mensaje = `¿Estás seguro de que quieres eliminar "${itemName}"?\n\nEsta acción no se puede deshacer.`;
    
    if (confirm(mensaje)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_item">
            <input type="hidden" name="item_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function duplicarItem(id) {
    // Cargar datos del item como en editar, pero sin ID
    editarItem(id);
    
    // Limpiar ID para crear nuevo item
    document.getElementById('edit-item-id').value = '';
    document.getElementById('item-modal-title').textContent = 'Duplicar Item';
    
    // Agregar " (Copia)" al nombre
    const itemInput = document.getElementById('item');
    if (itemInput && itemInput.value) {
        itemInput.value += ' (Copia)';
    }
}

// ================================
// GESTIÓN DE MODALES
// ================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Focus en primer input
    const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 100);
    }
    
    console.log(`📄 Modal abierto: ${modalId}`);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    modal.classList.remove('active');
    document.body.style.overflow = '';
    
    // Limpiar formularios específicos
    if (modalId === 'item-modal') {
        limpiarFormularioItem();
    }
    
    console.log(`📄 Modal cerrado: ${modalId}`);
}

function cerrarModalActivo() {
    const modalActivo = document.querySelector('.modal.active');
    if (modalActivo) {
        const modalId = modalActivo.id;
        closeModal(modalId);
    }
}

function limpiarFormularioItem() {
    editingItemId = null;
    
    const form = document.getElementById('item-form');
    if (form) form.reset();
    
    document.getElementById('edit-item-id').value = '';
    document.getElementById('item-modal-title').textContent = 'Nuevo Item';
    
    // Ocultar campo de nuevo grupo
    const nuevoGrupoInput = document.getElementById('nuevo-grupo');
    if (nuevoGrupoInput) nuevoGrupoInput.style.display = 'none';
    
    // Limpiar validaciones
    document.querySelectorAll('.form-group').forEach(group => {
        group.classList.remove('valid', 'invalid');
    });
}

// ================================
// ESTADÍSTICAS Y ANÁLISIS
// ================================
function mostrarEstadisticasDetalladas() {
    openModal('stats-modal');
}

function calcularEstadisticasPorGrupo() {
    // Esta función calcularía estadísticas dinámicas
    // Por ahora es placeholder
    console.log('📊 Calculando estadísticas por grupo...');
}

// ================================
// MÁRGENES
// ================================
function aplicarMargenGlobal() {
    const margenInput = document.getElementById('margen-global');
    if (!margenInput) return;
    
    const nuevoMargen = parseInt(margenInput.value);
    
    if (isNaN(nuevoMargen) || nuevoMargen < 0 || nuevoMargen > 99) {
        mostrarNotificacion('El margen debe estar entre 0% y 99%', 'error');
        return;
    }
    
    const mensaje = `¿Aplicar margen del ${nuevoMargen}% a todos los items sin margen personalizado?`;
    
    if (confirm(mensaje)) {
        margenGlobal = nuevoMargen;
        
        // Aquí iría la lógica para actualizar los márgenes
        console.log('💰 Aplicando margen global:', nuevoMargen);
        
        mostrarNotificacion(`Margen global actualizado a ${nuevoMargen}%`, 'success');
    }
}

function guardarMargenes() {
    console.log('💾 Guardando márgenes...');
    // Implementar lógica de guardado de márgenes
    mostrarNotificacion('Márgenes guardados correctamente', 'success');
}

// ================================
// EXPORTACIÓN E IMPORTACIÓN
// ================================
function exportarItems() {
    console.log('📤 Exportando items...');
    
    // Mostrar loading
    mostrarNotificacion('Preparando exportación...', 'info');
    
    // Aquí iría la lógica real de exportación
    setTimeout(() => {
        mostrarNotificacion('Items exportados correctamente', 'success');
    }, 1500);
}

function importarItems() {
    console.log('📥 Importando items...');
    // Implementar lógica de importación
}

// ================================
// SISTEMA DE NOTIFICACIONES
// ================================
function mostrarNotificacion(mensaje, tipo = 'info', duracion = 3000) {
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion notificacion-${tipo}`;
    
    // Iconos según tipo
    const iconos = {
        'success': '✅',
        'error': '❌', 
        'warning': '⚠️',
        'info': 'ℹ️'
    };
    
    notificacion.innerHTML = `
        <span class="notificacion-icon">${iconos[tipo] || 'ℹ️'}</span>
        <span class="notificacion-mensaje">${mensaje}</span>
        <button class="notificacion-cerrar" onclick="cerrarNotificacion(this)">×</button>
    `;
    
    // Agregar estilos si no existen
    if (!document.getElementById('notificaciones-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notificaciones-styles';
        styles.textContent = `
            .notificacion {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 1rem;
                box-shadow: var(--shadow-lg);
                display: flex;
                align-items: center;
                gap: 0.75rem;
                z-index: 10000;
                min-width: 300px;
                animation: slideInNotification 0.3s ease;
            }
            
            .notificacion-success { border-left: 4px solid var(--success); }
            .notificacion-error { border-left: 4px solid var(--danger); }
            .notificacion-warning { border-left: 4px solid var(--warning); }
            .notificacion-info { border-left: 4px solid var(--primary); }
            
            .notificacion-icon {
                font-size: 1.2rem;
            }
            
            .notificacion-mensaje {
                flex: 1;
                font-size: 0.875rem;
                font-weight: 500;
            }
            
            .notificacion-cerrar {
                background: none;
                border: none;
                font-size: 1.2rem;
                cursor: pointer;
                color: var(--text-secondary);
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .notificacion-cerrar:hover {
                color: var(--text-primary);
            }
            
            @keyframes slideInNotification {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutNotification {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(styles);
    }
    
    // Agregar al DOM
    document.body.appendChild(notificacion);
    
    // Auto-cerrar después de la duración especificada
    if (duracion > 0) {
        setTimeout(() => {
            cerrarNotificacion(notificacion.querySelector('.notificacion-cerrar'));
        }, duracion);
    }
    
    console.log(`🔔 Notificación ${tipo}: ${mensaje}`);
}

function cerrarNotificacion(boton) {
    const notificacion = boton.closest('.notificacion');
    if (notificacion) {
        notificacion.style.animation = 'slideOutNotification 0.3s ease';
        setTimeout(() => {
            notificacion.remove();
        }, 300);
    }
}

// ================================
// VALIDACIONES
// ================================
function validarFormularioItem() {
    const campos = [
        { id: 'tipo_costo', nombre: 'Tipo de Costo' },
        { id: 'recurrencia', nombre: 'Recurrencia' },
        { id: 'grupo', nombre: 'Grupo' },
        { id: 'categoria', nombre: 'Categoría' },
        { id: 'tipo_prod', nombre: 'Tipo de Producto' },
        { id: 'item', nombre: 'Nombre del Item' },
        { id: 'costoUSD', nombre: 'Costo USD' }
    ];

    let valido = true;
    
    campos.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        if (!elemento) return;
        
        const grupo = elemento.closest('.form-group');
        const valor = elemento.value.trim();
        
        if (!valor) {
            if (grupo) {
                grupo.classList.remove('valid');
                grupo.classList.add('invalid');
            }
            mostrarErrorCampo(elemento, `${campo.nombre} es obligatorio`);
            valido = false;
        } else {
            if (grupo) {
                grupo.classList.remove('invalid');
                grupo.classList.add('valid');
            }
            ocultarErrorCampo(elemento);
        }
    });

    // Validaciones específicas
    const costoUSD = document.getElementById('costoUSD');
    if (costoUSD?.value) {
        const costo = parseFloat(costoUSD.value);
        if (costo <= 0 || costo > 999999) {
            costoUSD.closest('.form-group')?.classList.add('invalid');
            mostrarErrorCampo(costoUSD, 'El costo debe ser mayor a 0 y menor a $999,999');
            valido = false;
        }
    }

    const margenCustom = document.getElementById('margen_custom');
    if (margenCustom?.value) {
        const margen = parseInt(margenCustom.value);
        if (margen < 0 || margen > 99) {
            margenCustom.closest('.form-group')?.classList.add('invalid');
            mostrarErrorCampo(margenCustom, 'El margen debe estar entre 0% y 99%');
            valido = false;
        }
    }

    return valido;
}

function mostrarErrorCampo(elemento, mensaje) {
    // Remover error anterior
    ocultarErrorCampo(elemento);
    
    // Crear elemento de error
    const error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = mensaje;
    error.style.cssText = `
        color: var(--danger);
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    `;
    
    // Insertar después del elemento
    elemento.parentNode.insertBefore(error, elemento.nextSibling);
}

function ocultarErrorCampo(elemento) {
    const errorExistente = elemento.parentNode.querySelector('.field-error');
    if (errorExistente) {
        errorExistente.remove();
    }
}

// ================================
// CONFIGURACIÓN DE ALERTAS
// ================================
function configurarAlertas() {
    // Auto-ocultar alertas de éxito/error
    setTimeout(() => {
        const alert = document.getElementById('success-alert');
        if (alert) {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 300);
        }
    }, 3000);
}

// ================================
// UTILIDADES
// ================================
function formatearNumero(numero, decimales = 2) {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales
    }).format(numero);
}

function formatearMoneda(numero) {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'USD'
    }).format(numero);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ================================
// MANEJO DE ERRORES
// ================================
window.addEventListener('error', function(e) {
    console.error('Error en admin.js:', e.error);
    mostrarNotificacion('Se produjo un error inesperado', 'error');
});

// ================================
// FUNCIONES ESPECÍFICAS PARA PHP
// ================================

// Funciones que interactúan con el backend PHP
function submitFormularioItem() {
    if (!validarFormularioItem()) {
        return false;
    }
    
    // El formulario se enviará normalmente al PHP
    mostrarNotificacion('Guardando item...', 'info');
    return true;
}

// ================================
// INICIALIZACIÓN FINAL
// ================================
console.log('✅ Admin.js cargado correctamente');

// Exportar funciones globales para uso desde HTML
window.adminJS = {
    cambiarTab,
    aplicarFiltros,
    limpiarFiltros,
    editarItem,
    eliminarItem,
    duplicarItem,
    openModal,
    closeModal,
    mostrarEstadisticasDetalladas,
    mostrarGestionGrupos,
    toggleSeleccionMasiva,
    aplicarCambiosMasivos,
    manejarCambioGrupo,
    confirmarNuevoGrupo,
    toggleGrupo,
    expandirTodosGrupos,
    contraerTodosGrupos,
    aplicarMargenGlobal,
    guardarMargenes,
    exportarItems,
    mostrarNotificacion
};

// Para compatibilidad con el HTML existente
window.cambiarTab = cambiarTab;
window.aplicarFiltros = aplicarFiltros;
window.limpiarFiltros = limpiarFiltros;
window.editarItem = editarItem;
window.eliminarItem = eliminarItem;
window.duplicarItem = duplicarItem;
window.openModal = openModal;
window.closeModal = closeModal;
window.mostrarEstadisticasDetalladas = mostrarEstadisticasDetalladas;
window.mostrarGestionGrupos = mostrarGestionGrupos;
window.toggleSeleccionMasiva = toggleSeleccionMasiva;
window.aplicarCambiosMasivos = aplicarCambiosMasivos;
window.manejarCambioGrupo = manejarCambioGrupo;
window.confirmarNuevoGrupo = confirmarNuevoGrupo;
window.toggleGrupo = toggleGrupo;
window.expandirTodosGrupos = expandirTodosGrupos;
window.contraerTodosGrupos = contraerTodosGrupos;
window.aplicarMargenGlobal = aplicarMargenGlobal;
window.guardarMargenes = guardarMargenes;
window.exportarItems = exportarItems;
window.crearNuevoGrupo = crearNuevoGrupo;
window.eliminarGrupo = eliminarGrupo;