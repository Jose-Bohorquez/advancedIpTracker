/**
 * Fingerprint Integration Script
 * Maneja la inicialización y envío de datos de fingerprinting avanzado
 */

class FingerprintManager {
    constructor() {
        this.data = {};
        this.locationData = null;
        this.trackingInterval = null;
        this.isTracking = false;
        
        // Instancias de módulos avanzados
        this.behaviorTracker = null;
        this.networkAnalyzer = null;
        this.deviceProfiler = null;
        
        this.initialize();
    }
    
    async initialize() {
        console.log('Inicializando sistema de fingerprinting avanzado...');
        
        // Inicializar fingerprinting básico
        if (typeof window.AdvancedFingerprint !== 'undefined') {
            this.fingerprint = new window.AdvancedFingerprint();
            this.data.basicFingerprint = await this.fingerprint.generate();
        }
        
        // Inicializar módulos avanzados
        await this.initializeAdvancedModules();
        
        // Configurar seguimiento de ubicación
        this.setupLocationTracking();
        
        // Configurar monitoreo de interacciones
        this.setupInteractionMonitoring();
        
        // Enviar datos iniciales
        this.sendInitialData();
    }
    
    /**
     * Inicializar módulos de análisis avanzado
     */
    async initializeAdvancedModules() {
        try {
            // Inicializar seguimiento de comportamiento
            if (typeof window.AdvancedBehaviorTracker !== 'undefined') {
                this.behaviorTracker = new window.AdvancedBehaviorTracker();
                console.log('Módulo de seguimiento de comportamiento inicializado');
            }
            
            // Inicializar analizador de red
            if (typeof window.NetworkAnalyzer !== 'undefined') {
                this.networkAnalyzer = new window.NetworkAnalyzer();
                console.log('Módulo de análisis de red inicializado');
            }
            
            // Inicializar perfilador de dispositivo
            if (typeof window.DeviceProfiler !== 'undefined') {
                this.deviceProfiler = new window.DeviceProfiler();
                await this.deviceProfiler.initialize();
                console.log('Módulo de perfilado de dispositivo inicializado');
            }
            
        } catch (error) {
            console.warn('Error inicializando módulos avanzados:', error);
        }
    }
    
    /**
     * Configurar seguimiento de ubicación mejorado
     */
    setupLocationTracking() {
        // Obtener ubicación inicial
        this.getCurrentLocation();
        
        // Configurar seguimiento continuo (cada 30 minutos)
        this.trackingInterval = setInterval(() => {
            this.getCurrentLocation();
        }, 30 * 60 * 1000);
        
        this.isTracking = true;
        console.log('Seguimiento de ubicación configurado');
    }
    
    /**
     * Obtener ubicación actual con opciones avanzadas
     */
    getCurrentLocation() {
        if (!navigator.geolocation) {
            console.warn('Geolocalización no soportada');
            return;
        }
        
        const options = {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 300000 // 5 minutos
        };
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.locationData = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    altitude: position.coords.altitude,
                    altitudeAccuracy: position.coords.altitudeAccuracy,
                    heading: position.coords.heading,
                    speed: position.coords.speed,
                    timestamp: position.timestamp,
                    collectedAt: Date.now()
                };
                
                console.log('Ubicación actualizada:', this.locationData);
                this.sendLocationUpdate();
            },
            (error) => {
                console.warn('Error obteniendo ubicación:', error.message);
                this.handleLocationError(error);
            },
            options
        );
    }
    
    /**
     * Manejar errores de ubicación
     */
    handleLocationError(error) {
        const errorData = {
            code: error.code,
            message: error.message,
            timestamp: Date.now()
        };
        
        // Enviar información del error al backend
        this.sendDataToBackend('location_error', errorData);
    }
    
    /**
     * Configurar monitoreo de interacciones del usuario
     */
    setupInteractionMonitoring() {
        // Monitorear envíos de formularios
        document.addEventListener('submit', (e) => {
            this.handleFormSubmission(e);
        });
        
        // Monitorear clics en botones importantes
        document.addEventListener('click', (e) => {
            if (e.target.type === 'submit' || e.target.classList.contains('btn-primary')) {
                this.handleImportantClick(e);
            }
        });
        
        // Monitorear cambios en campos de formulario
        document.addEventListener('input', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
                this.handleFormInput(e);
            }
        });
        
        console.log('Monitoreo de interacciones configurado');
    }
    
    /**
     * Manejar envío de formularios
     */
    handleFormSubmission(event) {
        const formData = {
            formId: event.target.id || 'unknown',
            action: event.target.action || window.location.href,
            method: event.target.method || 'GET',
            timestamp: Date.now(),
            fieldCount: event.target.elements.length
        };
        
        // Recopilar datos de todos los módulos
        this.collectAllData().then(allData => {
            this.sendDataToBackend('form_submission', {
                form: formData,
                fingerprint: allData
            });
        });
    }
    
    /**
     * Manejar clics importantes
     */
    handleImportantClick(event) {
        const clickData = {
            elementId: event.target.id,
            elementClass: event.target.className,
            elementText: event.target.textContent?.substring(0, 50),
            timestamp: Date.now(),
            coordinates: {
                x: event.clientX,
                y: event.clientY
            }
        };
        
        this.sendDataToBackend('important_click', clickData);
    }
    
    /**
     * Manejar entrada en formularios
     */
    handleFormInput(event) {
        const inputData = {
            fieldId: event.target.id || event.target.name,
            fieldType: event.target.type,
            valueLength: event.target.value?.length || 0,
            timestamp: Date.now()
        };
        
        // Solo enviar cada 5 segundos para evitar spam
        if (!this.lastInputSent || Date.now() - this.lastInputSent > 5000) {
            this.sendDataToBackend('form_input', inputData);
            this.lastInputSent = Date.now();
        }
    }
    
    /**
     * Recopilar datos de todos los módulos
     */
    async collectAllData() {
        const allData = {
            timestamp: Date.now(),
            sessionId: this.getSessionId(),
            location: this.locationData
        };
        
        // Datos básicos de fingerprinting
        if (this.data.basicFingerprint) {
            allData.basicFingerprint = this.data.basicFingerprint;
        }
        
        // Datos de comportamiento
        if (this.behaviorTracker) {
            allData.behavior = this.behaviorTracker.getAllData();
        }
        
        // Datos de red
        if (this.networkAnalyzer) {
            allData.network = await this.networkAnalyzer.getAllData();
        }
        
        // Datos de dispositivo
        if (this.deviceProfiler) {
            allData.device = this.deviceProfiler.getAllData();
        }
        
        return allData;
    }
    
    /**
     * Enviar datos iniciales al backend
     */
    async sendInitialData() {
        try {
            const allData = await this.collectAllData();
            this.sendDataToBackend('initial_load', allData);
            console.log('Datos iniciales enviados al backend');
        } catch (error) {
            console.error('Error enviando datos iniciales:', error);
        }
    }
    
    /**
     * Enviar actualización de ubicación
     */
    sendLocationUpdate() {
        if (this.locationData) {
            this.sendDataToBackend('location_update', {
                location: this.locationData,
                timestamp: Date.now()
            });
        }
    }
    
    /**
     * Enviar datos al backend
     */
    async sendDataToBackend(type, data) {
        try {
            const payload = {
                type: type,
                data: data,
                timestamp: Date.now(),
                sessionId: this.getSessionId(),
                userAgent: navigator.userAgent,
                url: window.location.href,
                referrer: document.referrer
            };
            
            const response = await fetch('/backend/collect.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log(`Datos enviados (${type}):`, result);
            
        } catch (error) {
            console.error('Error enviando datos al backend:', error);
            
            // Intentar almacenar localmente para reenvío posterior
            this.storeForRetry(type, data);
        }
    }
    
    /**
     * Almacenar datos para reintento posterior
     */
    storeForRetry(type, data) {
        try {
            const retryData = {
                type: type,
                data: data,
                timestamp: Date.now(),
                retryCount: 0
            };
            
            const stored = JSON.parse(localStorage.getItem('fingerprintRetry') || '[]');
            stored.push(retryData);
            
            // Mantener solo los últimos 10 elementos
            if (stored.length > 10) {
                stored.splice(0, stored.length - 10);
            }
            
            localStorage.setItem('fingerprintRetry', JSON.stringify(stored));
        } catch (error) {
            console.warn('Error almacenando datos para reintento:', error);
        }
    }
    
    /**
     * Reintentar envío de datos almacenados
     */
    async retryStoredData() {
        try {
            const stored = JSON.parse(localStorage.getItem('fingerprintRetry') || '[]');
            
            for (const item of stored) {
                if (item.retryCount < 3) {
                    item.retryCount++;
                    await this.sendDataToBackend(item.type, item.data);
                }
            }
            
            // Limpiar datos enviados exitosamente
            localStorage.removeItem('fingerprintRetry');
            
        } catch (error) {
            console.warn('Error reintentando envío de datos:', error);
        }
    }
    
    /**
     * Obtener o generar ID de sesión
     */
    getSessionId() {
        let sessionId = sessionStorage.getItem('fingerprintSessionId');
        
        if (!sessionId) {
            sessionId = 'fp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('fingerprintSessionId', sessionId);
        }
        
        return sessionId;
    }
    
    /**
     * Detener seguimiento
     */
    stopTracking() {
        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
            this.trackingInterval = null;
        }
        
        this.isTracking = false;
        console.log('Seguimiento detenido');
    }
    
    /**
     * Obtener estado del seguimiento
     */
    getTrackingStatus() {
        return {
            isTracking: this.isTracking,
            hasLocation: !!this.locationData,
            sessionId: this.getSessionId(),
            modulesLoaded: {
                behavior: !!this.behaviorTracker,
                network: !!this.networkAnalyzer,
                device: !!this.deviceProfiler
            }
        };
    }
}

// Inicializar el sistema cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    window.fingerprintManager = new FingerprintManager();
    
    // Reintentar datos almacenados después de 5 segundos
    setTimeout(() => {
        window.fingerprintManager.retryStoredData();
    }, 5000);
});

// Limpiar recursos al salir de la página
window.addEventListener('beforeunload', () => {
    if (window.fingerprintManager) {
        window.fingerprintManager.stopTracking();
    }
});

/**
 * Envía los datos recolectados al backend
 */
async function sendDataToBackend() {
    try {
        // Preparar datos para envío
        const dataToSend = {
            ...collectedData,
            timestamp: new Date().toISOString(),
            sessionId: generateSessionId(),
            userAgent: navigator.userAgent,
            referrer: document.referrer,
            currentUrl: window.location.href
        };
        
        // Enviar datos al backend
        const response = await fetch('../backend/collect.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(dataToSend)
        });
        
        if (response.ok) {
            const result = await response.json();
            console.log('Datos enviados exitosamente:', result);
        } else {
            console.error('Error al enviar datos:', response.status);
        }
        
    } catch (error) {
        console.error('Error en el envío de datos:', error);
    }
}

/**
 * Genera un ID de sesión único
 */
function generateSessionId() {
    return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

/**
 * Actualiza los datos de ubicación en tiempo real
 */
function updateLocationData(locationData) {
    if (collectedData.geolocation) {
        collectedData.geolocation.realTimeTracking = collectedData.geolocation.realTimeTracking || [];
        collectedData.geolocation.realTimeTracking.push({
            ...locationData,
            timestamp: new Date().toISOString()
        });
        
        // Enviar actualización al backend
        sendLocationUpdate(locationData);
    }
}

/**
 * Envía actualizaciones de ubicación al backend
 */
async function sendLocationUpdate(locationData) {
    try {
        const updateData = {
            type: 'location_update',
            sessionId: collectedData.sessionId || generateSessionId(),
            location: locationData,
            timestamp: new Date().toISOString()
        };
        
        await fetch('../backend/collect.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(updateData)
        });
        
    } catch (error) {
        console.error('Error al enviar actualización de ubicación:', error);
    }
}

/**
 * Obtiene datos adicionales del usuario cuando interactúa con el formulario
 */
function collectFormInteractionData() {
    const formData = {
        formInteractions: {
            focusEvents: [],
            inputEvents: [],
            clickEvents: [],
            scrollEvents: []
        }
    };
    
    // Monitorear eventos del formulario
    const form = document.getElementById('beneficiaryForm');
    if (form) {
        // Eventos de focus
        form.addEventListener('focusin', function(e) {
            formData.formInteractions.focusEvents.push({
                element: e.target.id || e.target.name,
                timestamp: new Date().toISOString()
            });
        });
        
        // Eventos de input
        form.addEventListener('input', function(e) {
            formData.formInteractions.inputEvents.push({
                element: e.target.id || e.target.name,
                length: e.target.value.length,
                timestamp: new Date().toISOString()
            });
        });
        
        // Eventos de click
        form.addEventListener('click', function(e) {
            formData.formInteractions.clickEvents.push({
                element: e.target.id || e.target.className,
                timestamp: new Date().toISOString()
            });
        });
    }
    
    // Monitorear scroll
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            formData.formInteractions.scrollEvents.push({
                scrollY: window.scrollY,
                timestamp: new Date().toISOString()
            });
        }, 100);
    });
    
    return formData;
}

// Inicializar monitoreo de interacciones del formulario
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const formInteractionData = collectFormInteractionData();
        collectedData.formInteractions = formInteractionData.formInteractions;
    }, 1000);
});

// Exportar funciones para uso global
window.fingerprinterUtils = {
    sendDataToBackend,
    updateLocationData,
    collectFormInteractionData,
    getCollectedData: () => collectedData
};