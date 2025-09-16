/**
 * Device Profiling Module
 * Módulo para perfilado avanzado de dispositivos y características de hardware
 */

class DeviceProfiler {
    constructor() {
        this.data = {
            basicInfo: {},
            screenInfo: {},
            hardwareInfo: {},
            browserInfo: {},
            mediaDevices: {},
            sensors: {},
            capabilities: {},
            performance: {},
            webglInfo: {},
            audioContext: {},
            batteryInfo: {},
            memoryInfo: {},
            storageInfo: {},
            networkInfo: {},
            securityFeatures: {}
        };
        
        this.initialize();
    }
    
    async initialize() {
        this.collectBasicInfo();
        this.collectScreenInfo();
        this.collectBrowserInfo();
        await this.collectMediaDevices();
        await this.collectSensorInfo();
        this.collectCapabilities();
        this.collectPerformanceInfo();
        await this.collectWebGLInfo();
        await this.collectAudioInfo();
        await this.collectBatteryInfo();
        this.collectMemoryInfo();
        await this.collectStorageInfo();
        this.collectSecurityFeatures();
    }
    
    /**
     * Información básica del dispositivo
     */
    collectBasicInfo() {
        this.data.basicInfo = {
            userAgent: navigator.userAgent,
            platform: navigator.platform,
            language: navigator.language,
            languages: navigator.languages,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine,
            doNotTrack: navigator.doNotTrack,
            maxTouchPoints: navigator.maxTouchPoints || 0,
            hardwareConcurrency: navigator.hardwareConcurrency || 0,
            deviceMemory: navigator.deviceMemory || 0,
            timestamp: Date.now(),
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            timezoneOffset: new Date().getTimezoneOffset()
        };
    }
    
    /**
     * Información detallada de pantalla
     */
    collectScreenInfo() {
        this.data.screenInfo = {
            // Información básica de pantalla
            screenWidth: screen.width,
            screenHeight: screen.height,
            screenAvailWidth: screen.availWidth,
            screenAvailHeight: screen.availHeight,
            screenColorDepth: screen.colorDepth,
            screenPixelDepth: screen.pixelDepth,
            
            // Información de ventana
            windowWidth: window.innerWidth,
            windowHeight: window.innerHeight,
            windowOuterWidth: window.outerWidth,
            windowOuterHeight: window.outerHeight,
            
            // Información de viewport
            documentWidth: document.documentElement.clientWidth,
            documentHeight: document.documentElement.clientHeight,
            
            // Información de DPI y escalado
            devicePixelRatio: window.devicePixelRatio || 1,
            
            // Orientación
            orientation: screen.orientation ? {
                angle: screen.orientation.angle,
                type: screen.orientation.type
            } : null,
            
            // Información adicional
            screenLeft: window.screenLeft || window.screenX || 0,
            screenTop: window.screenTop || window.screenY || 0
        };
        
        // Detectar múltiples monitores
        this.data.screenInfo.multipleMonitors = this.detectMultipleMonitors();
        
        // Calcular métricas derivadas
        this.data.screenInfo.aspectRatio = this.data.screenInfo.screenWidth / this.data.screenInfo.screenHeight;
        this.data.screenInfo.screenArea = this.data.screenInfo.screenWidth * this.data.screenInfo.screenHeight;
        this.data.screenInfo.availableArea = this.data.screenInfo.screenAvailWidth * this.data.screenInfo.screenAvailHeight;
    }
    
    /**
     * Información detallada del navegador
     */
    collectBrowserInfo() {
        this.data.browserInfo = {
            name: this.getBrowserName(),
            version: this.getBrowserVersion(),
            engine: this.getBrowserEngine(),
            vendor: navigator.vendor,
            product: navigator.product,
            productSub: navigator.productSub,
            appName: navigator.appName,
            appVersion: navigator.appVersion,
            appCodeName: navigator.appCodeName,
            buildID: navigator.buildID || null,
            oscpu: navigator.oscpu || null,
            
            // Plugins
            plugins: this.getPluginsList(),
            mimeTypes: this.getMimeTypesList(),
            
            // Características del navegador
            javaEnabled: navigator.javaEnabled ? navigator.javaEnabled() : false,
            webdriver: navigator.webdriver || false,
            
            // Información de PDF
            pdfViewerEnabled: navigator.pdfViewerEnabled || false,
            
            // Información de almacenamiento
            storageQuota: this.getStorageQuota()
        };
    }
    
    /**
     * Información de dispositivos multimedia
     */
    async collectMediaDevices() {
        try {
            if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                const devices = await navigator.mediaDevices.enumerateDevices();
                
                this.data.mediaDevices = {
                    audioInputs: devices.filter(d => d.kind === 'audioinput').length,
                    audioOutputs: devices.filter(d => d.kind === 'audiooutput').length,
                    videoInputs: devices.filter(d => d.kind === 'videoinput').length,
                    devices: devices.map(device => ({
                        kind: device.kind,
                        label: device.label || 'Unknown',
                        deviceId: device.deviceId ? 'present' : 'absent',
                        groupId: device.groupId ? 'present' : 'absent'
                    }))
                };
                
                // Probar capacidades de medios
                this.data.mediaDevices.capabilities = await this.testMediaCapabilities();
            }
        } catch (error) {
            this.data.mediaDevices.error = error.message;
        }
    }
    
    /**
     * Información de sensores del dispositivo
     */
    async collectSensorInfo() {
        this.data.sensors = {
            accelerometer: await this.testSensor('Accelerometer'),
            gyroscope: await this.testSensor('Gyroscope'),
            magnetometer: await this.testSensor('Magnetometer'),
            ambientLight: await this.testSensor('AmbientLightSensor'),
            proximity: await this.testSensor('ProximitySensor'),
            
            // Eventos de orientación
            deviceOrientationSupported: 'DeviceOrientationEvent' in window,
            deviceMotionSupported: 'DeviceMotionEvent' in window,
            
            // Vibración
            vibrationSupported: 'vibrate' in navigator,
            
            // Geolocalización
            geolocationSupported: 'geolocation' in navigator
        };
    }
    
    /**
     * Capacidades del navegador
     */
    collectCapabilities() {
        this.data.capabilities = {
            // APIs Web
            webGL: 'WebGLRenderingContext' in window,
            webGL2: 'WebGL2RenderingContext' in window,
            webRTC: 'RTCPeerConnection' in window,
            webAssembly: 'WebAssembly' in window,
            serviceWorker: 'serviceWorker' in navigator,
            webWorker: 'Worker' in window,
            sharedWorker: 'SharedWorker' in window,
            
            // Almacenamiento
            localStorage: 'localStorage' in window,
            sessionStorage: 'sessionStorage' in window,
            indexedDB: 'indexedDB' in window,
            webSQL: 'openDatabase' in window,
            
            // Comunicación
            webSockets: 'WebSocket' in window,
            serverSentEvents: 'EventSource' in window,
            fetch: 'fetch' in window,
            
            // Multimedia
            webAudio: 'AudioContext' in window || 'webkitAudioContext' in window,
            webMIDI: 'requestMIDIAccess' in navigator,
            
            // Gráficos
            canvas: 'HTMLCanvasElement' in window,
            svg: 'SVGElement' in window,
            
            // Entrada
            pointerEvents: 'PointerEvent' in window,
            touchEvents: 'TouchEvent' in window,
            
            // Notificaciones
            notifications: 'Notification' in window,
            push: 'PushManager' in window,
            
            // Criptografía
            crypto: 'crypto' in window && 'subtle' in crypto,
            
            // Pagos
            paymentRequest: 'PaymentRequest' in window,
            
            // Realidad virtual/aumentada
            webXR: 'xr' in navigator,
            webVR: 'getVRDisplays' in navigator
        };
    }
    
    /**
     * Información de rendimiento
     */
    collectPerformanceInfo() {
        if ('performance' in window) {
            this.data.performance = {
                // Timing de navegación
                navigationTiming: this.getNavigationTiming(),
                
                // Información de memoria (si está disponible)
                memory: performance.memory ? {
                    usedJSHeapSize: performance.memory.usedJSHeapSize,
                    totalJSHeapSize: performance.memory.totalJSHeapSize,
                    jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
                } : null,
                
                // Timing de recursos
                resourceTiming: performance.getEntriesByType('resource').length,
                
                // Métricas de rendimiento
                timeOrigin: performance.timeOrigin,
                now: performance.now()
            };
        }
    }
    
    /**
     * Información de WebGL
     */
    async collectWebGLInfo() {
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            
            if (gl) {
                this.data.webglInfo = {
                    vendor: gl.getParameter(gl.VENDOR),
                    renderer: gl.getParameter(gl.RENDERER),
                    version: gl.getParameter(gl.VERSION),
                    shadingLanguageVersion: gl.getParameter(gl.SHADING_LANGUAGE_VERSION),
                    
                    // Extensiones
                    extensions: gl.getSupportedExtensions(),
                    
                    // Parámetros
                    maxTextureSize: gl.getParameter(gl.MAX_TEXTURE_SIZE),
                    maxViewportDims: gl.getParameter(gl.MAX_VIEWPORT_DIMS),
                    maxVertexAttribs: gl.getParameter(gl.MAX_VERTEX_ATTRIBS),
                    maxVaryingVectors: gl.getParameter(gl.MAX_VARYING_VECTORS),
                    maxFragmentUniforms: gl.getParameter(gl.MAX_FRAGMENT_UNIFORM_VECTORS),
                    maxVertexUniforms: gl.getParameter(gl.MAX_VERTEX_UNIFORM_VECTORS),
                    
                    // Información adicional
                    antialias: gl.getContextAttributes().antialias,
                    depth: gl.getContextAttributes().depth,
                    stencil: gl.getContextAttributes().stencil,
                    premultipliedAlpha: gl.getContextAttributes().premultipliedAlpha,
                    preserveDrawingBuffer: gl.getContextAttributes().preserveDrawingBuffer,
                    powerPreference: gl.getContextAttributes().powerPreference
                };
                
                // Información de depuración si está disponible
                const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                if (debugInfo) {
                    this.data.webglInfo.unmaskedVendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
                    this.data.webglInfo.unmaskedRenderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
                }
            }
        } catch (error) {
            this.data.webglInfo.error = error.message;
        }
    }
    
    /**
     * Información de audio
     */
    async collectAudioInfo() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                const audioContext = new AudioContext();
                
                this.data.audioContext = {
                    sampleRate: audioContext.sampleRate,
                    state: audioContext.state,
                    maxChannelCount: audioContext.destination.maxChannelCount,
                    numberOfInputs: audioContext.destination.numberOfInputs,
                    numberOfOutputs: audioContext.destination.numberOfOutputs,
                    channelCount: audioContext.destination.channelCount,
                    channelCountMode: audioContext.destination.channelCountMode,
                    channelInterpretation: audioContext.destination.channelInterpretation
                };
                
                // Cerrar el contexto para liberar recursos
                await audioContext.close();
            }
        } catch (error) {
            this.data.audioContext.error = error.message;
        }
    }
    
    /**
     * Información de batería
     */
    async collectBatteryInfo() {
        try {
            if ('getBattery' in navigator) {
                const battery = await navigator.getBattery();
                
                this.data.batteryInfo = {
                    charging: battery.charging,
                    chargingTime: battery.chargingTime,
                    dischargingTime: battery.dischargingTime,
                    level: battery.level
                };
            }
        } catch (error) {
            this.data.batteryInfo.error = error.message;
        }
    }
    
    /**
     * Información de memoria
     */
    collectMemoryInfo() {
        if ('memory' in performance) {
            this.data.memoryInfo = {
                usedJSHeapSize: performance.memory.usedJSHeapSize,
                totalJSHeapSize: performance.memory.totalJSHeapSize,
                jsHeapSizeLimit: performance.memory.jsHeapSizeLimit,
                
                // Cálculos derivados
                memoryUsagePercentage: (performance.memory.usedJSHeapSize / performance.memory.jsHeapSizeLimit) * 100,
                availableMemory: performance.memory.jsHeapSizeLimit - performance.memory.usedJSHeapSize
            };
        }
        
        // Información de dispositivo si está disponible
        if ('deviceMemory' in navigator) {
            this.data.memoryInfo.deviceMemory = navigator.deviceMemory;
        }
    }
    
    /**
     * Información de almacenamiento
     */
    async collectStorageInfo() {
        this.data.storageInfo = {};
        
        // Quota de almacenamiento
        if ('storage' in navigator && 'estimate' in navigator.storage) {
            try {
                const estimate = await navigator.storage.estimate();
                this.data.storageInfo.quota = estimate.quota;
                this.data.storageInfo.usage = estimate.usage;
                this.data.storageInfo.usagePercentage = (estimate.usage / estimate.quota) * 100;
            } catch (error) {
                this.data.storageInfo.quotaError = error.message;
            }
        }
        
        // Persistencia de almacenamiento
        if ('storage' in navigator && 'persist' in navigator.storage) {
            try {
                this.data.storageInfo.persistent = await navigator.storage.persisted();
            } catch (error) {
                this.data.storageInfo.persistError = error.message;
            }
        }
    }
    
    /**
     * Características de seguridad
     */
    collectSecurityFeatures() {
        this.data.securityFeatures = {
            // Contexto seguro
            isSecureContext: window.isSecureContext,
            
            // Origen
            origin: window.location.origin,
            protocol: window.location.protocol,
            
            // Headers de seguridad
            crossOriginIsolated: window.crossOriginIsolated || false,
            
            // APIs de seguridad
            credentials: 'credentials' in navigator,
            permissions: 'permissions' in navigator,
            
            // Información de TLS
            tlsVersion: this.getTLSVersion(),
            
            // Políticas de seguridad
            csp: this.getCSPInfo(),
            
            // Características del navegador relacionadas con seguridad
            privateMode: this.detectPrivateMode(),
            adBlocker: this.detectAdBlocker()
        };
    }
    
    // Métodos auxiliares
    
    detectMultipleMonitors() {
        return screen.width !== screen.availWidth || 
               screen.height !== screen.availHeight ||
               window.screenLeft !== 0 || 
               window.screenTop !== 0;
    }
    
    getBrowserName() {
        const userAgent = navigator.userAgent;
        if (userAgent.includes('Chrome')) return 'Chrome';
        if (userAgent.includes('Firefox')) return 'Firefox';
        if (userAgent.includes('Safari')) return 'Safari';
        if (userAgent.includes('Edge')) return 'Edge';
        if (userAgent.includes('Opera')) return 'Opera';
        return 'Unknown';
    }
    
    getBrowserVersion() {
        const userAgent = navigator.userAgent;
        const match = userAgent.match(/(Chrome|Firefox|Safari|Edge|Opera)\/(\d+)/);
        return match ? match[2] : 'Unknown';
    }
    
    getBrowserEngine() {
        const userAgent = navigator.userAgent;
        if (userAgent.includes('WebKit')) return 'WebKit';
        if (userAgent.includes('Gecko')) return 'Gecko';
        if (userAgent.includes('Trident')) return 'Trident';
        return 'Unknown';
    }
    
    getPluginsList() {
        const plugins = [];
        for (let i = 0; i < navigator.plugins.length; i++) {
            const plugin = navigator.plugins[i];
            plugins.push({
                name: plugin.name,
                description: plugin.description,
                filename: plugin.filename,
                version: plugin.version || 'Unknown'
            });
        }
        return plugins;
    }
    
    getMimeTypesList() {
        const mimeTypes = [];
        for (let i = 0; i < navigator.mimeTypes.length; i++) {
            const mimeType = navigator.mimeTypes[i];
            mimeTypes.push({
                type: mimeType.type,
                description: mimeType.description,
                suffixes: mimeType.suffixes
            });
        }
        return mimeTypes;
    }
    
    getStorageQuota() {
        try {
            return {
                localStorage: this.getLocalStorageSize(),
                sessionStorage: this.getSessionStorageSize()
            };
        } catch (error) {
            return { error: error.message };
        }
    }
    
    getLocalStorageSize() {
        let total = 0;
        for (let key in localStorage) {
            if (localStorage.hasOwnProperty(key)) {
                total += localStorage[key].length + key.length;
            }
        }
        return total;
    }
    
    getSessionStorageSize() {
        let total = 0;
        for (let key in sessionStorage) {
            if (sessionStorage.hasOwnProperty(key)) {
                total += sessionStorage[key].length + key.length;
            }
        }
        return total;
    }
    
    async testSensor(sensorName) {
        try {
            if (sensorName in window) {
                const sensor = new window[sensorName]();
                return { supported: true, permissions: 'unknown' };
            }
            return { supported: false };
        } catch (error) {
            return { supported: false, error: error.message };
        }
    }
    
    async testMediaCapabilities() {
        const capabilities = {};
        
        try {
            // Probar capacidades de video
            if ('mediaCapabilities' in navigator) {
                const videoConfig = {
                    type: 'media-source',
                    video: {
                        contentType: 'video/mp4; codecs="avc1.42E01E"',
                        width: 1920,
                        height: 1080,
                        bitrate: 2000000,
                        framerate: 30
                    }
                };
                
                const videoSupport = await navigator.mediaCapabilities.decodingInfo(videoConfig);
                capabilities.h264Support = videoSupport;
            }
        } catch (error) {
            capabilities.error = error.message;
        }
        
        return capabilities;
    }
    
    getNavigationTiming() {
        const navigation = performance.getEntriesByType('navigation')[0];
        if (!navigation) return null;
        
        return {
            domainLookupTime: navigation.domainLookupEnd - navigation.domainLookupStart,
            connectTime: navigation.connectEnd - navigation.connectStart,
            requestTime: navigation.responseStart - navigation.requestStart,
            responseTime: navigation.responseEnd - navigation.responseStart,
            domProcessingTime: navigation.domContentLoadedEventStart - navigation.responseEnd,
            loadCompleteTime: navigation.loadEventEnd - navigation.loadEventStart,
            totalTime: navigation.loadEventEnd - navigation.navigationStart
        };
    }
    
    getTLSVersion() {
        // Esto es una aproximación basada en características del navegador
        if (window.crypto && window.crypto.subtle) {
            return 'TLS 1.2+';
        }
        return 'Unknown';
    }
    
    getCSPInfo() {
        // Intentar detectar CSP a través de violaciones
        const cspInfo = {
            present: false,
            violations: []
        };
        
        document.addEventListener('securitypolicyviolation', (e) => {
            cspInfo.present = true;
            cspInfo.violations.push({
                directive: e.violatedDirective,
                blockedURI: e.blockedURI,
                originalPolicy: e.originalPolicy
            });
        });
        
        return cspInfo;
    }
    
    detectPrivateMode() {
        // Método aproximado para detectar modo privado
        try {
            localStorage.setItem('test', 'test');
            localStorage.removeItem('test');
            return false;
        } catch (error) {
            return true;
        }
    }
    
    detectAdBlocker() {
        // Método básico para detectar bloqueadores de anuncios
        const testAd = document.createElement('div');
        testAd.innerHTML = '&nbsp;';
        testAd.className = 'adsbox';
        testAd.style.position = 'absolute';
        testAd.style.left = '-10000px';
        
        document.body.appendChild(testAd);
        
        const isBlocked = testAd.offsetHeight === 0;
        document.body.removeChild(testAd);
        
        return isBlocked;
    }
    
    /**
     * Generar huella digital única del dispositivo
     */
    generateDeviceFingerprint() {
        const components = [
            this.data.basicInfo.userAgent,
            this.data.screenInfo.screenWidth + 'x' + this.data.screenInfo.screenHeight,
            this.data.screenInfo.colorDepth,
            this.data.screenInfo.devicePixelRatio,
            this.data.basicInfo.timezone,
            this.data.basicInfo.language,
            this.data.basicInfo.hardwareConcurrency,
            this.data.webglInfo.renderer || '',
            this.data.webglInfo.vendor || '',
            JSON.stringify(this.data.capabilities),
            this.data.audioContext.sampleRate || '',
            this.data.mediaDevices.audioInputs || 0,
            this.data.mediaDevices.videoInputs || 0
        ];
        
        return this.hashComponents(components);
    }
    
    hashComponents(components) {
        const str = components.join('|');
        let hash = 0;
        
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        
        return Math.abs(hash).toString(16);
    }
    
    /**
     * Obtener todos los datos del perfil del dispositivo
     */
    getAllData() {
        return {
            ...this.data,
            deviceFingerprint: this.generateDeviceFingerprint(),
            profileTimestamp: Date.now(),
            profileVersion: '1.0'
        };
    }
}

// Exportar para uso global
window.DeviceProfiler = DeviceProfiler;