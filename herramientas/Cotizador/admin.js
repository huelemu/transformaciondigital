// ================================
// VARIABLES GLOBALES
// ================================
let editingItemId = null;
let margenGlobal = 50;
let itemsData = [];
let filteredData = [];

// ================================
// INICIALIZACIÓN
// ================================
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosItems();
    configurarEventos();
    calcularEstadisticas();
    inicializacionCompleta();
});

// ================================
// CONFIGURACIÓN DE EVENTOS
// ================================
function configurarEventos() {
    // Búsqueda de items
    document.getElementById('search-items').addEventListener('input', aplicarFiltros);
    
    // Filtros de selección
    document.getElementById('filter-tipo').addEventListener('change', aplicarFiltros);
    document.getElementById('filter-categoria').addEventListener('change', aplicarFiltros);
    document.getElementById('filter-recurrencia').addEventListener('change', aplicarFiltros);

    // Formulario de item
    document.getElementById('item-form').addEventListener('submit', function(e) {
        if (!validarFormularioItem()) {
            e.preventDefault();
        }
    });

    // Formulario de categoría
    document.getElementById('categoria-form').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarCategoria();
    });

    // Margen global
    document.getElementById('margen-global').addEventListener('input', function() {
        margenGlobal = parseInt(this.value);
        actualizarVistasMargenes();
    });

    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl + N para nuevo item
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            openModal('item-modal');
        }
        
        // Escape para cerrar modales
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                modal.classList.remove('active');
            });
        }

        // Ctrl + S para guardar márgenes
        if (e.ctrlKey && e.key === 's' && document.getElementById('margenes-tab').classList.contains('active')) {
            e.preventDefault();
            guardarMargenes();
        }

        // Ctrl + Shift + A para análisis
        if (e.ctrlKey && e.key === 'a' && e.shiftKey) {
            e.preventDefault();
            generarAnalisisCompleto();
        }
        
        // Ctrl + B para backup
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            crearBackup();
        }
        
        // Ctrl + I para importar
        if (e.ctrlKey && e.key === 'i') {
            e.preventDefault();
            importarItems();
        }
    });

    // Validación en tiempo real
    configurarValidacionTiempoReal();
}

// ================================
// GESTIÓN DE PESTAÑAS
// ================================
function switchTab(tabName) {
    // Desactivar todas las pestañas
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

    // Activar pestaña seleccionada
    event.target.classList.add('active');
    document.getElementById(tabName + '-tab').classList.add('active');

    // Actualizar contenido según la pestaña
    switch(tabName) {
        case 'items':
            aplicarFiltros();
            break;
        case 'margenes':
            actualizarVistasMargenes();
            break;
        case 'categorias':
            // Categorías se cargan desde PHP
            break;
    }
}

// ================================
// CARGA DE DATOS
// ================================
function cargarDatosItems() {
    // Obtener datos de la tabla HTML
    const rows = document.querySelectorAll('#items-table .item-row');
    itemsData = [];
    
    rows.forEach(row => {
        const id = row.dataset.id;
        const cells = row.querySelectorAll('td');
        
        itemsData.push({
            id: id,
            tipo_costo: cells[0].textContent.trim(),
            categoria: cells[1].textContent.trim(),
            tipo_prod: cells[2].textContent.trim(),
            item: cells[3].querySelector('.text-truncate').textContent.trim(),
            costoUSD: parseFloat(cells[4].textContent.replace('$', '').replace(',', '')),
            margen: parseInt(cells[5].textContent.replace('%', '')),
            precioVenta: parseFloat(cells[6].textContent.replace('$', '').replace(',', ''))
        });
    });
    
    filteredData = [...itemsData];
}

// ================================
// SISTEMA DE FILTROS
// ================================
function aplicarFiltros() {
    const searchTerm = document.getElementById('search-items').value.toLowerCase();
    const tipoFilter = document.getElementById('filter-tipo').value;
    const categoriaFilter = document.getElementById('filter-categoria').value;
    const recurrenciaFilter = document.getElementById('filter-recurrencia').value;

    const rows = document.querySelectorAll('#items-table .item-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const item = {
            tipo: row.querySelector('.tag').textContent.trim(),
            categoria: row.children[1].textContent.trim(),
            producto: row.children[2].textContent.trim(),
            item: row.children[3].textContent.trim(),
            recurrencia: getRecurrenciaFromRow(row)
        };

        let visible = true;

        // Filtro de búsqueda
        if (searchTerm) {
            const searchableText = `${item.item} ${item.producto} ${item.categoria}`.toLowerCase();
            if (!searchableText.includes(searchTerm)) {
                visible = false;
            }
        }

        // Filtros de selección
        if (tipoFilter && item.tipo !== tipoFilter) visible = false;
        if (categoriaFilter && item.categoria !== categoriaFilter) visible = false;
        if (recurrenciaFilter && item.recurrencia !== recurrenciaFilter) visible = false;

        // Mostrar/ocultar fila
        row.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
    });

    // Actualizar contador
    document.getElementById('filters-count').textContent = `Mostrando ${visibleCount} de ${rows.length} items`;

    // Efecto visual si no hay resultados
    if (visibleCount === 0) {
        mostrarMensajeNoResultados();
    } else {
        ocultarMensajeNoResultados();
    }
}

function clearFilters() {
    document.getElementById('search-items').value = '';
    document.getElementById('filter-tipo').value = '';
    document.getElementById('filter-categoria').value = '';
    document.getElementById('filter-recurrencia').value = '';
    aplicarFiltros();
    mostrarNotificacion('Filtros limpiados', 'success');
}

function toggleFilters() {
    const filtersGrid = document.getElementById('filters-grid');
    filtersGrid.classList.toggle('collapsed');
}

// ================================
// GESTIÓN DE ITEMS
// ================================
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
    
    if (modalId === 'item-modal') {
        // Focus en el primer campo
        setTimeout(() => {
            document.getElementById('tipo_costo').focus();
        }, 100);
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = '';
    
    if (modalId === 'item-modal') {
        limpiarFormularioItem();
    }
}

function limpiarFormularioItem() {
    editingItemId = null;
    document.getElementById('item-form').reset();
    document.getElementById('edit-item-id').value = '';
    document.getElementById('item-modal-title').textContent = 'Nuevo Item';
    
    // Limpiar clases de validación
    document.querySelectorAll('.form-group').forEach(group => {
        group.classList.remove('valid', 'invalid');
    });
}

function editarItem(id) {
    editingItemId = id;
    
    // Buscar datos del item en la tabla
    const row = document.querySelector(`[data-id="${id}"]`);
    if (!row) return;

    // Llenar formulario con datos existentes
    const cells = row.querySelectorAll('td');
    
    document.getElementById('edit-item-id').value = id;
    document.getElementById('tipo_costo').value = cells[0].querySelector('.tag').textContent.trim();
    document.getElementById('categoria').value = cells[1].textContent.trim();
    document.getElementById('tipo_prod').value = cells[2].textContent.trim();
    document.getElementById('item').value = cells[3].querySelector('.text-truncate').textContent.trim();
    document.getElementById('costoUSD').value = cells[4].textContent.replace('$', '').replace(',', '');
    
    // Margen personalizado si existe
    const margenText = cells[5].querySelector('.margin-indicator').textContent.trim();
    const margen = parseInt(margenText.replace('%', ''));
    if (cells[5].querySelector('.positive')) {
        document.getElementById('margen_custom').value = margen;
    }

    document.getElementById('item-modal-title').textContent = 'Editar Item';
    openModal('item-modal');
}

function duplicarItem(id) {
    // Similar a editar pero sin ID
    editarItem(id);
    document.getElementById('edit-item-id').value = '';
    document.getElementById('item-modal-title').textContent = 'Duplicar Item';
    
    // Agregar sufijo al nombre
    const itemInput = document.getElementById('item');
    itemInput.value += ' (Copia)';
}

function eliminarItem(id) {
    const row = document.querySelector(`[data-id="${id}"]`);
    const itemName = row.querySelector('.text-truncate').textContent.trim();
    
    if (confirm(`¿Estás seguro de que quieres eliminar "${itemName}"?\n\nEsta acción no se puede deshacer.`)) {
        document.getElementById('delete-item-id').value = id;
        document.getElementById('delete-form').submit();
    }
}

// ================================
// GESTIÓN DE MÁRGENES
// ================================
function actualizarMargenItem(id, nuevoMargen) {
    const margenNum = parseInt(nuevoMargen);
    const input = document.querySelector(`[data-id="${id}"]`);
    
    if (!input) return;

    // Marcar como modificado
    if (margenNum !== parseInt(input.dataset.original)) {
        input.classList.add('changed');
    } else {
        input.classList.remove('changed');
    }

    // Actualizar precio de venta
    const row = input.closest('tr');
    const costoText = row.children[1].textContent.replace('$', '').replace(',', '');
    const costo = parseFloat(costoText);
    const precioVenta = costo / (1 - margenNum / 100);
    
    row.querySelector(`.precio-venta-${id}`).textContent = `$${precioVenta.toFixed(4)}`;

    // Actualizar diferencia
    const diferencia = margenNum - margenGlobal;
    const diferenciaElement = row.querySelector(`.diferencia-${id}`);
    diferenciaElement.textContent = `${diferencia > 0 ? '+' : ''}${diferencia}%`;
    diferenciaElement.className = `margin-indicator diferencia-${id} ${diferencia > 0 ? 'positive' : (diferencia < 0 ? 'negative' : 'neutral')}`;
}

function aplicarMargenGlobal() {
    const nuevoMargen = parseInt(document.getElementById('margen-global').value);
    
    if (confirm(`¿Aplicar margen del ${nuevoMargen}% a todos los items que no tengan margen personalizado?`)) {
        margenGlobal = nuevoMargen;
        
        document.querySelectorAll('.margin-input').forEach(input => {
            const id = input.dataset.id;
            const row = input.closest('tr');
            const status = row.querySelector('.status-indicator');
            
            // Solo actualizar items con margen global
            if (status.textContent.trim() === 'Global') {
                input.value = nuevoMargen;
                actualizarMargenItem(id, nuevoMargen);
            }
        });
        
        mostrarNotificacion(`Margen global actualizado a ${nuevoMargen}%`, 'success');
    }
}

function guardarMargenes() {
    const margenes = {};
    let hayChangios = false;
    
    document.querySelectorAll('.margin-input').forEach(input => {
        const id = input.dataset.id;
        const valor = parseInt(input.value);
        const original = parseInt(input.dataset.original);
        
        if (valor !== original) {
            margenes[id] = valor;
            hayChangios = true;
        }
    });

    if (!hayChangios) {
        mostrarNotificacion('No hay cambios para guardar', 'info');
        return;
    }

    document.getElementById('margins-data').value = JSON.stringify(margenes);
    document.getElementById('margins-form').submit();
}

function actualizarVistasMargenes() {
    // Recalcular todos los precios con el nuevo margen global
    document.querySelectorAll('.margin-input').forEach(input => {
        const id = input.dataset.id;
        const valor = parseInt(input.value);
        actualizarMargenItem(id, valor);
    });
}

// ================================
// VALIDACIONES
// ================================
function validarFormularioItem() {
    const campos = [
        { id: 'tipo_costo', name: 'Tipo de Costo' },
        { id: 'recurrencia', name: 'Recurrencia' },
        { id: 'categoria', name: 'Categoría' },
        { id: 'tipo_prod', name: 'Tipo de Producto' },
        { id: 'item', name: 'Nombre del Item' },
        { id: 'costoUSD', name: 'Costo USD' }
    ];

    let valido = true;
    
    campos.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        const grupo = elemento.closest('.form-group');
        const valor = elemento.value.trim();
        
        if (!valor) {
            grupo.classList.remove('valid');
            grupo.classList.add('invalid');
            mostrarErrorCampo(elemento, `${campo.name} es obligatorio`);
            valido = false;
        } else {
            grupo.classList.remove('invalid');
            grupo.classList.add('valid');
            ocultarErrorCampo(elemento);
        }
    });

    // Validaciones específicas
    const costoUSD = document.getElementById('costoUSD');
    if (costoUSD.value && (parseFloat(costoUSD.value) <= 0 || parseFloat(costoUSD.value) > 999999)) {
        costoUSD.closest('.form-group').classList.add('invalid');
        mostrarErrorCampo(costoUSD, 'El costo debe ser mayor a 0 y menor a $999,999');
        valido = false;
    }

    const margenCustom = document.getElementById('margen_custom');
    if (margenCustom.value && (parseInt(margenCustom.value) < 0 || parseInt(margenCustom.value) > 99)) {
        margenCustom.closest('.form-group').classList.add('invalid');
        mostrarErrorCampo(margenCustom, 'El margen debe estar entre 0% y 99%');
        valido = false;
    }

    return valido;
}

function configurarValidacionTiempoReal() {
    const campos = ['tipo_costo', 'recurrencia', 'categoria', 'tipo_prod', 'item', 'costoUSD', 'margen_custom'];
    
    campos.forEach(campoId => {
        const elemento = document.getElementById(campoId);
        if (elemento) {
            elemento.addEventListener('input', function() {
                validarCampoIndividual(this);
            });
            
            elemento.addEventListener('blur', function() {
                validarCampoIndividual(this);
            });
        }
    });
}

function validarCampoIndividual(elemento) {
    const grupo = elemento.closest('.form-group');
    const valor = elemento.value.trim();
    const esObligatorio = grupo.classList.contains('required');

    // Limpiar errores previos
    ocultarErrorCampo(elemento);
    grupo.classList.remove('valid', 'invalid');

    if (esObligatorio && !valor) {
        grupo.classList.add('invalid');
        mostrarErrorCampo(elemento, 'Este campo es obligatorio');
        return false;
    }

    // Validaciones específicas
    switch (elemento.id) {
        case 'costoUSD':
            if (valor && (parseFloat(valor) <= 0 || parseFloat(valor) > 999999)) {
                grupo.classList.add('invalid');
                mostrarErrorCampo(elemento, 'Debe ser un valor entre $0.01 y $999,999');
                return false;
            }
            break;
            
        case 'margen_custom':
            if (valor && (parseInt(valor) < 0 || parseInt(valor) > 99)) {
                grupo.classList.add('invalid');
                mostrarErrorCampo(elemento, 'Debe ser un porcentaje entre 0% y 99%');
                return false;
            }
            break;
            
        case 'item':
            if (valor && valor.length < 10) {
                grupo.classList.add('invalid');
                mostrarErrorCampo(elemento, 'La descripción debe tener al menos 10 caracteres');
                return false;
            }
            break;
    }

    if (valor) {
        grupo.classList.add('valid');
    }
    
    return true;
}

function mostrarErrorCampo(elemento, mensaje) {
    ocultarErrorCampo(elemento);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = mensaje;
    
    elemento.parentNode.appendChild(errorDiv);
}

function ocultarErrorCampo(elemento) {
    const errorExistente = elemento.parentNode.querySelector('.error-message');
    if (errorExistente) {
        errorExistente.remove();
    }
}

// ================================
// GESTIÓN DE CATEGORÍAS
// ================================
function guardarCategoria() {
    const nuevaCategoria = document.getElementById('nueva_categoria').value.trim();
    const descripcion = document.getElementById('categoria_descripcion').value.trim();
    const margenDefecto = document.getElementById('categoria_margen').value;

    if (!nuevaCategoria) {
        mostrarNotificacion('El nombre de la categoría es obligatorio', 'error');
        return;
    }

    // Verificar si la categoría ya existe
    const categoriaExistente = document.querySelector(`#filter-categoria option[value="${nuevaCategoria}"]`);
    if (categoriaExistente) {
        mostrarNotificacion('Ya existe una categoría con ese nombre', 'error');
        return;
    }

    // Agregar a la lista de categorías
    const select = document.getElementById('categoria');
    const option = document.createElement('option');
    option.value = nuevaCategoria;
    option.textContent = nuevaCategoria;
    select.appendChild(option);

    // Agregar al filtro también
    const filterSelect = document.getElementById('filter-categoria');
    const filterOption = document.createElement('option');
    filterOption.value = nuevaCategoria;
    filterOption.textContent = nuevaCategoria;
    filterSelect.appendChild(filterOption);

    closeModal('categoria-modal');
    mostrarNotificacion(`Categoría "${nuevaCategoria}" creada correctamente`, 'success');
    
    // Limpiar formulario
    document.getElementById('categoria-form').reset();
}

// ================================
// UTILIDADES Y HELPERS
// ================================
function getRecurrenciaFromRow(row) {
    // Obtener recurrencia del item (necesaria para filtros)
    // Se podría implementar leyendo desde atributos data o parseando contenido
    return 'Mensual'; // Placeholder - implementar según estructura real
}

function calcularEstadisticas() {
    // Las estadísticas se calculan en PHP, pero aquí podríamos actualizarlas dinámicamente
    // después de cambios
}

function mostrarMensajeNoResultados() {
    let mensaje = document.getElementById('no-results-message');
    if (!mensaje) {
        mensaje = document.createElement('tr');
        mensaje.id = 'no-results-message';
        mensaje.innerHTML = `
            <td colspan="8" class="text-center" style="padding: 2rem;">
                <div style="color: var(--text-secondary);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                    <div style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">No se encontraron resultados</div>
                    <div style="font-size: 0.875rem;">Intenta ajustar los filtros de búsqueda</div>
                </div>
            </td>
        `;
        document.getElementById('items-table').appendChild(mensaje);
    }
    mensaje.style.display = '';
}

function ocultarMensajeNoResultados() {
    const mensaje = document.getElementById('no-results-message');
    if (mensaje) {
        mensaje.style.display = 'none';
    }
}

// ================================
// SISTEMA DE NOTIFICACIONES
// ================================
function mostrarNotificacion(mensaje, tipo = 'info', duracion = 4000) {
    const iconos = {
        'success': '✅',
        'error': '❌',
        'warning': '⚠️',
        'info': 'ℹ️'
    };

    const notificacion = document.createElement('div');
    notificacion.className = `alert alert-${tipo}`;
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1001;
        min-width: 350px;
        max-width: 500px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        cursor: pointer;
    `;
    
    notificacion.innerHTML = `
        <span class="alert-icon">${iconos[tipo]}</span>
        <div class="alert-content">
            <div class="alert-message">${mensaje}</div>
        </div>
    `;

    // Cerrar al hacer clic
    notificacion.addEventListener('click', function() {
        ocultarNotificacion(notificacion);
    });

    document.body.appendChild(notificacion);

    // Mostrar animación
    setTimeout(() => {
        notificacion.style.opacity = '1';
        notificacion.style.transform = 'translateX(0)';
    }, 100);

    // Ocultar automáticamente
    setTimeout(() => {
        ocultarNotificacion(notificacion);
    }, duracion);
}

function ocultarNotificacion(notificacion) {
    notificacion.style.opacity = '0';
    notificacion.style.transform = 'translateX(100%)';
    
    setTimeout(() => {
        if (notificacion.parentNode) {
            notificacion.parentNode.removeChild(notificacion);
        }
    }, 300);
}

// ================================
// FUNCIONES DE EXPORTACIÓN/IMPORTACIÓN
// ================================
function exportarItems() {
    if (itemsData.length === 0) {
        mostrarNotificacion('No hay datos para exportar', 'error');
        return;
    }

    const headers = ['ID', 'Tipo', 'Recurrencia', 'Categoría', 'Producto', 'Item', 'Costo USD', 'Margen %'];
    const csvContent = [
        headers.join(','),
        ...itemsData.map(item => [
            item.id,
            `"${item.tipo_costo}"`,
            `"${getRecurrenciaFromRow(document.querySelector(`[data-id="${item.id}"]`))}"`,
            `"${item.categoria}"`,
            `"${item.tipo_prod}"`,
            `"${item.item}"`,
            item.costoUSD,
            item.margen
        ].join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `items_administracion_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarNotificacion('Items exportados correctamente', 'success');
}

function importarItems() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.csv,.json';
    input.onchange = function(event) {
        const file = event.target.files[0];
        if (file) {
            procesarArchivoImportacion(file);
        }
    };
    input.click();
}

function procesarArchivoImportacion(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            let data;
            if (file.name.endsWith('.json')) {
                data = JSON.parse(e.target.result);
            } else {
                // Procesar CSV
                const lines = e.target.result.split('\n');
                const headers = lines[0].split(',').map(h => h.trim().replace(/"/g, ''));
                data = lines.slice(1).filter(line => line.trim()).map(line => {
                    const values = line.split(',').map(v => v.trim().replace(/"/g, ''));
                    const item = {};
                    headers.forEach((header, index) => {
                        item[header] = values[index] || '';
                    });
                    return item;
                });
            }
            procesarImportacion(data);
        } catch (error) {
            mostrarNotificacion('Error al procesar el archivo: ' + error.message, 'error');
        }
    };
    reader.readAsText(file);
}

function procesarImportacion(data) {
    if (!Array.isArray(data) || data.length === 0) {
        mostrarNotificacion('El archivo no contiene datos válidos', 'error');
        return;
    }

    const itemsValidos = data.filter(item => item.item && item.costoUSD);
    
    if (itemsValidos.length === 0) {
        mostrarNotificacion('No se encontraron items válidos en el archivo', 'error');
        return;
    }

    if (confirm(`¿Importar ${itemsValidos.length} items? Esta acción agregará nuevos items a la lista existente.`)) {
        // En un sistema real, aquí se haría una llamada AJAX al servidor
        mostrarNotificacion(`Se importarían ${itemsValidos.length} items correctamente`, 'info');
        console.log('Items a importar:', itemsValidos);
    }
}

// ================================
// FUNCIONES DE ANÁLISIS
// ================================
function generarAnalisisCompleto() {
    const analisis = {
        resumen: {
            totalItems: itemsData.length,
            itemsFijos: itemsData.filter(i => i.tipo_costo === 'Fijo').length,
            itemsVariables: itemsData.filter(i => i.tipo_costo === 'Variable').length,
            margenPromedio: itemsData.reduce((sum, i) => sum + i.margen, 0) / itemsData.length
        },
        categorias: analizarPorCategoria(),
        costos: analizarDistribucionCostos(),
        margenes: analizarDistribucionMargenes(),
        recomendaciones: generarRecomendaciones()
    };

    console.log('📊 Análisis Completo:', analisis);
    mostrarModalAnalisis(analisis);
}

function analizarPorCategoria() {
    const categorias = {};
    
    itemsData.forEach(item => {
        if (!categorias[item.categoria]) {
            categorias[item.categoria] = {
                cantidad: 0,
                costoTotal: 0,
                margenPromedio: 0,
                items: []
            };
        }
        
        categorias[item.categoria].cantidad++;
        categorias[item.categoria].costoTotal += item.costoUSD;
        categorias[item.categoria].margenPromedio += item.margen;
        categorias[item.categoria].items.push(item);
    });

    // Calcular promedios
    Object.keys(categorias).forEach(cat => {
        categorias[cat].margenPromedio = categorias[cat].margenPromedio / categorias[cat].cantidad;
        categorias[cat].costoPromedio = categorias[cat].costoTotal / categorias[cat].cantidad;
    });

    return categorias;
}

function analizarDistribucionCostos() {
    const costos = itemsData.map(i => i.costoUSD).sort((a, b) => a - b);
    const total = costos.length;
    
    return {
        minimo: costos[0] || 0,
        maximo: costos[total - 1] || 0,
        mediana: total > 0 ? costos[Math.floor(total / 2)] : 0,
        promedio: costos.reduce((sum, c) => sum + c, 0) / total,
        q1: total > 0 ? costos[Math.floor(total * 0.25)] : 0,
        q3: total > 0 ? costos[Math.floor(total * 0.75)] : 0
    };
}

function analizarDistribucionMargenes() {
    const margenes = itemsData.map(i => i.margen).sort((a, b) => a - b);
    const total = margenes.length;
    
    return {
        minimo: margenes[0] || 0,
        maximo: margenes[total - 1] || 0,
        mediana: total > 0 ? margenes[Math.floor(total / 2)] : 0,
        promedio: margenes.reduce((sum, m) => sum + m, 0) / total,
        distribucion: {
            bajo: margenes.filter(m => m < 30).length,
            medio: margenes.filter(m => m >= 30 && m <= 60).length,
            alto: margenes.filter(m => m > 60).length
        }
    };
}

function generarRecomendaciones() {
    const recomendaciones = [];
    const costos = analizarDistribucionCostos();
    const margenes = analizarDistribucionMargenes();

    // Recomendaciones basadas en distribución de costos
    if (costos.maximo > costos.promedio * 10) {
        recomendaciones.push({
            tipo: 'warning',
            titulo: 'Items de costo muy elevado',
            descripcion: 'Algunos items tienen costos significativamente altos. Revisar su necesidad.',
            accion: 'Revisar items con costo >  + (costos.promedio * 5).toFixed(2)
        });
    }

    // Recomendaciones basadas en márgenes
    if (margenes.distribucion.bajo > margenes.distribucion.alto) {
        recomendaciones.push({
            tipo: 'info',
            titulo: 'Oportunidad de mejora en márgenes',
            descripcion: 'Muchos items tienen márgenes bajos. Evaluar posibilidad de ajuste.',
            accion: 'Revisar ' + margenes.distribucion.bajo + ' items con margen < 30%'
        });
    }

    // Recomendaciones basadas en categorías
    const categorias = analizarPorCategoria();
    const categoriasCount = Object.keys(categorias).length;
    
    if (categoriasCount < 3) {
        recomendaciones.push({
            tipo: 'info',
            titulo: 'Diversificar categorías',
            descripcion: 'Considerar agregar más categorías de productos/servicios.',
            accion: 'Explorar nuevas líneas de negocio'
        });
    }

    return recomendaciones;
}

function mostrarModalAnalisis(analisis) {
    // Crear modal dinámico para mostrar análisis
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">📊 Análisis Completo de Items</h3>
                <button class="close-btn" onclick="this.closest('.modal').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Total de Items</label>
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">${analisis.resumen.totalItems}</div>
                    </div>
                    <div class="form-group">
                        <label>Margen Promedio</label>
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--success-color);">${analisis.resumen.margenPromedio.toFixed(1)}%</div>
                    </div>
                </div>
                
                <h4 style="margin: 1.5rem 0 1rem;">Distribución de Costos</h4>
                <div class="form-grid">
                    <div><strong>Mínimo:</strong> ${analisis.costos.minimo.toFixed(2)}</div>
                    <div><strong>Máximo:</strong> ${analisis.costos.maximo.toFixed(2)}</div>
                    <div><strong>Promedio:</strong> ${analisis.costos.promedio.toFixed(2)}</div>
                    <div><strong>Mediana:</strong> ${analisis.costos.mediana.toFixed(2)}</div>
                </div>

                <h4 style="margin: 1.5rem 0 1rem;">Recomendaciones</h4>
                ${analisis.recomendaciones.map(rec => `
                    <div class="alert alert-${rec.tipo}">
                        <strong>${rec.titulo}</strong><br>
                        ${rec.descripcion}<br>
                        <em>Acción sugerida: ${rec.accion}</em>
                    </div>
                `).join('')}
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">Cerrar</button>
                <button class="btn btn-primary" onclick="exportarAnalisis()">Exportar Análisis</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function exportarAnalisis() {
    const analisis = {
        fecha: new Date().toISOString(),
        resumen: {
            totalItems: itemsData.length,
            itemsFijos: itemsData.filter(i => i.tipo_costo === 'Fijo').length,
            itemsVariables: itemsData.filter(i => i.tipo_costo === 'Variable').length,
            margenPromedio: itemsData.reduce((sum, i) => sum + i.margen, 0) / itemsData.length
        },
        categorias: analizarPorCategoria(),
        costos: analizarDistribucionCostos(),
        margenes: analizarDistribucionMargenes(),
        recomendaciones: generarRecomendaciones()
    };

    const blob = new Blob([JSON.stringify(analisis, null, 2)], { type: 'application/json' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `analisis_items_${new Date().toISOString().split('T')[0]}.json`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarNotificacion('Análisis exportado correctamente', 'success');
}

// ================================
// FUNCIONES DE BACKUP Y RESTAURACIÓN
// ================================
function crearBackup() {
    const backup = {
        fecha: new Date().toISOString(),
        version: '1.0',
        items: itemsData,
        configuracion: {
            margenGlobal: margenGlobal
        }
    };

    const blob = new Blob([JSON.stringify(backup, null, 2)], { type: 'application/json' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `backup_items_${new Date().toISOString().split('T')[0]}.json`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarNotificacion('Backup creado correctamente', 'success');
}

function restaurarBackup() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    input.onchange = function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const backup = JSON.parse(e.target.result);
                    
                    if (!backup.items || !Array.isArray(backup.items)) {
                        throw new Error('Formato de backup inválido');
                    }

                    if (confirm(`¿Restaurar backup del ${new Date(backup.fecha).toLocaleDateString()}?\n\nEsto reemplazará todos los datos actuales.`)) {
                        // En un sistema real, aquí se enviarían los datos al servidor
                        console.log('Backup a restaurar:', backup);
                        mostrarNotificacion('Backup restaurado correctamente (simulado)', 'success');
                    }
                } catch (error) {
                    mostrarNotificacion('Error al procesar el backup: ' + error.message, 'error');
                }
            };
            reader.readAsText(file);
        }
    };
    input.click();
}

// ================================
// DRAG AND DROP PARA IMPORTACIÓN
// ================================
function configurarDragAndDrop() {
    const dropZones = document.querySelectorAll('.filters-container, .table-container');
    
    dropZones.forEach(zone => {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        zone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type === 'text/csv' || file.type === 'application/json' || file.name.endsWith('.csv') || file.name.endsWith('.json')) {
                    procesarArchivoImportacion(file);
                } else {
                    mostrarNotificacion('Solo se permiten archivos CSV o JSON', 'error');
                }
            }
        });
    });
}

// ================================
// BÚSQUEDA AVANZADA CON SUGERENCIAS
// ================================
function configurarBusquedaAvanzada() {
    const searchInput = document.getElementById('search-items');
    let timeoutId;

    searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            mostrarSugerenciasBusqueda(this.value);
        }, 300);
    });

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            ocultarSugerencias();
        }
    });
}

function mostrarSugerenciasBusqueda(termino) {
    if (termino.length < 2) {
        ocultarSugerencias();
        return;
    }

    const sugerencias = obtenerSugerencias(termino);
    
    if (sugerencias.length === 0) {
        ocultarSugerencias();
        return;
    }

    let contenedorSugerencias = document.getElementById('search-suggestions');
    if (!contenedorSugerencias) {
        contenedorSugerencias = document.createElement('div');
        contenedorSugerencias.id = 'search-suggestions';
        contenedorSugerencias.className = 'search-results';
        
        // Buscar contenedor padre
        const searchContainer = document.querySelector('.filters-container');
        if (searchContainer) {
            searchContainer.style.position = 'relative';
            searchContainer.appendChild(contenedorSugerencias);
        }
    }

    contenedorSugerencias.innerHTML = sugerencias.map(sugerencia => `
        <div class="search-result-item" onclick="aplicarSugerencia('${sugerencia}')">
            ${resaltarTermino(sugerencia, termino)}
        </div>
    `).join('');

    contenedorSugerencias.classList.add('active');
}

function obtenerSugerencias(termino) {
    const terminoLower = termino.toLowerCase();
    const sugerencias = new Set();

    itemsData.forEach(item => {
        // Buscar en diferentes campos
        const campos = [item.item, item.categoria, item.tipo_prod, item.tipo_costo];
        
        campos.forEach(campo => {
            if (campo && campo.toLowerCase().includes(terminoLower)) {
                // Agregar palabras que contengan el término
                const palabras = campo.split(' ');
                palabras.forEach(palabra => {
                    if (palabra.toLowerCase().includes(terminoLower) && palabra.length > 2) {
                        sugerencias.add(palabra);
                    }
                });
                
                // Si coincide exactamente con algún campo, agregarlo completo
                if (campo.toLowerCase().startsWith(terminoLower)) {
                    sugerencias.add(campo);
                }
            }
        });
    });

    return Array.from(sugerencias).slice(0, 8); // Máximo 8 sugerencias
}

function resaltarTermino(texto, termino) {
    const regex = new RegExp(`(${termino})`, 'gi');
    return texto.replace(regex, '<strong>$1</strong>');
}

function aplicarSugerencia(sugerencia) {
    document.getElementById('search-items').value = sugerencia;
    aplicarFiltros();
    ocultarSugerencias();
}

function ocultarSugerencias() {
    const contenedor = document.getElementById('search-suggestions');
    if (contenedor) {
        contenedor.classList.remove('active');
    }
}

// ================================
// GUARDADO AUTOMÁTICO
// ================================
function configurarGuardadoAutomatico() {
    let tiempoEspera;
    const TIEMPO_GUARDADO = 3000; // 3 segundos

    // Detectar cambios en inputs de margen
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('margin-input')) {
            clearTimeout(tiempoEspera);
            
            // Mostrar indicador de cambios pendientes
            mostrarIndicadorCambiosPendientes();
            
            tiempoEspera = setTimeout(() => {
                if (hayMargenesCambiados()) {
                    guardarMargenes();
                    ocultarIndicadorCambiosPendientes();
                }
            }, TIEMPO_GUARDADO);
        }
    });
}

function hayMargenesCambiados() {
    return document.querySelector('.margin-input.changed') !== null;
}

function mostrarIndicadorCambiosPendientes() {
    let indicador = document.getElementById('cambios-pendientes');
    if (!indicador) {
        indicador = document.createElement('div');
        indicador.id = 'cambios-pendientes';
        indicador.innerHTML = `
            <div style="position: fixed; bottom: 20px; left: 20px; background: var(--warning-color); color: white; padding: 0.75rem 1rem; border-radius: 8px; box-shadow: var(--shadow-lg); z-index: 1000;">
                <span>💾</span> Cambios pendientes de guardar...
            </div>
        `;
        document.body.appendChild(indicador);
    }
}

function ocultarIndicadorCambiosPendientes() {
    const indicador = document.getElementById('cambios-pendientes');
    if (indicador) {
        indicador.remove();
    }
}

// ================================
// INICIALIZACIÓN COMPLETA
// ================================
function inicializacionCompleta() {
    configurarDragAndDrop();
    configurarBusquedaAvanzada();
    configurarGuardadoAutomatico();
    
    // Agregar botones de análisis
    agregarBotonesAnalisis();
    
    // Configurar tooltips avanzados
    configurarTooltipsAvanzados();
    
    // Mostrar consejos al usuario
    setTimeout(() => {
        if (itemsData.length === 0) {
            mostrarNotificacion('¡Bienvenido! Comienza agregando tu primer item con Ctrl+N', 'info', 6000);
        } else if (itemsData.length > 50) {
            mostrarNotificacion('Tienes muchos items. Usa los filtros para encontrar lo que buscas más rápido.', 'info', 5000);
        }
    }, 2000);
}

function agregarBotonesAnalisis() {
    const headerActions = document.querySelector('.header-actions');
    if (headerActions) {
        const btnAnalisis = document.createElement('button');
        btnAnalisis.className = 'btn btn-secondary';
        btnAnalisis.innerHTML = '<span>📊</span> Análisis';
        btnAnalisis.onclick = generarAnalisisCompleto;
        headerActions.appendChild(btnAnalisis);

        const btnExportar = document.createElement('button');
        btnExportar.className = 'btn btn-secondary';
        btnExportar.innerHTML = '<span>📤</span> Exportar';
        btnExportar.onclick = exportarItems;
        headerActions.appendChild(btnExportar);

        const btnImportar = document.createElement('button');
        btnImportar.className = 'btn btn-secondary';
        btnImportar.innerHTML = '<span>📥</span> Importar';
        btnImportar.onclick = importarItems;
        headerActions.appendChild(btnImportar);
    }
}

function configurarTooltipsAvanzados() {
    // Agregar tooltips informativos a elementos clave
    const elementos = [
        { selector: '#margen-global', texto: 'Este margen se aplica a todos los items que no tengan margen personalizado' },
        { selector: '.margin-input', texto: 'Haz clic para editar el margen específico de este item' },
        { selector: '.filter-input', texto: 'Escribe para filtrar los resultados en tiempo real' }
    ];

    elementos.forEach(elem => {
        const element = document.querySelector(elem.selector);
        if (element) {
            element.setAttribute('data-tooltip', elem.texto);
            element.classList.add('tooltip-advanced');
        }
    });
}

// ================================
// REPORTES AVANZADOS
// ================================
function generarReporteCategoria(categoria) {
    const itemsCategoria = itemsData.filter(item => item.categoria === categoria);
    
    if (itemsCategoria.length === 0) {
        mostrarNotificacion(`No hay items en la categoría "${categoria}"`, 'info');
        return;
    }

    const reporte = {
        categoria: categoria,
        totalItems: itemsCategoria.length,
        costoPromedio: itemsCategoria.reduce((sum, item) => sum + item.costoUSD, 0) / itemsCategoria.length,
        margenPromedio: itemsCategoria.reduce((sum, item) => sum + item.margen, 0) / itemsCategoria.length,
        costoTotal: itemsCategoria.reduce((sum, item) => sum + item.costoUSD, 0),
        precioTotal: itemsCategoria.reduce((sum, item) => sum + item.precioVenta, 0),
        itemMasCaro: itemsCategoria.reduce((max, item) => item.costoUSD > max.costoUSD ? item : max),
        itemMasBarato: itemsCategoria.reduce((min, item) => item.costoUSD < min.costoUSD ? item : min)
    };

    console.log(`Reporte de categoría "${categoria}":`, reporte);
    mostrarNotificacion(`Reporte generado para "${categoria}" (ver consola)`, 'success');
}

// ================================
// AUTOCOMPLETADO Y SUGERENCIAS
// ================================
function configurarAutocompletado() {
    const tiposProductoComunes = ['WhatsApp', 'Email', 'SMS', 'Omnicanalidad', 'IA', 'CiberSecurity', 'VoIP', 'Chat', 'Video'];
    
    const tipoProductoInput = document.getElementById('tipo_prod');
    if (tipoProductoInput) {
        tipoProductoInput.addEventListener('input', function() {
            const valor = this.value.toLowerCase();
            if (valor.length >= 2) {
                const sugerencias = tiposProductoComunes.filter(tipo => 
                    tipo.toLowerCase().includes(valor)
                );
                
                if (sugerencias.length > 0) {
                    mostrarSugerenciasAutocompletado(this, sugerencias);
                }
            }
        });
    }
}

function mostrarSugerenciasAutocompletado(input, sugerencias) {
    // Implementar dropdown de sugerencias para autocompletado
    let dropdown = input.parentNode.querySelector('.autocomplete-dropdown');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.className = 'autocomplete-dropdown search-results';
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(dropdown);
    }

    dropdown.innerHTML = sugerencias.map(sugerencia => `
        <div class="search-result-item" onclick="seleccionarSugerencia('${input.id}', '${sugerencia}')">
            ${sugerencia}
        </div>
    `).join('');

    dropdown.classList.add('active');
}

function seleccionarSugerencia(inputId, valor) {
    document.getElementById(inputId).value = valor;
    
    // Ocultar dropdown
    const dropdown = document.getElementById(inputId).parentNode.querySelector('.autocomplete-dropdown');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
}

// ================================
// EXPORTAR FUNCIONES GLOBALES
// ================================
window.switchTab = switchTab;
window.openModal = openModal;
window.closeModal = closeModal;
window.editarItem = editarItem;
window.duplicarItem = duplicarItem;
window.eliminarItem = eliminarItem;
window.actualizarMargenItem = actualizarMargenItem;
window.aplicarMargenGlobal = aplicarMargenGlobal;
window.guardarMargenes = guardarMargenes;
window.clearFilters = clearFilters;
window.toggleFilters = toggleFilters;
window.exportarItems = exportarItems;
window.importarItems = importarItems;
window.crearBackup = crearBackup;
window.restaurarBackup = restaurarBackup;
window.generarAnalisisCompleto = generarAnalisisCompleto;
window.aplicarSugerencia = aplicarSugerencia;
window.exportarAnalisis = exportarAnalisis;
window.seleccionarSugerencia = seleccionarSugerencia;
window.generarReporteCategoria = generarReporteCategoria;