/**
 * Advanced Fingerprinting Library
 * Funciones avanzadas para recolección de datos del dispositivo y navegador
 * SOLO PARA FINES EDUCATIVOS
 */

class AdvancedFingerprint {
    constructor() {
        this.data = {};
        this.initialized = false;
    }

    /**
     * Inicializar y recolectar todos los datos disponibles
     */
    async initialize() {
        if (this.initialized) return this.data;
        
        try {
            // Recolectar datos básicos
            await this.collectBasicInfo();
            
            // Recolectar datos de hardware
            await this.collectHardwareInfo();
            
            // Recolectar datos de red
            await this.collectNetworkInfo();
            
            // Recolectar fingerprints
            await this.collectFingerprints();
            
            // Recolectar datos de sensores
            await this.collectSensorData();
            
            // Recolectar datos de almacenamiento
            await this.collectStorageInfo();
            
            // Recolectar datos de medios
            await this.collectMediaInfo();
            
            // Recolectar datos de APIs del navegador
            await this.collectBrowserAPIs();
            
            // Recolectar datos de rendimiento
            await this.collectPerformanceInfo();
            
            this.initialized = true;
            return this.data;
            
        } catch (error) {
            console.error('Error en fingerprinting:', error);
            return this.data;
        }
    }

    /**
     * Recolectar información básica del navegador
     */
    async collectBasicInfo() {
        this.data.basic = {
            userAgent: navigator.userAgent,
            platform: navigator.platform,
            language: navigator.language,
            languages: navigator.languages || [],
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine,
            doNotTrack: navigator.doNotTrack,
            maxTouchPoints: navigator.maxTouchPoints || 0,
            vendor: navigator.vendor,
            vendorSub: navigator.vendorSub,
            productSub: navigator.productSub,
            appName: navigator.appName,
            appVersion: navigator.appVersion,
            appCodeName: navigator.appCodeName,
            oscpu: navigator.oscpu,
            buildID: navigator.buildID
        };
    }

    /**
     * Recolectar información de hardware
     */
    async collectHardwareInfo() {
        this.data.hardware = {
            hardwareConcurrency: navigator.hardwareConcurrency,
            deviceMemory: navigator.deviceMemory,
            screen: {
                width: screen.width,
                height: screen.height,
                availWidth: screen.availWidth,
                availHeight: screen.availHeight,
                colorDepth: screen.colorDepth,
                pixelDepth: screen.pixelDepth,
                orientation: screen.orientation ? {
                    angle: screen.orientation.angle,
                    type: screen.orientation.type
                } : null
            },
            window: {
                innerWidth: window.innerWidth,
                innerHeight: window.innerHeight,
                outerWidth: window.outerWidth,
                outerHeight: window.outerHeight,
                devicePixelRatio: window.devicePixelRatio,
                screenX: window.screenX,
                screenY: window.screenY
            }
        };

        // Información de batería
        if (navigator.getBattery) {
            try {
                const battery = await navigator.getBattery();
                this.data.hardware.battery = {
                    charging: battery.charging,
                    chargingTime: battery.chargingTime,
                    dischargingTime: battery.dischargingTime,
                    level: battery.level
                };
            } catch (e) {
                this.data.hardware.battery = 'unavailable';
            }
        }
    }

    /**
     * Recolectar información de red
     */
    async collectNetworkInfo() {
        this.data.network = {};

        // Información de conexión
        if (navigator.connection || navigator.mozConnection || navigator.webkitConnection) {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            this.data.network.connection = {
                effectiveType: connection.effectiveType,
                downlink: connection.downlink,
                downlinkMax: connection.downlinkMax,
                rtt: connection.rtt,
                saveData: connection.saveData,
                type: connection.type
            };
        }

        // Detectar WebRTC y obtener IPs locales
        try {
            this.data.network.localIPs = await this.getLocalIPs();
        } catch (e) {
            this.data.network.localIPs = 'unavailable';
        }

        // Información de zona horaria
        this.data.network.timezone = {
            name: Intl.DateTimeFormat().resolvedOptions().timeZone,
            offset: new Date().getTimezoneOffset(),
            locale: Intl.DateTimeFormat().resolvedOptions().locale
        };
    }

    /**
     * Obtener IPs locales usando WebRTC
     */
    getLocalIPs() {
        return new Promise((resolve) => {
            const ips = [];
            const RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
            
            if (!RTCPeerConnection) {
                resolve(['WebRTC not supported']);
                return;
            }

            const pc = new RTCPeerConnection({
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
            });

            pc.createDataChannel('');
            
            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    const candidate = event.candidate.candidate;
                    const ipMatch = candidate.match(/([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/);
                    if (ipMatch && !ips.includes(ipMatch[1])) {
                        ips.push(ipMatch[1]);
                    }
                }
            };

            pc.createOffer().then(offer => pc.setLocalDescription(offer));
            
            setTimeout(() => {
                pc.close();
                resolve(ips.length > 0 ? ips : ['No local IPs detected']);
            }, 2000);
        });
    }

    /**
     * Recolectar fingerprints únicos
     */
    async collectFingerprints() {
        this.data.fingerprints = {
            canvas: this.getCanvasFingerprint(),
            webgl: this.getWebGLFingerprint(),
            audio: await this.getAudioFingerprint(),
            fonts: await this.getFontFingerprint(),
            css: this.getCSSFingerprint(),
            svg: this.getSVGFingerprint()
        };
    }

    /**
     * Canvas fingerprinting avanzado
     */
    getCanvasFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Configurar canvas
            canvas.width = 200;
            canvas.height = 50;
            
            // Dibujar texto con diferentes fuentes y estilos
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillStyle = '#f60';
            ctx.fillRect(125, 1, 62, 20);
            
            ctx.fillStyle = '#069';
            ctx.fillText('Canvas fingerprint 🔒', 2, 15);
            
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.font = '18px Arial';
            ctx.fillText('Advanced test', 4, 25);
            
            // Agregar formas geométricas
            ctx.globalCompositeOperation = 'multiply';
            ctx.fillStyle = 'rgb(255,0,255)';
            ctx.beginPath();
            ctx.arc(50, 25, 20, 0, Math.PI * 2, true);
            ctx.closePath();
            ctx.fill();
            
            return {
                dataURL: canvas.toDataURL(),
                hash: this.hashCode(canvas.toDataURL())
            };
        } catch (e) {
            return { error: e.message };
        }
    }

    /**
     * WebGL fingerprinting avanzado
     */
    getWebGLFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            
            if (!gl) return { error: 'WebGL not supported' };

            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            const extensions = gl.getSupportedExtensions();
            
            return {
                vendor: gl.getParameter(gl.VENDOR),
                renderer: gl.getParameter(gl.RENDERER),
                version: gl.getParameter(gl.VERSION),
                shadingLanguageVersion: gl.getParameter(gl.SHADING_LANGUAGE_VERSION),
                unmaskedVendor: debugInfo ? gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) : null,
                unmaskedRenderer: debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : null,
                extensions: extensions,
                maxTextureSize: gl.getParameter(gl.MAX_TEXTURE_SIZE),
                maxViewportDims: gl.getParameter(gl.MAX_VIEWPORT_DIMS),
                maxVertexAttribs: gl.getParameter(gl.MAX_VERTEX_ATTRIBS),
                aliasedLineWidthRange: gl.getParameter(gl.ALIASED_LINE_WIDTH_RANGE),
                aliasedPointSizeRange: gl.getParameter(gl.ALIASED_POINT_SIZE_RANGE)
            };
        } catch (e) {
            return { error: e.message };
        }
    }

    /**
     * Audio fingerprinting
     */
    getAudioFingerprint() {
        return new Promise((resolve) => {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const analyser = audioContext.createAnalyser();
                const gainNode = audioContext.createGain();
                const scriptProcessor = audioContext.createScriptProcessor(4096, 1, 1);
                
                oscillator.type = 'triangle';
                oscillator.frequency.setValueAtTime(10000, audioContext.currentTime);
                
                gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                
                oscillator.connect(analyser);
                analyser.connect(scriptProcessor);
                scriptProcessor.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                let fingerprint = '';
                
                scriptProcessor.onaudioprocess = function(bins) {
                    const array = new Float32Array(analyser.frequencyBinCount);
                    analyser.getFloatFrequencyData(array);
                    
                    fingerprint = Array.from(array.slice(0, 30)).join(',');
                    
                    oscillator.disconnect();
                    scriptProcessor.disconnect();
                    audioContext.close();
                    
                    resolve({
                        fingerprint: fingerprint,
                        hash: this.hashCode(fingerprint),
                        sampleRate: audioContext.sampleRate,
                        maxChannelCount: audioContext.destination.maxChannelCount
                    });
                };
                
                oscillator.start(0);
                
                setTimeout(() => {
                    resolve({ error: 'Audio fingerprinting timeout' });
                }, 1000);
                
            } catch (e) {
                resolve({ error: e.message });
            }
        });
    }

    /**
     * Detección de fuentes instaladas
     */
    getFontFingerprint() {
        return new Promise((resolve) => {
            const baseFonts = ['monospace', 'sans-serif', 'serif'];
            const testFonts = [
                'Arial', 'Arial Black', 'Arial Narrow', 'Arial Rounded MT Bold',
                'Calibri', 'Cambria', 'Cambria Math', 'Candara', 'Comic Sans MS',
                'Consolas', 'Constantia', 'Corbel', 'Courier New', 'Franklin Gothic Medium',
                'Garamond', 'Georgia', 'Helvetica', 'Impact', 'Lucida Console',
                'Lucida Sans Unicode', 'Microsoft Sans Serif', 'MS Gothic', 'MS PGothic',
                'MS Sans Serif', 'MS Serif', 'Palatino Linotype', 'Segoe Print',
                'Segoe Script', 'Segoe UI', 'Segoe UI Light', 'Segoe UI Semibold',
                'Segoe UI Symbol', 'Tahoma', 'Times', 'Times New Roman', 'Trebuchet MS',
                'Verdana', 'Wingdings', 'Wingdings 2', 'Wingdings 3'
            ];
            
            const detectedFonts = [];
            const testString = 'mmmmmmmmmmlli';
            const testSize = '72px';
            
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            
            // Medir dimensiones con fuentes base
            const baseDimensions = {};
            baseFonts.forEach(baseFont => {
                context.font = testSize + ' ' + baseFont;
                baseDimensions[baseFont] = {
                    width: context.measureText(testString).width,
                    height: parseInt(testSize)
                };
            });
            
            // Probar cada fuente
            testFonts.forEach(testFont => {
                let detected = false;
                baseFonts.forEach(baseFont => {
                    context.font = testSize + ' ' + testFont + ', ' + baseFont;
                    const dimensions = {
                        width: context.measureText(testString).width,
                        height: parseInt(testSize)
                    };
                    
                    if (dimensions.width !== baseDimensions[baseFont].width) {
                        detected = true;
                    }
                });
                
                if (detected) {
                    detectedFonts.push(testFont);
                }
            });
            
            resolve({
                fonts: detectedFonts,
                count: detectedFonts.length,
                hash: this.hashCode(detectedFonts.join(','))
            });
        });
    }

    /**
     * CSS fingerprinting
     */
    getCSSFingerprint() {
        const features = {
            supportsCSS: typeof CSS !== 'undefined',
            supportsGrid: CSS && CSS.supports && CSS.supports('display', 'grid'),
            supportsFlex: CSS && CSS.supports && CSS.supports('display', 'flex'),
            supportsCustomProperties: CSS && CSS.supports && CSS.supports('--custom', 'value'),
            supportsFilter: CSS && CSS.supports && CSS.supports('filter', 'blur(1px)'),
            supportsBackdropFilter: CSS && CSS.supports && CSS.supports('backdrop-filter', 'blur(1px)'),
            supportsClipPath: CSS && CSS.supports && CSS.supports('clip-path', 'circle(50%)'),
            supportsMask: CSS && CSS.supports && CSS.supports('mask', 'url(#mask)')
        };
        
        return features;
    }

    /**
     * SVG fingerprinting
     */
    getSVGFingerprint() {
        try {
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('width', '100');
            svg.setAttribute('height', '100');
            
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', '10');
            text.setAttribute('y', '30');
            text.setAttribute('font-family', 'Arial');
            text.setAttribute('font-size', '16');
            text.textContent = 'SVG Test';
            
            svg.appendChild(text);
            document.body.appendChild(svg);
            
            const bbox = text.getBBox();
            const result = {
                width: bbox.width,
                height: bbox.height,
                x: bbox.x,
                y: bbox.y
            };
            
            document.body.removeChild(svg);
            return result;
        } catch (e) {
            return { error: e.message };
        }
    }

    /**
     * Recolectar datos de sensores
     */
    async collectSensorData() {
        this.data.sensors = {};

        // Acelerómetro y giroscopio
        if (window.DeviceMotionEvent) {
            this.data.sensors.deviceMotion = await this.getDeviceMotion();
        }

        // Orientación del dispositivo
        if (window.DeviceOrientationEvent) {
            this.data.sensors.deviceOrientation = await this.getDeviceOrientation();
        }

        // Luz ambiente
        if (navigator.permissions) {
            try {
                const permission = await navigator.permissions.query({ name: 'ambient-light-sensor' });
                this.data.sensors.ambientLightPermission = permission.state;
            } catch (e) {
                this.data.sensors.ambientLightPermission = 'unavailable';
            }
        }
    }

    /**
     * Obtener datos de movimiento del dispositivo
     */
    getDeviceMotion() {
        return new Promise((resolve) => {
            let motionData = null;
            
            const handleMotion = (event) => {
                motionData = {
                    acceleration: event.acceleration,
                    accelerationIncludingGravity: event.accelerationIncludingGravity,
                    rotationRate: event.rotationRate,
                    interval: event.interval
                };
            };
            
            window.addEventListener('devicemotion', handleMotion);
            
            setTimeout(() => {
                window.removeEventListener('devicemotion', handleMotion);
                resolve(motionData || 'no_motion_detected');
            }, 1000);
        });
    }

    /**
     * Obtener datos de orientación del dispositivo
     */
    getDeviceOrientation() {
        return new Promise((resolve) => {
            let orientationData = null;
            
            const handleOrientation = (event) => {
                orientationData = {
                    alpha: event.alpha,
                    beta: event.beta,
                    gamma: event.gamma,
                    absolute: event.absolute
                };
            };
            
            window.addEventListener('deviceorientation', handleOrientation);
            
            setTimeout(() => {
                window.removeEventListener('deviceorientation', handleOrientation);
                resolve(orientationData || 'no_orientation_detected');
            }, 1000);
        });
    }

    /**
     * Recolectar información de almacenamiento
     */
    async collectStorageInfo() {
        this.data.storage = {
            localStorage: this.testStorage('localStorage'),
            sessionStorage: this.testStorage('sessionStorage'),
            indexedDB: await this.testIndexedDB(),
            webSQL: this.testWebSQL(),
            cookies: this.getCookieInfo()
        };

        // Estimar cuota de almacenamiento
        if (navigator.storage && navigator.storage.estimate) {
            try {
                this.data.storage.quota = await navigator.storage.estimate();
            } catch (e) {
                this.data.storage.quota = 'unavailable';
            }
        }
    }

    /**
     * Probar disponibilidad de almacenamiento
     */
    testStorage(type) {
        try {
            const storage = window[type];
            const testKey = '__test__';
            storage.setItem(testKey, 'test');
            storage.removeItem(testKey);
            return {
                available: true,
                length: storage.length
            };
        } catch (e) {
            return {
                available: false,
                error: e.message
            };
        }
    }

    /**
     * Probar IndexedDB
     */
    testIndexedDB() {
        return new Promise((resolve) => {
            if (!window.indexedDB) {
                resolve({ available: false, reason: 'not_supported' });
                return;
            }

            try {
                const request = indexedDB.open('__test__', 1);
                request.onsuccess = () => {
                    request.result.close();
                    indexedDB.deleteDatabase('__test__');
                    resolve({ available: true });
                };
                request.onerror = () => {
                    resolve({ available: false, error: request.error });
                };
            } catch (e) {
                resolve({ available: false, error: e.message });
            }
        });
    }

    /**
     * Probar WebSQL
     */
    testWebSQL() {
        try {
            return {
                available: !!window.openDatabase,
                version: window.openDatabase ? 'supported' : 'not_supported'
            };
        } catch (e) {
            return {
                available: false,
                error: e.message
            };
        }
    }

    /**
     * Obtener información de cookies
     */
    getCookieInfo() {
        return {
            enabled: navigator.cookieEnabled,
            count: document.cookie.split(';').filter(c => c.trim()).length,
            content: document.cookie
        };
    }

    /**
     * Recolectar información de medios
     */
    async collectMediaInfo() {
        this.data.media = {
            devices: await this.getMediaDevices(),
            codecs: this.getSupportedCodecs(),
            webRTC: this.getWebRTCInfo()
        };
    }

    /**
     * Obtener dispositivos de media
     */
    async getMediaDevices() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
            return { available: false, reason: 'not_supported' };
        }

        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            return {
                available: true,
                count: devices.length,
                types: devices.reduce((acc, device) => {
                    acc[device.kind] = (acc[device.kind] || 0) + 1;
                    return acc;
                }, {})
            };
        } catch (e) {
            return { available: false, error: e.message };
        }
    }

    /**
     * Obtener códecs soportados
     */
    getSupportedCodecs() {
        const video = document.createElement('video');
        const audio = document.createElement('audio');
        
        const videoCodecs = [
            'video/mp4; codecs="avc1.42E01E"',
            'video/mp4; codecs="avc1.4D401F"',
            'video/webm; codecs="vp8"',
            'video/webm; codecs="vp9"',
            'video/ogg; codecs="theora"'
        ];
        
        const audioCodecs = [
            'audio/mpeg',
            'audio/ogg; codecs="vorbis"',
            'audio/wav; codecs="1"',
            'audio/mp4; codecs="mp4a.40.2"'
        ];
        
        return {
            video: videoCodecs.filter(codec => video.canPlayType(codec) !== ''),
            audio: audioCodecs.filter(codec => audio.canPlayType(codec) !== '')
        };
    }

    /**
     * Obtener información de WebRTC
     */
    getWebRTCInfo() {
        const RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
        
        if (!RTCPeerConnection) {
            return { available: false };
        }
        
        try {
            const pc = new RTCPeerConnection();
            const result = {
                available: true,
                iceGatheringState: pc.iceGatheringState,
                iceConnectionState: pc.iceConnectionState,
                signalingState: pc.signalingState
            };
            pc.close();
            return result;
        } catch (e) {
            return { available: false, error: e.message };
        }
    }

    /**
     * Recolectar APIs del navegador
     */
    async collectBrowserAPIs() {
        this.data.apis = {
            geolocation: !!navigator.geolocation,
            notification: 'Notification' in window,
            serviceWorker: 'serviceWorker' in navigator,
            webWorker: typeof Worker !== 'undefined',
            webAssembly: typeof WebAssembly !== 'undefined',
            webGL: this.checkWebGLSupport(),
            webGL2: this.checkWebGL2Support(),
            webVR: !!navigator.getVRDisplays,
            webXR: !!navigator.xr,
            gamepad: !!navigator.getGamepads,
            bluetooth: !!navigator.bluetooth,
            usb: !!navigator.usb,
            serial: !!navigator.serial,
            hid: !!navigator.hid,
            wakeLock: !!navigator.wakeLock,
            share: !!navigator.share,
            clipboard: !!navigator.clipboard,
            permissions: !!navigator.permissions,
            mediaSession: !!navigator.mediaSession,
            presentation: !!navigator.presentation
        };
    }

    /**
     * Verificar soporte de WebGL
     */
    checkWebGLSupport() {
        try {
            const canvas = document.createElement('canvas');
            return !!(canvas.getContext('webgl') || canvas.getContext('experimental-webgl'));
        } catch (e) {
            return false;
        }
    }

    /**
     * Verificar soporte de WebGL2
     */
    checkWebGL2Support() {
        try {
            const canvas = document.createElement('canvas');
            return !!canvas.getContext('webgl2');
        } catch (e) {
            return false;
        }
    }

    /**
     * Recolectar información de rendimiento
     */
    async collectPerformanceInfo() {
        this.data.performance = {
            timing: performance.timing ? {
                navigationStart: performance.timing.navigationStart,
                loadEventEnd: performance.timing.loadEventEnd,
                domContentLoadedEventEnd: performance.timing.domContentLoadedEventEnd
            } : null,
            memory: performance.memory ? {
                usedJSHeapSize: performance.memory.usedJSHeapSize,
                totalJSHeapSize: performance.memory.totalJSHeapSize,
                jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
            } : null,
            navigation: performance.navigation ? {
                type: performance.navigation.type,
                redirectCount: performance.navigation.redirectCount
            } : null
        };
    }

    /**
     * Generar hash simple para strings
     */
    hashCode(str) {
        let hash = 0;
        if (str.length === 0) return hash;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convertir a 32bit integer
        }
        return hash.toString(36);
    }

    /**
     * Obtener todos los datos recolectados
     */
    getData() {
        return this.data;
    }

    /**
     * Obtener resumen de fingerprint único
     */
    getFingerprint() {
        const key = JSON.stringify(this.data);
        return this.hashCode(key);
    }
}

// Exportar para uso global
if (typeof window !== 'undefined') {
    window.AdvancedFingerprint = AdvancedFingerprint;
}

// Exportar para Node.js si está disponible
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdvancedFingerprint;
}