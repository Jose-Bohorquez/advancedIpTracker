/**
 * Advanced Behavior Tracking Module
 * Módulo para seguimiento avanzado del comportamiento del usuario
 */

class AdvancedBehaviorTracker {
    constructor() {
        this.data = {
            mouseMovements: [],
            keyboardTiming: [],
            scrollPatterns: [],
            clickHeatmap: [],
            focusTime: {},
            typingSpeed: 0,
            pausePatterns: [],
            interactionTimeline: [],
            formBehavior: {},
            navigationPatterns: []
        };
        
        this.sessionStart = Date.now();
        this.lastKeyTime = 0;
        this.keystrokes = [];
        this.currentFocus = null;
        this.focusStartTime = 0;
        
        this.initialize();
    }
    
    initialize() {
        this.setupMouseTracking();
        this.setupKeyboardTracking();
        this.setupScrollTracking();
        this.setupClickTracking();
        this.setupFocusTracking();
        this.setupFormBehaviorTracking();
        this.setupNavigationTracking();
    }
    
    /**
     * Seguimiento de movimientos del mouse
     */
    setupMouseTracking() {
        let mouseData = [];
        let lastMouseTime = 0;
        
        document.addEventListener('mousemove', (e) => {
            const currentTime = Date.now();
            const timeDiff = currentTime - lastMouseTime;
            
            if (timeDiff > 50) { // Throttle para rendimiento
                mouseData.push({
                    x: e.clientX,
                    y: e.clientY,
                    timestamp: currentTime,
                    timeDiff: timeDiff,
                    velocity: this.calculateMouseVelocity(e, timeDiff)
                });
                
                // Mantener solo los últimos 100 movimientos
                if (mouseData.length > 100) {
                    mouseData.shift();
                }
                
                this.data.mouseMovements = mouseData;
                lastMouseTime = currentTime;
            }
        });
    }
    
    /**
     * Seguimiento de patrones de teclado
     */
    setupKeyboardTracking() {
        document.addEventListener('keydown', (e) => {
            const currentTime = Date.now();
            const timeDiff = currentTime - this.lastKeyTime;
            
            this.keystrokes.push({
                key: e.key,
                code: e.code,
                timestamp: currentTime,
                timeDiff: timeDiff,
                target: e.target.id || e.target.name || 'unknown'
            });
            
            this.data.keyboardTiming.push(timeDiff);
            this.lastKeyTime = currentTime;
            
            // Calcular velocidad de escritura
            this.calculateTypingSpeed();
            
            // Detectar patrones de pausa
            if (timeDiff > 2000) { // Pausa mayor a 2 segundos
                this.data.pausePatterns.push({
                    duration: timeDiff,
                    timestamp: currentTime,
                    context: e.target.id || 'unknown'
                });
            }
        });
    }
    
    /**
     * Seguimiento de patrones de scroll
     */
    setupScrollTracking() {
        let scrollData = [];
        let lastScrollTime = 0;
        let lastScrollY = 0;
        
        window.addEventListener('scroll', () => {
            const currentTime = Date.now();
            const currentScrollY = window.scrollY;
            const timeDiff = currentTime - lastScrollTime;
            const scrollDiff = currentScrollY - lastScrollY;
            
            if (timeDiff > 100) { // Throttle
                scrollData.push({
                    scrollY: currentScrollY,
                    scrollDiff: scrollDiff,
                    timestamp: currentTime,
                    timeDiff: timeDiff,
                    velocity: Math.abs(scrollDiff / timeDiff)
                });
                
                if (scrollData.length > 50) {
                    scrollData.shift();
                }
                
                this.data.scrollPatterns = scrollData;
                lastScrollTime = currentTime;
                lastScrollY = currentScrollY;
            }
        });
    }
    
    /**
     * Seguimiento de clics (mapa de calor)
     */
    setupClickTracking() {
        document.addEventListener('click', (e) => {
            this.data.clickHeatmap.push({
                x: e.clientX,
                y: e.clientY,
                timestamp: Date.now(),
                target: e.target.tagName,
                targetId: e.target.id,
                targetClass: e.target.className,
                pageX: e.pageX,
                pageY: e.pageY
            });
            
            // Registrar en timeline de interacciones
            this.data.interactionTimeline.push({
                type: 'click',
                timestamp: Date.now(),
                element: e.target.id || e.target.tagName,
                coordinates: { x: e.clientX, y: e.clientY }
            });
        });
    }
    
    /**
     * Seguimiento de tiempo de enfoque en elementos
     */
    setupFocusTracking() {
        document.addEventListener('focusin', (e) => {
            const currentTime = Date.now();
            
            // Guardar tiempo del elemento anterior
            if (this.currentFocus && this.focusStartTime) {
                const focusTime = currentTime - this.focusStartTime;
                const elementId = this.currentFocus.id || this.currentFocus.name || 'unknown';
                
                this.data.focusTime[elementId] = (this.data.focusTime[elementId] || 0) + focusTime;
            }
            
            this.currentFocus = e.target;
            this.focusStartTime = currentTime;
            
            this.data.interactionTimeline.push({
                type: 'focus',
                timestamp: currentTime,
                element: e.target.id || e.target.name || 'unknown'
            });
        });
        
        document.addEventListener('focusout', (e) => {
            if (this.currentFocus && this.focusStartTime) {
                const focusTime = Date.now() - this.focusStartTime;
                const elementId = e.target.id || e.target.name || 'unknown';
                
                this.data.focusTime[elementId] = (this.data.focusTime[elementId] || 0) + focusTime;
            }
        });
    }
    
    /**
     * Seguimiento de comportamiento en formularios
     */
    setupFormBehaviorTracking() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            const formId = form.id || 'unknown-form';
            this.data.formBehavior[formId] = {
                startTime: null,
                completionTime: null,
                fieldOrder: [],
                corrections: [],
                abandonments: 0
            };
            
            // Detectar inicio de formulario
            form.addEventListener('focusin', (e) => {
                if (!this.data.formBehavior[formId].startTime) {
                    this.data.formBehavior[formId].startTime = Date.now();
                }
                
                const fieldId = e.target.id || e.target.name;
                if (!this.data.formBehavior[formId].fieldOrder.includes(fieldId)) {
                    this.data.formBehavior[formId].fieldOrder.push(fieldId);
                }
            });
            
            // Detectar correcciones (backspace/delete)
            form.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' || e.key === 'Delete') {
                    this.data.formBehavior[formId].corrections.push({
                        field: e.target.id || e.target.name,
                        timestamp: Date.now(),
                        key: e.key
                    });
                }
            });
            
            // Detectar envío de formulario
            form.addEventListener('submit', () => {
                this.data.formBehavior[formId].completionTime = Date.now();
            });
        });
    }
    
    /**
     * Seguimiento de patrones de navegación
     */
    setupNavigationTracking() {
        // Tiempo en página
        this.data.navigationPatterns.push({
            type: 'page_load',
            timestamp: Date.now(),
            url: window.location.href,
            referrer: document.referrer
        });
        
        // Detectar intento de salida
        window.addEventListener('beforeunload', () => {
            this.data.navigationPatterns.push({
                type: 'page_unload',
                timestamp: Date.now(),
                timeOnPage: Date.now() - this.sessionStart
            });
        });
        
        // Detectar cambios de visibilidad
        document.addEventListener('visibilitychange', () => {
            this.data.navigationPatterns.push({
                type: document.hidden ? 'page_hidden' : 'page_visible',
                timestamp: Date.now()
            });
        });
    }
    
    /**
     * Calcular velocidad del mouse
     */
    calculateMouseVelocity(e, timeDiff) {
        if (this.lastMousePosition && timeDiff > 0) {
            const distance = Math.sqrt(
                Math.pow(e.clientX - this.lastMousePosition.x, 2) +
                Math.pow(e.clientY - this.lastMousePosition.y, 2)
            );
            return distance / timeDiff;
        }
        
        this.lastMousePosition = { x: e.clientX, y: e.clientY };
        return 0;
    }
    
    /**
     * Calcular velocidad de escritura
     */
    calculateTypingSpeed() {
        if (this.keystrokes.length >= 10) {
            const recentKeystrokes = this.keystrokes.slice(-10);
            const totalTime = recentKeystrokes[recentKeystrokes.length - 1].timestamp - 
                            recentKeystrokes[0].timestamp;
            
            if (totalTime > 0) {
                this.data.typingSpeed = (recentKeystrokes.length / totalTime) * 60000; // WPM
            }
        }
    }
    
    /**
     * Detectar patrones de automatización
     */
    detectAutomation() {
        const automationIndicators = {
            perfectTiming: this.checkPerfectTiming(),
            noMouseMovement: this.data.mouseMovements.length === 0,
            rapidFormFilling: this.checkRapidFormFilling(),
            uniformKeyTiming: this.checkUniformKeyTiming(),
            noScrolling: this.data.scrollPatterns.length === 0,
            suspiciousPatterns: this.checkSuspiciousPatterns()
        };
        
        return automationIndicators;
    }
    
    checkPerfectTiming() {
        const timings = this.data.keyboardTiming.slice(-10);
        if (timings.length < 5) return false;
        
        const variance = this.calculateVariance(timings);
        return variance < 10; // Muy poca variación en timing
    }
    
    checkRapidFormFilling() {
        for (const formId in this.data.formBehavior) {
            const form = this.data.formBehavior[formId];
            if (form.startTime && form.completionTime) {
                const fillTime = form.completionTime - form.startTime;
                if (fillTime < 5000 && form.fieldOrder.length > 3) { // Menos de 5 segundos para 3+ campos
                    return true;
                }
            }
        }
        return false;
    }
    
    checkUniformKeyTiming() {
        const timings = this.data.keyboardTiming;
        if (timings.length < 10) return false;
        
        const average = timings.reduce((a, b) => a + b, 0) / timings.length;
        const uniformCount = timings.filter(t => Math.abs(t - average) < 50).length;
        
        return (uniformCount / timings.length) > 0.8; // 80% de timings muy similares
    }
    
    checkSuspiciousPatterns() {
        return {
            noCorrections: Object.values(this.data.formBehavior)
                .every(form => form.corrections.length === 0),
            perfectFieldOrder: Object.values(this.data.formBehavior)
                .some(form => this.isSequentialFieldOrder(form.fieldOrder)),
            noHesitation: this.data.pausePatterns.length === 0
        };
    }
    
    isSequentialFieldOrder(fieldOrder) {
        // Verificar si los campos se llenaron en orden perfecto
        const expectedOrder = ['documentType', 'documentNumber', 'firstName', 'lastName', 'email', 'phone', 'address'];
        return fieldOrder.every((field, index) => field === expectedOrder[index]);
    }
    
    calculateVariance(numbers) {
        const mean = numbers.reduce((a, b) => a + b, 0) / numbers.length;
        const squaredDiffs = numbers.map(num => Math.pow(num - mean, 2));
        return squaredDiffs.reduce((a, b) => a + b, 0) / numbers.length;
    }
    
    /**
     * Obtener todos los datos recolectados
     */
    getAllData() {
        return {
            ...this.data,
            sessionDuration: Date.now() - this.sessionStart,
            automationDetection: this.detectAutomation(),
            summary: this.generateSummary()
        };
    }
    
    /**
     * Generar resumen de comportamiento
     */
    generateSummary() {
        return {
            totalMouseMovements: this.data.mouseMovements.length,
            totalKeystrokes: this.keystrokes.length,
            totalClicks: this.data.clickHeatmap.length,
            totalScrolls: this.data.scrollPatterns.length,
            averageTypingSpeed: this.data.typingSpeed,
            totalPauses: this.data.pausePatterns.length,
            formsStarted: Object.keys(this.data.formBehavior).length,
            formsCompleted: Object.values(this.data.formBehavior)
                .filter(form => form.completionTime).length,
            mostFocusedElement: this.getMostFocusedElement(),
            interactionDensity: this.calculateInteractionDensity()
        };
    }
    
    getMostFocusedElement() {
        let maxTime = 0;
        let mostFocused = null;
        
        for (const [element, time] of Object.entries(this.data.focusTime)) {
            if (time > maxTime) {
                maxTime = time;
                mostFocused = element;
            }
        }
        
        return { element: mostFocused, time: maxTime };
    }
    
    calculateInteractionDensity() {
        const sessionTime = Date.now() - this.sessionStart;
        const totalInteractions = this.data.interactionTimeline.length;
        
        return sessionTime > 0 ? (totalInteractions / sessionTime) * 60000 : 0; // Interacciones por minuto
    }
}

// Exportar para uso global
window.AdvancedBehaviorTracker = AdvancedBehaviorTracker;