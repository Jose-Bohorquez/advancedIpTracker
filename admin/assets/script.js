/**
 * Advanced IP Tracker - JavaScript del Panel de Administración
 * Funcionalidades interactivas para el dashboard y generador de enlaces
 */

// Variables globales
let currentData = [];
let filteredData = [];
let currentPage = 1;
const itemsPerPage = 10;
let sortColumn = 'timestamp';
let sortDirection = 'desc';

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupEventListeners();
    loadStatistics();
    loadRecentCaptures();
});

/**
 * Inicializar el dashboard
 */
function initializeDashboard() {
    console.log('Inicializando Advanced IP Tracker Dashboard...');
    
    // Verificar si estamos en la página del dashboard
    if (document.getElementById('capturesTable')) {
        loadRecentCaptures();
        setupTableSorting();
        setupPagination();
    }
    
    // Verificar si estamos en la página del generador de enlaces
    if (document.getElementById('linkForm')) {
        setupLinkGenerator();
        loadExistingLinks();
    }
    
    // Configurar actualización automática cada 30 segundos
    setInterval(function() {
        if (document.getElementById('capturesTable')) {
            loadRecentCaptures(false); // Sin mostrar loading
        }
        loadStatistics(false);
    }, 30000);
}

/**
 * Configurar event listeners
 */
function setupEventListeners() {
    // Búsqueda en tiempo real
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterData(this.value);
        });
    }
    
    // Filtros
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', applyFilters);
    });
    
    // Botones de acción
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-view')) {
            const filename = e.target.dataset.filename;
            viewCaptureDetails(filename);
        }
        
        if (e.target.classList.contains('btn-delete')) {
            const filename = e.target.dataset.filename;
            deleteCaptureFile(filename);
        }
        
        if (e.target.classList.contains('btn-copy-link')) {
            const link = e.target.dataset.link;
            copyToClipboard(link);
        }
        
        if (e.target.classList.contains('btn-delete-link')) {
            const linkId = e.target.dataset.linkId;
            deleteLinkFile(linkId);
        }
    });
    
    // Cerrar modales
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal') || e.target.classList.contains('close')) {
            closeModal();
        }
    });
    
    // Escape para cerrar modales
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
}

/**
 * Cargar estadísticas generales
 */
async function loadStatistics(showLoading = true) {
    try {
        if (showLoading) {
            showLoadingStats();
        }
        
        const response = await fetch('dashboard.php?action=stats');
        const stats = await response.json();
        
        if (stats.success) {
            updateStatisticsDisplay(stats.data);
            updateCharts(stats.data);
        } else {
            console.error('Error cargando estadísticas:', stats.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error cargando estadísticas', 'danger');
    }
}

/**
 * Actualizar display de estadísticas
 */
function updateStatisticsDisplay(stats) {
    const elements = {
        'totalCaptures': stats.total_captures || 0,
        'uniqueIPs': stats.unique_ips || 0,
        'totalCountries': stats.countries || 0,
        'totalSize': formatFileSize(stats.total_size || 0),
        'dateRange': stats.date_range || 'N/A'
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            if (typeof value === 'number') {
                animateNumber(element, value);
            } else {
                element.textContent = value;
            }
        }
    });
}

/**
 * Animar números en las estadísticas
 */
function animateNumber(element, targetValue) {
    const startValue = parseInt(element.textContent) || 0;
    const duration = 1000;
    const startTime = performance.now();
    
    function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
        element.textContent = currentValue.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        }
    }
    
    requestAnimationFrame(updateNumber);
}

/**
 * Cargar capturas recientes
 */
async function loadRecentCaptures(showLoading = true) {
    try {
        if (showLoading) {
            showLoadingTable();
        }
        
        const response = await fetch('dashboard.php?action=list');
        const result = await response.json();
        
        if (result.success) {
            currentData = result.data;
            filteredData = [...currentData];
            displayCapturesTable();
            setupPagination();
        } else {
            console.error('Error cargando capturas:', result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error cargando datos', 'danger');
    }
}

/**
 * Mostrar tabla de capturas
 */
function displayCapturesTable() {
    const tbody = document.querySelector('#capturesTable tbody');
    if (!tbody) return;
    
    // Aplicar ordenamiento
    sortData();
    
    // Calcular paginación
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageData = filteredData.slice(startIndex, endIndex);
    
    if (pageData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted">
                    <div class="p-4">
                        <div style="font-size: 3em; margin-bottom: 10px;">📭</div>
                        <div>No hay capturas disponibles</div>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = pageData.map(capture => {
        const timestamp = new Date(capture.timestamp).toLocaleString('es-ES');
        const country = capture.country || 'Desconocido';
        const city = capture.city || 'Desconocida';
        const browser = capture.browser || 'Desconocido';
        const os = capture.os || 'Desconocido';
        const device = capture.device || 'Desconocido';
        
        return `
            <tr class="fade-in">
                <td><code>${capture.ip}</code></td>
                <td>${timestamp}</td>
                <td>
                    <span class="badge badge-info">${country}</span>
                    <small class="text-muted d-block">${city}</small>
                </td>
                <td>${browser}</td>
                <td>${os}</td>
                <td>${device}</td>
                <td>
                    <span class="badge ${capture.geolocation ? 'badge-success' : 'badge-secondary'}">
                        ${capture.geolocation ? '✓ Sí' : '✗ No'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm btn-view" data-filename="${capture.filename}" title="Ver detalles">
                        👁️ Ver
                    </button>
                    <button class="btn btn-danger btn-sm btn-delete" data-filename="${capture.filename}" title="Eliminar">
                        🗑️ Eliminar
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Configurar ordenamiento de tabla
 */
function setupTableSorting() {
    const headers = document.querySelectorAll('#capturesTable th[data-sort]');
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'desc';
            }
            
            // Actualizar indicadores visuales
            headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            this.classList.add(`sort-${sortDirection}`);
            
            displayCapturesTable();
        });
    });
}

/**
 * Ordenar datos
 */
function sortData() {
    filteredData.sort((a, b) => {
        let aVal = a[sortColumn];
        let bVal = b[sortColumn];
        
        // Manejar diferentes tipos de datos
        if (sortColumn === 'timestamp') {
            aVal = new Date(aVal).getTime();
            bVal = new Date(bVal).getTime();
        } else if (typeof aVal === 'string') {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
        }
        
        if (sortDirection === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });
}

/**
 * Filtrar datos
 */
function filterData(searchTerm) {
    if (!searchTerm.trim()) {
        filteredData = [...currentData];
    } else {
        const term = searchTerm.toLowerCase();
        filteredData = currentData.filter(capture => {
            return Object.values(capture).some(value => 
                String(value).toLowerCase().includes(term)
            );
        });
    }
    
    currentPage = 1;
    displayCapturesTable();
    setupPagination();
}

/**
 * Aplicar filtros
 */
function applyFilters() {
    const countryFilter = document.getElementById('countryFilter')?.value;
    const browserFilter = document.getElementById('browserFilter')?.value;
    const osFilter = document.getElementById('osFilter')?.value;
    
    filteredData = currentData.filter(capture => {
        let matches = true;
        
        if (countryFilter && capture.country !== countryFilter) {
            matches = false;
        }
        
        if (browserFilter && capture.browser !== browserFilter) {
            matches = false;
        }
        
        if (osFilter && capture.os !== osFilter) {
            matches = false;
        }
        
        return matches;
    });
    
    currentPage = 1;
    displayCapturesTable();
    setupPagination();
}

/**
 * Configurar paginación
 */
function setupPagination() {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    const paginationContainer = document.getElementById('pagination');
    
    if (!paginationContainer || totalPages <= 1) {
        if (paginationContainer) paginationContainer.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Botón anterior
    paginationHTML += `
        <button class="btn btn-primary btn-sm ${currentPage === 1 ? 'disabled' : ''}" 
                onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
            ← Anterior
        </button>
    `;
    
    // Números de página
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <button class="btn ${i === currentPage ? 'btn-primary' : 'btn-secondary'} btn-sm" 
                    onclick="changePage(${i})">
                ${i}
            </button>
        `;
    }
    
    // Botón siguiente
    paginationHTML += `
        <button class="btn btn-primary btn-sm ${currentPage === totalPages ? 'disabled' : ''}" 
                onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
            Siguiente →
        </button>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
}

/**
 * Cambiar página
 */
function changePage(page) {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        displayCapturesTable();
        setupPagination();
    }
}

/**
 * Ver detalles de captura
 */
async function viewCaptureDetails(filename) {
    try {
        showLoadingModal();
        
        const response = await fetch(`dashboard.php?action=view&filename=${encodeURIComponent(filename)}`);
        const result = await response.json();
        
        if (result.success) {
            displayCaptureModal(result.data);
        } else {
            showAlert('Error cargando detalles: ' + result.message, 'danger');
            closeModal();
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error cargando detalles', 'danger');
        closeModal();
    }
}

/**
 * Mostrar modal con detalles de captura
 */
function displayCaptureModal(data) {
    const modal = document.getElementById('captureModal');
    const content = document.getElementById('captureDetails');
    
    if (!modal || !content) return;
    
    // Organizar datos por categorías
    const categories = {
        'Información Básica': {
            'IP': data.ip,
            'Timestamp': new Date(data.timestamp).toLocaleString('es-ES'),
            'User Agent': data.userAgent,
            'Referrer': data.referrer || 'Directo',
            'URL Actual': data.currentUrl
        },
        'Ubicación': {
            'País': data.country || 'Desconocido',
            'Región': data.regionName || 'Desconocida',
            'Ciudad': data.city || 'Desconocida',
            'Código Postal': data.zip || 'N/A',
            'Latitud': data.lat || 'N/A',
            'Longitud': data.lon || 'N/A',
            'Zona Horaria': data.timezone || 'N/A',
            'ISP': data.isp || 'Desconocido'
        },
        'Dispositivo y Navegador': {
            'Navegador': data.browser || 'Desconocido',
            'Sistema Operativo': data.os || 'Desconocido',
            'Dispositivo': data.device || 'Desconocido',
            'Plataforma': data.platform || 'Desconocida',
            'Idioma': data.language || 'N/A',
            'Idiomas': Array.isArray(data.languages) ? data.languages.join(', ') : 'N/A'
        },
        'Pantalla y Ventana': {
            'Resolución de Pantalla': `${data.screenWidth || 'N/A'} x ${data.screenHeight || 'N/A'}`,
            'Tamaño de Ventana': `${data.windowWidth || 'N/A'} x ${data.windowHeight || 'N/A'}`,
            'Profundidad de Color': data.screenColorDepth || 'N/A',
            'Profundidad de Píxel': data.screenPixelDepth || 'N/A'
        }
    };
    
    // Agregar información adicional si está disponible
    if (data.connectionType || data.hardwareConcurrency || data.deviceMemory) {
        categories['Hardware y Conexión'] = {};
        if (data.connectionType) categories['Hardware y Conexión']['Tipo de Conexión'] = data.connectionType;
        if (data.hardwareConcurrency) categories['Hardware y Conexión']['Núcleos de CPU'] = data.hardwareConcurrency;
        if (data.deviceMemory) categories['Hardware y Conexión']['Memoria del Dispositivo'] = data.deviceMemory + ' GB';
    }
    
    if (data.battery) {
        categories['Batería'] = {
            'Cargando': data.battery.charging ? 'Sí' : 'No',
            'Nivel': Math.round(data.battery.level * 100) + '%',
            'Tiempo de Carga': data.battery.chargingTime || 'N/A',
            'Tiempo de Descarga': data.battery.dischargingTime || 'N/A'
        };
    }
    
    if (data.plugins && data.plugins.length > 0) {
        categories['Plugins Instalados'] = {};
        data.plugins.forEach((plugin, index) => {
            categories['Plugins Instalados'][`Plugin ${index + 1}`] = plugin.name;
        });
    }
    
    // Generar HTML
    let html = '';
    Object.entries(categories).forEach(([categoryName, categoryData]) => {
        html += `
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">${categoryName}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
        `;
        
        Object.entries(categoryData).forEach(([key, value]) => {
            html += `
                <div class="col-md-6 mb-2">
                    <strong>${key}:</strong>
                    <span class="text-muted">${value}</span>
                </div>
            `;
        });
        
        html += `
                    </div>
                </div>
            </div>
        `;
    });
    
    content.innerHTML = html;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

/**
 * Eliminar archivo de captura
 */
async function deleteCaptureFile(filename) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta captura?')) {
        return;
    }
    
    try {
        const response = await fetch('dashboard.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ filename: filename })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Captura eliminada correctamente', 'success');
            loadRecentCaptures();
            loadStatistics();
        } else {
            showAlert('Error eliminando captura: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error eliminando captura', 'danger');
    }
}

/**
 * Configurar generador de enlaces
 */
function setupLinkGenerator() {
    const form = document.getElementById('linkForm');
    const templateSelect = document.getElementById('template');
    
    if (templateSelect) {
        templateSelect.addEventListener('change', function() {
            selectTemplate(this.value);
        });
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            generateLink();
        });
    }
}

/**
 * Seleccionar template de enlace
 */
function selectTemplate(templateName) {
    const templates = {
        'prize': {
            message: '¡Felicidades! Has ganado un premio',
            prize: 'iPhone 15 Pro GRATIS',
            redirect: 'https://www.apple.com/iphone-15-pro/'
        },
        'urgent': {
            message: '¡URGENTE! Acción requerida',
            prize: 'Verificación de cuenta necesaria',
            redirect: 'https://www.google.com/search?q=phishing+awareness'
        },
        'social': {
            message: 'Alguien mencionó tu nombre',
            prize: 'Ver quién te mencionó',
            redirect: 'https://www.facebook.com'
        },
        'work': {
            message: 'Documento importante compartido',
            prize: 'Acceder al documento',
            redirect: 'https://docs.google.com'
        }
    };
    
    const template = templates[templateName];
    if (template) {
        document.getElementById('customMessage').value = template.message;
        document.getElementById('customPrize').value = template.prize;
        document.getElementById('redirectUrl').value = template.redirect;
    }
}

/**
 * Generar nuevo enlace
 */
async function generateLink() {
    const formData = new FormData(document.getElementById('linkForm'));
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('link-generator.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Enlace generado correctamente', 'success');
            document.getElementById('linkForm').reset();
            loadExistingLinks();
        } else {
            showAlert('Error generando enlace: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error generando enlace', 'danger');
    }
}

/**
 * Cargar enlaces existentes
 */
async function loadExistingLinks() {
    try {
        const response = await fetch('link-generator.php?action=list');
        const result = await response.json();
        
        if (result.success) {
            displayLinksTable(result.data);
        }
    } catch (error) {
        console.error('Error cargando enlaces:', error);
    }
}

/**
 * Mostrar tabla de enlaces
 */
function displayLinksTable(links) {
    const tbody = document.querySelector('#linksTable tbody');
    if (!tbody) return;
    
    if (links.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <div class="p-4">
                        <div style="font-size: 3em; margin-bottom: 10px;">🔗</div>
                        <div>No hay enlaces creados</div>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = links.map(link => {
        const createdDate = new Date(link.created_at).toLocaleString('es-ES');
        const trackingUrl = `${window.location.origin}/advanced-ip-tracker/frontend/track.php?id=${link.id}`;
        
        return `
            <tr>
                <td><strong>${link.campaign_name}</strong></td>
                <td>${link.description || 'Sin descripción'}</td>
                <td><code style="font-size: 0.8em;">${trackingUrl}</code></td>
                <td>${createdDate}</td>
                <td>
                    <span class="badge badge-info">${link.clicks || 0} clics</span>
                    <span class="badge badge-success">${(link.unique_visitors || []).length} únicos</span>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm btn-copy-link" data-link="${trackingUrl}" title="Copiar enlace">
                        📋 Copiar
                    </button>
                    <button class="btn btn-info btn-sm" onclick="viewLinkStats('${link.id}')" title="Ver estadísticas">
                        📊 Stats
                    </button>
                    <button class="btn btn-danger btn-sm btn-delete-link" data-link-id="${link.id}" title="Eliminar">
                        🗑️ Eliminar
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Copiar al portapapeles
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showAlert('Enlace copiado al portapapeles', 'success');
    } catch (error) {
        // Fallback para navegadores que no soportan clipboard API
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showAlert('Enlace copiado al portapapeles', 'success');
    }
}

/**
 * Eliminar enlace
 */
async function deleteLinkFile(linkId) {
    if (!confirm('¿Estás seguro de que quieres eliminar este enlace?')) {
        return;
    }
    
    try {
        const response = await fetch('link-generator.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ linkId: linkId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Enlace eliminado correctamente', 'success');
            loadExistingLinks();
        } else {
            showAlert('Error eliminando enlace: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error eliminando enlace', 'danger');
    }
}

/**
 * Ver estadísticas de enlace
 */
function viewLinkStats(linkId) {
    window.open(`dashboard.php?link=${linkId}`, '_blank');
}

/**
 * Mostrar alerta
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} fade-in`;
    alertDiv.innerHTML = `
        <span>${message}</span>
        <button type="button" class="close" onclick="this.parentElement.remove()">
            <span>&times;</span>
        </button>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    // Auto-remove después de 5 segundos
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

/**
 * Crear contenedor de alertas
 */
function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    `;
    document.body.appendChild(container);
    return container;
}

/**
 * Mostrar loading en estadísticas
 */
function showLoadingStats() {
    const statCards = document.querySelectorAll('.stat-card .value');
    statCards.forEach(card => {
        card.innerHTML = '<div class="loading"></div>';
    });
}

/**
 * Mostrar loading en tabla
 */
function showLoadingTable() {
    const tbody = document.querySelector('#capturesTable tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <div class="p-4">
                        <div class="loading" style="margin: 0 auto 10px;"></div>
                        <div>Cargando datos...</div>
                    </div>
                </td>
            </tr>
        `;
    }
}

/**
 * Mostrar loading en modal
 */
function showLoadingModal() {
    const modal = document.getElementById('captureModal');
    const content = document.getElementById('captureDetails');
    
    if (modal && content) {
        content.innerHTML = `
            <div class="text-center p-4">
                <div class="loading" style="margin: 0 auto 20px;"></div>
                <div>Cargando detalles...</div>
            </div>
        `;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Cerrar modal
 */
function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
    document.body.style.overflow = 'auto';
}

/**
 * Formatear tamaño de archivo
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Actualizar gráficos (placeholder para Chart.js si se implementa)
 */
function updateCharts(stats) {
    // Aquí se pueden implementar gráficos con Chart.js
    console.log('Actualizando gráficos con:', stats);
}

// Exportar funciones para uso global
window.changePage = changePage;
window.viewLinkStats = viewLinkStats;
window.selectTemplate = selectTemplate;