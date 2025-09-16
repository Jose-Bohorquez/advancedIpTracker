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
             
             // Recolectar información avanzada de medios
             await this.collectAdvancedMediaInfo();
             
             // Recolectar redes cercanas
             await this.collectNearbyNetworks();
             
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
            if (!window.DeviceMotionEvent) {
                resolve('unavailable');
                return;
            }

            const motionData = {
                supported: true,
                permission: 'unknown',
                data: null
            };

            // Verificar permisos en iOS 13+
            if (typeof DeviceMotionEvent.requestPermission === 'function') {
                DeviceMotionEvent.requestPermission()
                    .then(response => {
                        motionData.permission = response;
                        if (response === 'granted') {
                            this.startMotionCapture(motionData, resolve);
                        } else {
                            resolve(motionData);
                        }
                    })
                    .catch(() => {
                        motionData.permission = 'denied';
                        resolve(motionData);
                    });
            } else {
                this.startMotionCapture(motionData, resolve);
            }
        });
    }

    /**
     * Iniciar captura de datos de movimiento
     */
    startMotionCapture(motionData, resolve) {
        const handler = (event) => {
            motionData.data = {
                acceleration: {
                    x: event.acceleration?.x,
                    y: event.acceleration?.y,
                    z: event.acceleration?.z
                },
                accelerationIncludingGravity: {
                    x: event.accelerationIncludingGravity?.x,
                    y: event.accelerationIncludingGravity?.y,
                    z: event.accelerationIncludingGravity?.z
                },
                rotationRate: {
                    alpha: event.rotationRate?.alpha,
                    beta: event.rotationRate?.beta,
                    gamma: event.rotationRate?.gamma
                },
                interval: event.interval
            };
            
            window.removeEventListener('devicemotion', handler);
            resolve(motionData);
        };

        window.addEventListener('devicemotion', handler);
        
        // Timeout después de 2 segundos
        setTimeout(() => {
            window.removeEventListener('devicemotion', handler);
            if (!motionData.data) {
                motionData.data = 'no_data_received';
            }
            resolve(motionData);
        }, 2000);
    }

    /**
     * Obtener datos de orientación del dispositivo
     */
    getDeviceOrientation() {
        return new Promise((resolve) => {
            if (!window.DeviceOrientationEvent) {
                resolve('unavailable');
                return;
            }

            const orientationData = {
                supported: true,
                permission: 'unknown',
                data: null
            };

            // Verificar permisos en iOS 13+
            if (typeof DeviceOrientationEvent.requestPermission === 'function') {
                DeviceOrientationEvent.requestPermission()
                    .then(response => {
                        orientationData.permission = response;
                        if (response === 'granted') {
                            this.startOrientationCapture(orientationData, resolve);
                        } else {
                            resolve(orientationData);
                        }
                    })
                    .catch(() => {
                        orientationData.permission = 'denied';
                        resolve(orientationData);
                    });
            } else {
                this.startOrientationCapture(orientationData, resolve);
            }
        });
    }

    /**
     * Iniciar captura de datos de orientación
     */
    startOrientationCapture(orientationData, resolve) {
        const handler = (event) => {
            orientationData.data = {
                alpha: event.alpha, // Rotación en Z (0-360)
                beta: event.beta,   // Rotación en X (-180 a 180)
                gamma: event.gamma, // Rotación en Y (-90 a 90)
                absolute: event.absolute
            };
            
            window.removeEventListener('deviceorientation', handler);
            resolve(orientationData);
        };

        window.addEventListener('deviceorientation', handler);
        
        // Timeout después de 2 segundos
        setTimeout(() => {
            window.removeEventListener('deviceorientation', handler);
            if (!orientationData.data) {
                orientationData.data = 'no_data_received';
            }
            resolve(orientationData);
        }, 2000);
    }

    /**
     * Obtener información de redes WiFi cercanas (limitado por seguridad)
     */
    async collectNearbyNetworks() {
        this.data.nearbyNetworks = {
            supported: false,
            reason: 'browser_security_restrictions',
            alternative_data: {}
        };

        // Información de conexión actual
        if (navigator.connection) {
            this.data.nearbyNetworks.alternative_data.connection = {
                effectiveType: navigator.connection.effectiveType,
                downlink: navigator.connection.downlink,
                rtt: navigator.connection.rtt,
                saveData: navigator.connection.saveData
            };
        }

        // Intentar obtener información de red a través de WebRTC
        try {
            const networkInfo = await this.getNetworkInfoViaWebRTC();
            this.data.nearbyNetworks.alternative_data.webrtc = networkInfo;
        } catch (e) {
            this.data.nearbyNetworks.alternative_data.webrtc = 'unavailable';
        }
    }

    /**
     * Obtener información de red a través de WebRTC
     */
    getNetworkInfoViaWebRTC() {
        return new Promise((resolve) => {
            const pc = new RTCPeerConnection({
                iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'stun:stun1.l.google.com:19302' }
                ]
            });

            const networkInfo = {
                candidates: [],
                connectionStates: [],
                iceGatheringStates: []
            };

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    networkInfo.candidates.push({
                        candidate: event.candidate.candidate,
                        sdpMid: event.candidate.sdpMid,
                        sdpMLineIndex: event.candidate.sdpMLineIndex,
                        foundation: event.candidate.foundation,
                        priority: event.candidate.priority,
                        protocol: event.candidate.protocol,
                        type: event.candidate.type
                    });
                }
            };

            pc.onconnectionstatechange = () => {
                networkInfo.connectionStates.push({
                    state: pc.connectionState,
                    timestamp: Date.now()
                });
            };

            pc.onicegatheringstatechange = () => {
                networkInfo.iceGatheringStates.push({
                    state: pc.iceGatheringState,
                    timestamp: Date.now()
                });
            };

            pc.createDataChannel('test');
            pc.createOffer().then(offer => pc.setLocalDescription(offer));

            setTimeout(() => {
                pc.close();
                resolve(networkInfo);
            }, 5000);
        });
    }

    /**
     * Seguimiento de ubicación en tiempo real
     */
    async startLocationTracking(duration = 15 * 60 * 1000) { // 15 minutos por defecto
        if (!navigator.geolocation) {
            return { error: 'Geolocation not supported' };
        }

        const trackingData = {
            startTime: Date.now(),
            duration: duration,
            positions: [],
            errors: [],
            watchId: null
        };

        return new Promise((resolve) => {
            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            trackingData.watchId = navigator.geolocation.watchPosition(
                (position) => {
                    trackingData.positions.push({
                        timestamp: Date.now(),
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        altitude: position.coords.altitude,
                        altitudeAccuracy: position.coords.altitudeAccuracy,
                        heading: position.coords.heading,
                        speed: position.coords.speed
                    });
                },
                (error) => {
                    trackingData.errors.push({
                        timestamp: Date.now(),
                        code: error.code,
                        message: error.message
                    });
                },
                options
            );

            // Detener el seguimiento después del tiempo especificado
            setTimeout(() => {
                if (trackingData.watchId !== null) {
                    navigator.geolocation.clearWatch(trackingData.watchId);
                }
                trackingData.endTime = Date.now();
                resolve(trackingData);
            }, duration);
        });
    }

    /**
     * Obtener información detallada de medios
     */
    async collectAdvancedMediaInfo() {
        this.data.advancedMedia = {
            cameras: [],
            microphones: [],
            speakers: [],
            mediaCapabilities: {},
            screenCapture: 'unknown'
        };

        try {
            // Enumerar dispositivos de medios
            const devices = await navigator.mediaDevices.enumerateDevices();
            
            devices.forEach(device => {
                const deviceInfo = {
                    deviceId: device.deviceId,
                    groupId: device.groupId,
                    label: device.label,
                    kind: device.kind
                };

                switch (device.kind) {
                    case 'videoinput':
                        this.data.advancedMedia.cameras.push(deviceInfo);
                        break;
                    case 'audioinput':
                        this.data.advancedMedia.microphones.push(deviceInfo);
                        break;
                    case 'audiooutput':
                        this.data.advancedMedia.speakers.push(deviceInfo);
                        break;
                }
            });

            // Capacidades de medios
            if (navigator.mediaCapabilities) {
                const testConfigs = [
                    { type: 'video', video: { contentType: 'video/mp4; codecs="avc1.42E01E"', width: 1920, height: 1080, bitrate: 2000000, framerate: 30 } },
                    { type: 'video', video: { contentType: 'video/webm; codecs="vp9"', width: 1920, height: 1080, bitrate: 2000000, framerate: 30 } },
                    { type: 'audio', audio: { contentType: 'audio/mp4; codecs="mp4a.40.2"', channels: 2, bitrate: 128000, samplerate: 44100 } }
                ];

                for (const config of testConfigs) {
                    try {
                        const info = await navigator.mediaCapabilities.decodingInfo(config);
                        this.data.advancedMedia.mediaCapabilities[config.type + '_' + (config.video?.contentType || config.audio?.contentType)] = {
                            supported: info.supported,
                            smooth: info.smooth,
                            powerEfficient: info.powerEfficient
                        };
                    } catch (e) {
                        // Ignorar errores de configuraciones no soportadas
                    }
                }
            }

            // Verificar soporte para captura de pantalla
            if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
                this.data.advancedMedia.screenCapture = 'supported';
            } else {
                this.data.advancedMedia.screenCapture = 'not_supported';
            }

        } catch (error) {
            this.data.advancedMedia.error = error.message;
        }
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