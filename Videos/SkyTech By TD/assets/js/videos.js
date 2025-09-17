/**
 * JAVASCRIPT PARA SECCIÓN DE VIDEOS - SkyTech By TD
 * Funciones para manejar módulos colapsables y navegación de videos
 */

/**
 * Función principal para toggle de módulos
 * @param {number} moduleNumber - Número del módulo a expandir/contraer
 */
function toggleModule(moduleNumber) {
    const header = event.currentTarget;
    const content = document.getElementById('module-' + moduleNumber);
    const icon = header.querySelector('.module-icon');
    
    // Toggle active state
    header.classList.toggle('active');
    content.classList.toggle('active');
    
    // Animate icon rotation
    if (header.classList.contains('active')) {
        icon.style.transform = 'rotate(90deg)';
        content.style.display = 'block';
        
        // Opcional: scroll suave hasta el módulo abierto
        setTimeout(() => {
            header.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }, 100);
    } else {
        icon.style.transform = 'rotate(0deg)';
        content.style.display = 'none';
    }
}

/**
 * Función alternativa para toggle exclusivo (cierra otros módulos)
 * @param {number} moduleNumber - Número del módulo a abrir
 */
function toggleModuleExclusive(moduleNumber) {
    // Close all modules first
    const allHeaders = document.querySelectorAll('.module-header');
    const allContents = document.querySelectorAll('.module-content');
    
    allHeaders.forEach(header => {
        header.classList.remove('active');
        const icon = header.querySelector('.module-icon');
        if (icon) {
            icon.style.transform = 'rotate(0deg)';
        }
    });
    
    allContents.forEach(content => {
        content.classList.remove('active');
        content.style.display = 'none';
    });
    
    // Open selected module
    const selectedHeader = document.querySelector(`[onclick*="toggleModuleExclusive(${moduleNumber})"]`);
    const selectedContent = document.getElementById('module-' + moduleNumber);
    
    if (selectedHeader && selectedContent) {
        selectedHeader.classList.add('active');
        selectedContent.classList.add('active');
        const icon = selectedHeader.querySelector('.module-icon');
        if (icon) {
            icon.style.transform = 'rotate(90deg)';
        }
        selectedContent.style.display = 'block';
    }
}

/**
 * Función para expandir todos los módulos
 */
function expandAllModules() {
    const allHeaders = document.querySelectorAll('.module-header');
    const allContents = document.querySelectorAll('.module-content');
    
    allHeaders.forEach(header => {
        header.classList.add('active');
        const icon = header.querySelector('.module-icon');
        if (icon) {
            icon.style.transform = 'rotate(90deg)';
        }
    });
    
    allContents.forEach(content => {
        content.classList.add('active');
        content.style.display = 'block';
    });
}

/**
 * Función para contraer todos los módulos
 */
function collapseAllModules() {
    const allHeaders = document.querySelectorAll('.module-header');
    const allContents = document.querySelectorAll('.module-content');
    
    allHeaders.forEach(header => {
        header.classList.remove('active');
        const icon = header.querySelector('.module-icon');
        if (icon) {
            icon.style.transform = 'rotate(0deg)';
        }
    });
    
    allContents.forEach(content => {
        content.classList.remove('active');
        content.style.display = 'none';
    });
}

/**
 * Función para inicializar tooltips en los videos (opcional)
 */
function initVideoTooltips() {
    const videoItems = document.querySelectorAll('.video-item');
    
    videoItems.forEach(item => {
        const title = item.querySelector('.video-title');
        if (title && title.textContent.length > 50) {
            title.setAttribute('title', title.textContent);
        }
    });
}

/**
 * Función para tracking de reproducción de videos (opcional)
 * Se puede conectar con Google Analytics u otros sistemas
 */
function trackVideoView(videoTitle, moduleNumber) {
    console.log(`Video visto: ${videoTitle} en módulo ${moduleNumber}`);
    
    // Ejemplo de envío a Google Analytics (si está configurado)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'video_view', {
            'video_title': videoTitle,
            'module_number': moduleNumber,
            'timestamp': new Date().toISOString()
        });
    }
}

/**
 * Función para búsqueda de videos (opcional - para implementar después)
 */
function searchVideos(searchTerm) {
    const videoItems = document.querySelectorAll('.video-item');
    const term = searchTerm.toLowerCase();
    
    videoItems.forEach(item => {
        const title = item.querySelector('.video-title').textContent.toLowerCase();
        const description = item.querySelector('.video-description').textContent.toLowerCase();
        
        if (title.includes(term) || description.includes(term)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Inicialización cuando la página carga
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    initVideoTooltips();
    
    // Agregar listeners para tracking (opcional)
    const videoContainers = document.querySelectorAll('.video-container iframe');
    videoContainers.forEach((iframe, index) => {
        const videoItem = iframe.closest('.video-item');
        const title = videoItem.querySelector('.video-title').textContent;
        const moduleContainer = iframe.closest('.module-container');
        const moduleNumber = Array.from(moduleContainer.parentElement.children).indexOf(moduleContainer) + 1;
        
        // Tracking cuando se hace clic en el iframe
        iframe.addEventListener('load', () => {
            iframe.addEventListener('click', () => {
                trackVideoView(title, moduleNumber);
            });
        });
    });
    
    console.log('Sistema de videos SkyTech inicializado correctamente');
});

/**
 * Función para agregar nuevos videos dinámicamente (para uso futuro)
 */
function addVideo(moduleNumber, videoData) {
    const moduleContent = document.getElementById(`module-${moduleNumber}`);
    const videoGrid = moduleContent.querySelector('.video-grid');
    
    const videoItem = document.createElement('div');
    videoItem.className = 'video-item';
    videoItem.innerHTML = `
        <div class="video-container">
            <iframe src="${videoData.url}" 
                    frameborder="0" 
                    allow="autoplay; fullscreen;" 
                    title="${videoData.title}">
            </iframe>
        </div>
        <div class="video-info">
            <h3 class="video-title">${videoData.title}</h3>
            <p class="video-description">${videoData.description}</p>
            <div class="video-meta">
                <span class="video-duration">${videoData.duration}</span>
                <span class="video-date">${videoData.date}</span>
            </div>
        </div>
    `;
    
    videoGrid.appendChild(videoItem);
    
    // Actualizar estadísticas del módulo
    updateModuleStats(moduleNumber);
}

/**
 * Función para actualizar estadísticas del módulo
 */
function updateModuleStats(moduleNumber) {
    const moduleContent = document.getElementById(`module-${moduleNumber}`);
    const videos = moduleContent.querySelectorAll('.video-item');
    const statsElement = document.querySelector(`[onclick*="${moduleNumber}"] .module-stats`);
    
    if (statsElement) {
        const videoCountElement = statsElement.querySelector('.stats-item:first-child span:last-child');
        if (videoCountElement) {
            videoCountElement.textContent = `${videos.length} videos`;
        }
    }
}