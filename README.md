# 🎯 Advanced IP Tracker - Herramienta Educativa de Ciberseguridad

## 🎓 PROPÓSITO EXCLUSIVAMENTE EDUCATIVO

> **⚠️ ATENCIÓN**: Esta herramienta ha sido desarrollada **ÚNICAMENTE** con fines educativos y de concientización en ciberseguridad. Su uso está estrictamente limitado a entornos educativos, de investigación y demostraciones autorizadas.

### 🚨 **ADVERTENCIAS CRÍTICAS**

#### ✅ **USOS PERMITIDOS Y ÉTICOS**
- 🎓 **Educación**: Enseñanza de ciberseguridad en instituciones académicas
- 🔬 **Investigación**: Estudios académicos sobre seguridad web
- 🏢 **Entrenamientos**: Capacitación corporativa en seguridad
- 🛡️ **Concientización**: Demostraciones sobre riesgos de phishing
- ✅ **Pentesting**: Pruebas de penetración autorizadas
- 📚 **Aprendizaje**: Comprensión de técnicas de fingerprinting

#### ❌ **USOS ESTRICTAMENTE PROHIBIDOS**
- 🚫 **Phishing Real**: Cualquier intento de engaño malicioso
- 🚫 **Recolección No Autorizada**: Obtención de datos sin consentimiento
- 🚫 **Actividades Ilegales**: Violación de leyes locales o internacionales
- 🚫 **Violación de Privacidad**: Acceso no autorizado a información personal
- 🚫 **Uso Comercial**: Explotación con fines lucrativos sin autorización
- 🚫 **Distribución Maliciosa**: Compartir con intenciones dañinas

#### ⚖️ **RESPONSABILIDAD LEGAL COMPLETA DEL USUARIO**

**🔴 IMPORTANTE**: Al usar esta herramienta, **TÚ ASUMES COMPLETA RESPONSABILIDAD** por:

- ✅ Cumplir con todas las leyes aplicables
- ✅ Obtener consentimiento explícito antes de demostraciones
- ✅ Proteger cualquier dato recolectado
- ✅ Usar la herramienta solo con propósitos éticos
- ✅ Respetar la privacidad y derechos de terceros

**Los desarrolladores NO son responsables del mal uso de esta herramienta.**

---

### 🛡️ **MEDIDAS DE PROTECCIÓN IMPLEMENTADAS**

- 📋 Documentación extensa sobre uso ético
- ⚠️ Advertencias claras en toda la interfaz
- 🔒 Limitaciones técnicas para prevenir abuso
- 📊 Logging para auditoría y transparencia
- 🌐 Código abierto para revisión comunitaria
- 📚 Recursos educativos sobre ciberseguridad

## 📋 Descripción del Proyecto

Advanced IP Tracker es una herramienta educativa avanzada que simula las técnicas más sofisticadas utilizadas por atacantes para recolectar información de dispositivos a través de enlaces maliciosos. Demuestra cómo un simple clic puede exponer una cantidad sorprendente de información personal y del dispositivo utilizando tecnologías modernas de fingerprinting y geolocalización híbrida.

### 🎯 Objetivos Educativos

1. **Concientizar** sobre los riesgos de hacer clic en enlaces desconocidos
2. **Demostrar** qué información puede ser recolectada sin el conocimiento del usuario
3. **Educar** sobre técnicas avanzadas de ingeniería social y phishing
4. **Promover** mejores prácticas de seguridad digital
5. **Enseñar** sobre técnicas modernas de fingerprinting y tracking
6. **Mostrar** vulnerabilidades en navegadores y dispositivos modernos

## 🛠️ Características Técnicas

### 📊 Datos Recolectados

La herramienta puede capturar los siguientes tipos de información:

#### 🌐 Información de Red
- Dirección IP pública con análisis de múltiples fuentes
- Proveedor de servicios de Internet (ISP) y organización
- Geolocalización híbrida (país, región, ciudad, coordenadas precisas)
- Tipo de conexión de red y velocidad estimada
- Información avanzada de proxy/VPN/Tor
- Análisis de latencia y calidad de conexión
- Detección de redes corporativas y educativas

#### 💻 Información del Dispositivo
- Sistema operativo y versión detallada
- Navegador web, versión y motor de renderizado
- Resolución de pantalla, densidad de píxeles y orientación
- Zona horaria y configuración regional
- Idiomas del sistema y preferencias
- Plugins instalados y extensiones detectables
- Información avanzada de hardware (CPU, GPU, memoria)
- Capacidades multimedia y codecs soportados

#### 🔍 Fingerprinting Avanzado
- Canvas fingerprinting con múltiples técnicas
- WebGL fingerprinting y capacidades gráficas
- Audio fingerprinting y análisis de contexto
- Fuentes instaladas y renderizado de texto
- Información detallada de batería y sensores
- Análisis de comportamiento del usuario
- Detección de automatización y bots
- Fingerprinting de red y conectividad

#### 📱 Información Adicional
- User Agent completo y análisis de componentes
- Referrer (página de origen) y cadena de navegación
- Cookies existentes y almacenamiento local
- Capacidades de almacenamiento y APIs disponibles
- Geolocalización de alta precisión (GPS)
- Seguimiento de ubicación en tiempo real
- Análisis de patrones de interacción
- Métricas de rendimiento del dispositivo

### 🏗️ Arquitectura del Sistema

```
advanced-ip-tracker/
├── frontend/           # Páginas de captura
│   ├── index.html     # Página principal de demostración
│   └── track.php      # Manejador de enlaces únicos
├── backend/           # Lógica del servidor
│   ├── collect.php    # API de recolección de datos
│   └── config.php     # Configuraciones del sistema
├── admin/             # Panel de administración
│   ├── dashboard.php  # Dashboard principal
│   ├── link-generator.php # Generador de enlaces
│   └── assets/        # CSS y JavaScript
├── data/              # Almacenamiento de datos
│   ├── captures/      # Datos capturados
│   └── links/         # Enlaces generados
└── logs/              # Archivos de registro
```

## 🚀 Instalación y Configuración

### 📋 Requisitos

- Servidor web (Apache/Nginx)
- PHP 7.4 o superior
- Extensiones PHP: json, curl, mbstring
- Permisos de escritura en directorios `data/` y `logs/`

### 🔧 Instalación

1. **Clonar o descargar** el proyecto en tu servidor web
2. **Configurar permisos** de escritura:
   ```bash
   chmod 755 data/ logs/
   chmod 644 data/* logs/*
   ```
3. **Configurar** el archivo `backend/config.php` según tu entorno
4. **Acceder** al panel de administración: `http://tu-servidor/advanced-ip-tracker/admin/`

### ⚙️ Configuración

Edita el archivo `backend/config.php` para personalizar:

- URLs base del sistema
- Configuraciones de seguridad
- APIs externas para geolocalización
- Límites de rate limiting
- Configuraciones de logging

## 📖 Guía de Uso Educativo

### 👨‍🏫 Para Educadores

1. **Preparación**:
   - Configura la herramienta en un entorno controlado
   - Prepara ejemplos de enlaces maliciosos comunes
   - Documenta los datos que se pueden recolectar

2. **Demostración**:
   - Genera enlaces de prueba usando diferentes templates
   - Muestra cómo se ven los enlaces "legítimos"
   - Demuestra la cantidad de datos recolectados

3. **Análisis**:
   - Revisa los datos capturados con los estudiantes
   - Explica las implicaciones de privacidad
   - Discute métodos de protección

### 🎓 Para Estudiantes

1. **Observa** cómo un enlace aparentemente inocente puede recolectar datos
2. **Analiza** qué información de tu dispositivo fue capturada
3. **Reflexiona** sobre las implicaciones de privacidad y seguridad
4. **Aprende** a identificar enlaces sospechosos

## 🛡️ Medidas de Protección

### 🔒 Para Usuarios

- **Verificar URLs** antes de hacer clic
- **Usar navegadores actualizados** con protecciones anti-phishing
- **Configurar VPN** para ocultar la IP real
- **Deshabilitar JavaScript** en sitios no confiables
- **Usar extensiones de seguridad** como uBlock Origin
- **Verificar certificados SSL** de los sitios web

### 🏢 Para Organizaciones

- **Implementar filtros de URL** en firewalls
- **Capacitar empleados** sobre phishing
- **Usar sandboxing** para enlaces sospechosos
- **Monitorear tráfico de red** anómalo
- **Implementar políticas de seguridad** estrictas

## 🔍 Análisis de Riesgos Demostrados

### 📊 Nivel de Exposición

| Tipo de Dato | Riesgo | Impacto |
|--------------|--------|----------|
| IP Pública | Alto | Geolocalización, identificación |
| User Agent | Medio | Fingerprinting, vulnerabilidades |
| Resolución de Pantalla | Bajo | Fingerprinting adicional |
| Zona Horaria | Medio | Correlación de ubicación |
| Plugins | Alto | Vectores de ataque |
| Geolocalización | Crítico | Ubicación exacta |

### 🎯 Vectores de Ataque Comunes

1. **Phishing por Email**: Enlaces en correos fraudulentos
2. **Redes Sociales**: Enlaces compartidos maliciosamente
3. **Mensajería**: WhatsApp, Telegram, SMS
4. **Publicidad Maliciosa**: Banners y pop-ups
5. **Sitios Comprometidos**: Redirecciones maliciosas

## 📚 Recursos Educativos Adicionales

### 🔗 Enlaces Útiles

- [OWASP Phishing Guide](https://owasp.org/www-community/attacks/Phishing)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)
- [Have I Been Pwned](https://haveibeenpwned.com/)
- [PhishTank](https://www.phishtank.com/)

### 📖 Lecturas Recomendadas

- "The Art of Deception" por Kevin Mitnick
- "Social Engineering: The Science of Human Hacking" por Christopher Hadnagy
- "Phishing Dark Waters" por Michele Fincher

## 🤝 Contribuciones

¡Las contribuciones son bienvenidas y muy apreciadas! Este proyecto prospera gracias a la colaboración de la comunidad de ciberseguridad y desarrolladores éticos.

### 🚀 **Cómo Contribuir**

#### 1. **Fork y Clone**
```bash
# Fork el repositorio en GitHub
# Luego clona tu fork
git clone https://github.com/Jose-Bohorquez/advancedIpTracker.git
cd advancedIpTracker
```

#### 2. **Crear una Rama**
```bash
# Crea una rama para tu contribución
git checkout -b feature/nueva-funcionalidad
# o
git checkout -b fix/correccion-bug
```

#### 3. **Desarrollar y Probar**
- Implementa tu contribución
- Asegúrate de que el código siga las convenciones del proyecto
- Prueba exhaustivamente tu código
- Documenta los cambios apropiadamente

#### 4. **Commit y Push**
```bash
# Commits descriptivos
git add .
git commit -m "feat: añadir nueva técnica de fingerprinting"
git push origin feature/nueva-funcionalidad
```

#### 5. **Pull Request**
- Crea un Pull Request detallado
- Describe qué cambios realizaste y por qué
- Incluye capturas de pantalla si es relevante
- Referencia issues relacionados

### 💡 **Ideas de Contribución**

#### 🎨 **Mejoras de Interfaz**
- Diseño responsive mejorado
- Nuevos temas y estilos (modo oscuro/claro)
- Componentes de UI modernos
- Animaciones y transiciones suaves
- Accesibilidad web (WCAG 2.1)
- Internacionalización (i18n)

#### 🔧 **Funcionalidades Técnicas**
- Nuevas técnicas de fingerprinting ético
- Análisis de comportamiento avanzado
- Integración con APIs de geolocalización
- Sistemas de alertas y notificaciones
- Exportación de datos en múltiples formatos
- Métricas y analytics avanzados
- Optimización de rendimiento

#### 📚 **Documentación y Educación**
- Tutoriales paso a paso
- Videos educativos
- Casos de estudio reales (anonimizados)
- Guías de mejores prácticas
- Traducción a otros idiomas
- Documentación de API
- Ejemplos de uso ético

#### 🛡️ **Seguridad y Privacidad**
- Auditorías de seguridad
- Implementación de cifrado
- Políticas de retención de datos
- Anonización de información
- Cumplimiento con regulaciones (GDPR, CCPA)
- Pruebas de penetración éticas

#### 🧪 **Testing y Calidad**
- Tests unitarios y de integración
- Tests de rendimiento
- Tests de seguridad
- Automatización de CI/CD
- Análisis de código estático
- Cobertura de código

### ✅ **Tipos de Contribución Aceptados**

- **💻 Código**: Nuevas funcionalidades, corrección de bugs, optimizaciones
- **📖 Documentación**: Mejoras en README, wikis, comentarios de código
- **🎨 Diseño**: Mockups, prototipos, mejoras de UX/UI
- **🧪 Testing**: Pruebas de funcionalidad, reportes de bugs
- **🎓 Educación**: Contenido educativo, tutoriales, ejemplos
- **🌍 Traducción**: Localización a diferentes idiomas
- **🔬 Investigación**: Nuevas técnicas éticas, análisis de seguridad
- **📊 Análisis**: Métricas, estadísticas, informes de uso

### ❌ **Contribuciones NO Aceptadas**

- ⛔ Código malicioso o con intenciones dañinas
- ⛔ Funcionalidades que violen la privacidad sin consentimiento
- ⛔ Implementaciones que faciliten actividades ilegales
- ⛔ Código sin documentación apropiada
- ⛔ Contribuciones que no sigan las pautas éticas
- ⛔ Backdoors o vulnerabilidades intencionales
- ⛔ Técnicas de evasión de detección maliciosa

### 📋 **Pautas de Contribución**

#### **Código**
- Sigue las convenciones de nomenclatura existentes
- Comenta el código complejo apropiadamente
- Incluye tests para nuevas funcionalidades
- Mantén la compatibilidad hacia atrás cuando sea posible
- Optimiza para rendimiento y seguridad

#### **Documentación**
- Usa Markdown para documentación
- Incluye ejemplos prácticos
- Mantén la documentación actualizada
- Traduce contenido importante

#### **Commits**
- Usa mensajes de commit descriptivos
- Sigue el formato: `tipo(alcance): descripción`
- Ejemplos: `feat(ui): añadir modo oscuro`, `fix(api): corregir validación de datos`

### 🏆 **Reconocimiento de Contribuidores**

Todos los contribuidores serán reconocidos en:
- 📜 Sección de agradecimientos del README
- 👥 Archivo CONTRIBUTORS.md dedicado
- 🚀 Releases y changelogs
- 📚 Documentación del proyecto
- 🌟 Hall of Fame de contribuidores

### 🎯 **Roadmap de Contribuciones**

#### **Próximas Prioridades**
1. Mejoras de accesibilidad web
2. Implementación de tests automatizados
3. Optimización de rendimiento
4. Documentación multiidioma
5. Integración con herramientas de CI/CD

### 📧 **Contacto para Contribuciones**

¿Tienes ideas o preguntas sobre contribuciones?

- 🐛 **Issues**: Abre un Issue en GitHub para bugs o sugerencias
- 💬 **Discusiones**: Usa GitHub Discussions para ideas generales
- 📧 **Email**: Contacta para colaboraciones especiales
- 🐦 **Social**: Síguenos para actualizaciones del proyecto

### 🤝 **Código de Conducta**

Todos los contribuidores deben adherirse a nuestro código de conducta:

- 🤝 Ser respetuoso y profesional
- 🎓 Mantener el enfoque educativo
- 🛡️ Priorizar la ética y la seguridad
- 🌍 Ser inclusivo y welcoming
- 📚 Compartir conocimiento constructivamente

---

**¡Gracias por considerar contribuir a este proyecto educativo! Juntos podemos hacer de la web un lugar más seguro a través de la educación y la concientización.**

## 📄 Licencia y Términos de Uso

### 📜 Licencia MIT

Este proyecto está licenciado bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

### ⚖️ Términos de Uso y Responsabilidades

#### 🎓 **USO EXCLUSIVAMENTE EDUCATIVO**

Esta herramienta ha sido desarrollada **ÚNICAMENTE** con propósitos educativos y de concientización en ciberseguridad. Su uso está estrictamente limitado a:

✅ **Usos Permitidos:**
- Educación en ciberseguridad y ethical hacking
- Demostraciones en aulas y entornos académicos
- Entrenamientos corporativos de seguridad
- Investigación académica en ciberseguridad
- Pruebas de penetración autorizadas
- Concientización sobre riesgos de phishing

❌ **Usos Prohibidos:**
- Recolección no autorizada de datos personales
- Actividades de phishing real o maliciosas
- Violación de privacidad de terceros
- Cualquier actividad ilegal o no ética
- Uso comercial sin autorización
- Distribución con fines maliciosos

#### ⚖️ **RESPONSABILIDAD LEGAL**

**EL USUARIO ASUME COMPLETA RESPONSABILIDAD** por el uso de esta herramienta:

1. **Cumplimiento Legal**: Debes cumplir con todas las leyes locales, nacionales e internacionales aplicables
2. **Consentimiento Informado**: Obtener consentimiento explícito antes de usar en demostraciones con datos reales
3. **Protección de Datos**: Respetar las leyes de protección de datos (GDPR, CCPA, etc.)
4. **Uso Ético**: Mantener estándares éticos en todo momento
5. **No Responsabilidad del Desarrollador**: Los desarrolladores NO son responsables del mal uso de esta herramienta

#### 🛡️ **MEDIDAS DE PROTECCIÓN IMPLEMENTADAS**

- Advertencias claras sobre el propósito educativo
- Documentación extensa sobre uso ético
- Limitaciones técnicas para prevenir abuso
- Logging para auditoría y transparencia
- Código abierto para revisión de la comunidad

#### 📋 **CONDICIONES DE USO**

**Al usar esta herramienta, aceptas:**

1. Usarla solo con fines educativos legítimos
2. No utilizarla para dañar, engañar o explotar a otros
3. Obtener permisos apropiados antes de demostraciones
4. Proteger cualquier dato recolectado durante pruebas
5. Reportar cualquier vulnerabilidad encontrada responsablemente
6. No modificar el código para propósitos maliciosos
7. Dar crédito apropiado al proyecto original

#### 🚨 **DESCARGO DE RESPONSABILIDAD**

**IMPORTANTE**: Los desarrolladores de este proyecto:

- NO son responsables por el uso indebido de esta herramienta
- NO proporcionan garantías sobre la funcionalidad
- NO se hacen responsables por daños directos o indirectos
- NO aprueban ni fomentan actividades maliciosas
- Proporcionan esta herramienta "tal como está"

#### 📞 **REPORTE DE USO INDEBIDO**

Si descubres que esta herramienta está siendo utilizada de manera maliciosa:

- Reporta el incidente a las autoridades apropiadas
- Contacta a los desarrolladores del proyecto
- Proporciona evidencia del mal uso
- Ayuda a proteger a la comunidad

---

**⚠️ RECORDATORIO FINAL**: Esta herramienta es un arma de doble filo. En las manos correctas, educa y protege. En las manos equivocadas, puede causar daño. Úsala sabiamente y siempre con propósitos éticos y educativos.

## 🆘 Soporte y Contacto

### 🐛 Reportar Problemas

Si encuentras bugs o tienes sugerencias:

1. Verifica que no sea un problema conocido
2. Crea un issue detallado en GitHub
3. Incluye pasos para reproducir el problema
4. Proporciona información del entorno

### 💬 Comunidad

- **Discusiones**: GitHub Discussions
- **Issues**: GitHub Issues
- **Email**: [Contacto del desarrollador]

## 🔄 Changelog

### 🔄 Changelog

### Versión 2.0.0 (Actual) - Enero 2025

#### 🚀 **Nuevas Funcionalidades Principales**
- ✅ **Sistema de Geolocalización Híbrida**: Combinación de IP, GPS y triangulación de red
- ✅ **Fingerprinting Avanzado**: Canvas, WebGL, Audio y análisis de comportamiento
- ✅ **Seguimiento en Tiempo Real**: Monitoreo continuo de ubicación por 30 minutos
- ✅ **Recolección de Datos Optimizada**: Más de 100 puntos de datos únicos
- ✅ **Sistema de Integración Automática**: Script de fingerprinting independiente

#### 🔧 **Mejoras Técnicas**
- ✅ **APIs Múltiples de Geolocalización**: ip-api.com, ipinfo.io, ipapi.co
- ✅ **Detección de Automatización**: Identificación de bots y herramientas automatizadas
- ✅ **Análisis de Red Avanzado**: Detección de VPN, Proxy y Tor
- ✅ **Métricas de Rendimiento**: Análisis de velocidad y capacidades del dispositivo
- ✅ **Fingerprinting de Hardware**: CPU, GPU, memoria y sensores

#### 🎨 **Mejoras de Interfaz**
- ✅ **Diseño Moderno**: Gradientes y efectos visuales mejorados
- ✅ **Animaciones Fluidas**: Transiciones suaves y efectos interactivos
- ✅ **Indicadores de Progreso**: Visualización del estado en tiempo real
- ✅ **Tooltips Informativos**: Ayuda contextual para usuarios
- ✅ **Responsive Design**: Optimización para dispositivos móviles

#### 🛡️ **Seguridad y Estabilidad**
- ✅ **Manejo de Errores Mejorado**: Recuperación automática de fallos
- ✅ **Validación de Datos**: Sanitización y validación en backend
- ✅ **Headers CORS Optimizados**: Mejor compatibilidad entre navegadores
- ✅ **Rate Limiting**: Protección contra abuso del sistema
- ✅ **Logging Avanzado**: Registro detallado para auditoría

#### 📊 **Métricas de Rendimiento**
- **Tiempo de carga**: ~2-3 segundos
- **Datos recolectados**: 100+ puntos de datos únicos
- **Precisión de ubicación**: ±5-10 metros (con GPS)
- **Compatibilidad**: 98%+ navegadores modernos
- **Tamaño de payload**: ~25-35KB por sesión

### Versión 1.0.0 (Anterior)

- ✅ Sistema completo de captura de datos
- ✅ Panel de administración funcional
- ✅ Generador de enlaces personalizados
- ✅ Dashboard con estadísticas
- ✅ Documentación educativa completa
- ✅ Múltiples templates de phishing
- ✅ Sistema de logging avanzado

## 🙏 Agradecimientos

Este proyecto fue desarrollado con fines puramente educativos para ayudar a crear conciencia sobre los riesgos de ciberseguridad. Agradecemos a:

- La comunidad de ciberseguridad por compartir conocimiento
- Los educadores que utilizan herramientas como esta responsablemente
- Los desarrolladores de las tecnologías utilizadas

---

**Recuerda**: La mejor defensa contra el phishing es la educación y la conciencia. Usa esta herramienta responsablemente para crear un internet más seguro para todos.

---

*Desarrollado con ❤️ para la educación en ciberseguridad*