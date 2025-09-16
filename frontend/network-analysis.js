/**
 * Network Analysis Module
 * Módulo para análisis avanzado de red y conexión del usuario
 */

class NetworkAnalyzer {
    constructor() {
        this.data = {
            connectionInfo: {},
            performanceMetrics: {},
            networkTiming: {},
            bandwidthEstimate: 0,
            latencyTests: [],
            connectionStability: {},
            resourceLoadTimes: [],
            dnsResolutionTimes: [],
            tcpConnectionTimes: [],
            tlsHandshakeTimes: []
        };
        
        this.initialize();
    }
    
    initialize() {
        this.analyzeConnection();
        this.measurePerformance();
        this.testLatency();
        this.monitorConnectionStability();
        this.analyzeResourceLoading();
        this.estimateBandwidth();
    }
    
    /**
     * Analizar información de conexión básica
     */
    analyzeConnection() {
        if ('connection' in navigator) {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            
            this.data.connectionInfo = {
                effectiveType: connection.effectiveType,
                downlink: connection.downlink,
                downlinkMax: connection.downlinkMax,
                rtt: connection.rtt,
                saveData: connection.saveData,
                type: connection.type
            };
            
            // Monitorear cambios en la conexión
            connection.addEventListener('change', () => {
                this.data.connectionInfo.changes = this.data.connectionInfo.changes || [];
                this.data.connectionInfo.changes.push({
                    timestamp: Date.now(),
                    effectiveType: connection.effectiveType,
                    downlink: connection.downlink,
                    rtt: connection.rtt
                });
            });
        }
    }
    
    /**
     * Medir métricas de rendimiento
     */
    measurePerformance() {
        if ('performance' in window) {
            const navigation = performance.getEntriesByType('navigation')[0];
            
            if (navigation) {
                this.data.performanceMetrics = {
                    domainLookupTime: navigation.domainLookupEnd - navigation.domainLookupStart,
                    tcpConnectTime: navigation.connectEnd - navigation.connectStart,
                    tlsTime: navigation.secureConnectionStart > 0 ? 
                        navigation.connectEnd - navigation.secureConnectionStart : 0,
                    requestTime: navigation.responseStart - navigation.requestStart,
                    responseTime: navigation.responseEnd - navigation.responseStart,
                    domProcessingTime: navigation.domContentLoadedEventStart - navigation.responseEnd,
                    loadCompleteTime: navigation.loadEventEnd - navigation.loadEventStart,
                    totalLoadTime: navigation.loadEventEnd - navigation.navigationStart
                };
                
                this.data.networkTiming = {
                    dnsLookup: navigation.domainLookupEnd - navigation.domainLookupStart,
                    tcpConnection: navigation.connectEnd - navigation.connectStart,
                    tlsHandshake: navigation.secureConnectionStart > 0 ? 
                        navigation.connectEnd - navigation.secureConnectionStart : 0,
                    serverResponse: navigation.responseStart - navigation.requestStart,
                    contentDownload: navigation.responseEnd - navigation.responseStart
                };
            }
        }
    }
    
    /**
     * Probar latencia con múltiples métodos
     */
    async testLatency() {
        const tests = [
            this.pingTest(),
            this.imageLatencyTest(),
            this.fetchLatencyTest(),
            this.websocketLatencyTest()
        ];
        
        try {
            const results = await Promise.allSettled(tests);
            this.data.latencyTests = results.map((result, index) => ({
                method: ['ping', 'image', 'fetch', 'websocket'][index],
                status: result.status,
                latency: result.status === 'fulfilled' ? result.value : null,
                error: result.status === 'rejected' ? result.reason : null
            }));
        } catch (error) {
            console.warn('Error en pruebas de latencia:', error);
        }
    }
    
    /**
     * Test de ping usando imagen
     */
    pingTest() {
        return new Promise((resolve, reject) => {
            const start = performance.now();
            const img = new Image();
            
            img.onload = () => {
                const latency = performance.now() - start;
                resolve(latency);
            };
            
            img.onerror = () => {
                reject('Ping test failed');
            };
            
            // Usar un pixel transparente pequeño
            img.src = `data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7?t=${Date.now()}`;
            
            setTimeout(() => reject('Ping timeout'), 5000);
        });
    }
    
    /**
     * Test de latencia con imagen
     */
    imageLatencyTest() {
        return new Promise((resolve, reject) => {
            const start = performance.now();
            const img = new Image();
            
            img.onload = () => {
                const latency = performance.now() - start;
                resolve(latency);
            };
            
            img.onerror = () => {
                reject('Image latency test failed');
            };
            
            // Usar una imagen pequeña de un CDN confiable
            img.src = `https://httpbin.org/image/png?t=${Date.now()}`;
            
            setTimeout(() => reject('Image latency timeout'), 10000);
        });
    }
    
    /**
     * Test de latencia con fetch
     */
    async fetchLatencyTest() {
        const start = performance.now();
        
        try {
            const response = await fetch(`https://httpbin.org/get?t=${Date.now()}`, {
                method: 'GET',
                cache: 'no-cache'
            });
            
            if (response.ok) {
                return performance.now() - start;
            } else {
                throw new Error('Fetch failed');
            }
        } catch (error) {
            throw new Error('Fetch latency test failed');
        }
    }
    
    /**
     * Test de latencia con WebSocket
     */
    websocketLatencyTest() {
        return new Promise((resolve, reject) => {
            try {
                const start = performance.now();
                const ws = new WebSocket('wss://echo.websocket.org');
                
                ws.onopen = () => {
                    const openLatency = performance.now() - start;
                    ws.send('ping');
                    
                    ws.onmessage = () => {
                        const totalLatency = performance.now() - start;
                        ws.close();
                        resolve(totalLatency);
                    };
                };
                
                ws.onerror = () => {
                    reject('WebSocket latency test failed');
                };
                
                setTimeout(() => {
                    ws.close();
                    reject('WebSocket latency timeout');
                }, 10000);
                
            } catch (error) {
                reject('WebSocket not supported');
            }
        });
    }
    
    /**
     * Monitorear estabilidad de conexión
     */
    monitorConnectionStability() {
        let connectionLost = 0;
        let connectionRestored = 0;
        const startTime = Date.now();
        
        window.addEventListener('online', () => {
            connectionRestored++;
            this.data.connectionStability.restored = connectionRestored;
            this.data.connectionStability.lastRestored = Date.now();
        });
        
        window.addEventListener('offline', () => {
            connectionLost++;
            this.data.connectionStability.lost = connectionLost;
            this.data.connectionStability.lastLost = Date.now();
        });
        
        this.data.connectionStability = {
            isOnline: navigator.onLine,
            lost: connectionLost,
            restored: connectionRestored,
            monitoringStart: startTime
        };
    }
    
    /**
     * Analizar tiempos de carga de recursos
     */
    analyzeResourceLoading() {
        if ('performance' in window) {
            const resources = performance.getEntriesByType('resource');
            
            this.data.resourceLoadTimes = resources.map(resource => ({
                name: resource.name,
                type: this.getResourceType(resource.name),
                duration: resource.duration,
                size: resource.transferSize || 0,
                cached: resource.transferSize === 0 && resource.decodedBodySize > 0,
                dnsTime: resource.domainLookupEnd - resource.domainLookupStart,
                tcpTime: resource.connectEnd - resource.connectStart,
                tlsTime: resource.secureConnectionStart > 0 ? 
                    resource.connectEnd - resource.secureConnectionStart : 0,
                requestTime: resource.responseStart - resource.requestStart,
                responseTime: resource.responseEnd - resource.responseStart
            }));
            
            // Separar tiempos por tipo
            this.data.dnsResolutionTimes = this.data.resourceLoadTimes
                .map(r => r.dnsTime)
                .filter(t => t > 0);
                
            this.data.tcpConnectionTimes = this.data.resourceLoadTimes
                .map(r => r.tcpTime)
                .filter(t => t > 0);
                
            this.data.tlsHandshakeTimes = this.data.resourceLoadTimes
                .map(r => r.tlsTime)
                .filter(t => t > 0);
        }
    }
    
    /**
     * Estimar ancho de banda
     */
    async estimateBandwidth() {
        try {
            // Test con imagen de tamaño conocido
            const testSizes = [
                { url: 'https://httpbin.org/bytes/1024', size: 1024 },      // 1KB
                { url: 'https://httpbin.org/bytes/10240', size: 10240 },    // 10KB
                { url: 'https://httpbin.org/bytes/102400', size: 102400 }   // 100KB
            ];
            
            const bandwidthTests = [];
            
            for (const test of testSizes) {
                try {
                    const start = performance.now();
                    const response = await fetch(`${test.url}?t=${Date.now()}`, {
                        cache: 'no-cache'
                    });
                    
                    if (response.ok) {
                        await response.blob();
                        const duration = (performance.now() - start) / 1000; // segundos
                        const bandwidth = (test.size * 8) / duration; // bits por segundo
                        
                        bandwidthTests.push({
                            size: test.size,
                            duration: duration,
                            bandwidth: bandwidth
                        });
                    }
                } catch (error) {
                    console.warn(`Error en test de ancho de banda para ${test.size} bytes:`, error);
                }
            }
            
            if (bandwidthTests.length > 0) {
                // Promedio de los tests exitosos
                this.data.bandwidthEstimate = bandwidthTests.reduce((sum, test) => 
                    sum + test.bandwidth, 0) / bandwidthTests.length;
            }
            
        } catch (error) {
            console.warn('Error estimando ancho de banda:', error);
        }
    }
    
    /**
     * Determinar tipo de recurso
     */
    getResourceType(url) {
        const extension = url.split('.').pop().toLowerCase().split('?')[0];
        
        const types = {
            'js': 'script',
            'css': 'stylesheet',
            'png': 'image',
            'jpg': 'image',
            'jpeg': 'image',
            'gif': 'image',
            'svg': 'image',
            'woff': 'font',
            'woff2': 'font',
            'ttf': 'font',
            'eot': 'font',
            'mp4': 'video',
            'webm': 'video',
            'mp3': 'audio',
            'wav': 'audio'
        };
        
        return types[extension] || 'other';
    }
    
    /**
     * Detectar uso de VPN/Proxy
     */
    async detectVpnProxy() {
        const indicators = {
            suspiciousLatency: false,
            inconsistentLocation: false,
            knownVpnProvider: false,
            unusualDnsServers: false,
            timeZoneMismatch: false
        };
        
        try {
            // Verificar latencia sospechosa
            const avgLatency = this.data.latencyTests
                .filter(test => test.latency !== null)
                .reduce((sum, test) => sum + test.latency, 0) / this.data.latencyTests.length;
                
            indicators.suspiciousLatency = avgLatency > 500; // Más de 500ms promedio
            
            // Verificar inconsistencia de zona horaria
            const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const timezoneOffset = new Date().getTimezoneOffset();
            
            // Esto requeriría comparar con la ubicación IP detectada
            // indicators.timeZoneMismatch = ...
            
        } catch (error) {
            console.warn('Error detectando VPN/Proxy:', error);
        }
        
        return indicators;
    }
    
    /**
     * Generar huella digital de red
     */
    generateNetworkFingerprint() {
        const fingerprint = {
            connectionType: this.data.connectionInfo.effectiveType || 'unknown',
            downlinkSpeed: this.data.connectionInfo.downlink || 0,
            rtt: this.data.connectionInfo.rtt || 0,
            bandwidthEstimate: this.data.bandwidthEstimate,
            avgDnsTime: this.calculateAverage(this.data.dnsResolutionTimes),
            avgTcpTime: this.calculateAverage(this.data.tcpConnectionTimes),
            avgTlsTime: this.calculateAverage(this.data.tlsHandshakeTimes),
            connectionStability: this.data.connectionStability.lost || 0,
            resourceCacheRatio: this.calculateCacheRatio(),
            performanceProfile: this.generatePerformanceProfile()
        };
        
        return fingerprint;
    }
    
    calculateAverage(array) {
        if (array.length === 0) return 0;
        return array.reduce((sum, val) => sum + val, 0) / array.length;
    }
    
    calculateCacheRatio() {
        const totalResources = this.data.resourceLoadTimes.length;
        if (totalResources === 0) return 0;
        
        const cachedResources = this.data.resourceLoadTimes.filter(r => r.cached).length;
        return cachedResources / totalResources;
    }
    
    generatePerformanceProfile() {
        return {
            totalLoadTime: this.data.performanceMetrics.totalLoadTime || 0,
            domProcessingTime: this.data.performanceMetrics.domProcessingTime || 0,
            networkEfficiency: this.calculateNetworkEfficiency(),
            resourceOptimization: this.calculateResourceOptimization()
        };
    }
    
    calculateNetworkEfficiency() {
        const networkTime = (this.data.performanceMetrics.domainLookupTime || 0) +
                           (this.data.performanceMetrics.tcpConnectTime || 0) +
                           (this.data.performanceMetrics.requestTime || 0) +
                           (this.data.performanceMetrics.responseTime || 0);
                           
        const totalTime = this.data.performanceMetrics.totalLoadTime || 1;
        
        return networkTime / totalTime;
    }
    
    calculateResourceOptimization() {
        const totalSize = this.data.resourceLoadTimes.reduce((sum, r) => sum + r.size, 0);
        const totalTime = this.data.resourceLoadTimes.reduce((sum, r) => sum + r.duration, 0);
        
        return totalTime > 0 ? totalSize / totalTime : 0;
    }
    
    /**
     * Obtener todos los datos de análisis de red
     */
    getAllData() {
        return {
            ...this.data,
            networkFingerprint: this.generateNetworkFingerprint(),
            vpnProxyIndicators: this.detectVpnProxy(),
            analysisTimestamp: Date.now()
        };
    }
}

// Exportar para uso global
window.NetworkAnalyzer = NetworkAnalyzer;