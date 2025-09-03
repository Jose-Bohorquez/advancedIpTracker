# 🔒 Advanced IP Tracker - Herramienta Educativa de Concientización

## ⚠️ ADVERTENCIA IMPORTANTE

**Esta herramienta ha sido desarrollada EXCLUSIVAMENTE con fines educativos** para demostrar los riesgos de seguridad asociados con hacer clic en enlaces sospechosos. Su propósito es crear conciencia sobre las técnicas de phishing y recolección de datos que utilizan los ciberdelincuentes.

### 🚨 USO ÉTICO ÚNICAMENTE

- ✅ **Permitido**: Educación en ciberseguridad, demostraciones en aulas, entrenamientos corporativos
- ❌ **Prohibido**: Uso malicioso, recolección no autorizada de datos, actividades ilegales
- ⚖️ **Responsabilidad**: El usuario es completamente responsable del uso que haga de esta herramienta

## 📋 Descripción del Proyecto

Advanced IP Tracker es una herramienta educativa que simula las técnicas utilizadas por atacantes para recolectar información de dispositivos a través de enlaces maliciosos. Demuestra cómo un simple clic puede exponer una cantidad sorprendente de información personal y del dispositivo.

### 🎯 Objetivos Educativos

1. **Concientizar** sobre los riesgos de hacer clic en enlaces desconocidos
2. **Demostrar** qué información puede ser recolectada sin el conocimiento del usuario
3. **Educar** sobre técnicas de ingeniería social y phishing
4. **Promover** mejores prácticas de seguridad digital

## 🛠️ Características Técnicas

### 📊 Datos Recolectados

La herramienta puede capturar los siguientes tipos de información:

#### 🌐 Información de Red
- Dirección IP pública
- Proveedor de servicios de Internet (ISP)
- Geolocalización aproximada (país, región, ciudad)
- Tipo de conexión de red
- Información de proxy/VPN

#### 💻 Información del Dispositivo
- Sistema operativo y versión
- Navegador web y versión
- Resolución de pantalla
- Zona horaria
- Idioma del sistema
- Plugins instalados
- Información de hardware (CPU, memoria)

#### 🔍 Fingerprinting Avanzado
- Canvas fingerprinting
- WebGL fingerprinting
- Audio fingerprinting
- Fuentes instaladas
- Información de batería (si está disponible)
- Sensores del dispositivo

#### 📱 Información Adicional
- User Agent completo
- Referrer (página de origen)
- Cookies existentes
- Capacidades de almacenamiento
- Información de geolocalización (si se permite)

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

Este proyecto es de código abierto y las contribuciones son bienvenidas para mejorar su valor educativo:

1. **Fork** el repositorio
2. **Crea** una rama para tu feature
3. **Implementa** mejoras educativas
4. **Envía** un pull request

### 💡 Ideas para Contribuir

- Nuevos templates de enlaces
- Mejoras en la interfaz de usuario
- Documentación adicional
- Traducciones a otros idiomas
- Casos de estudio reales

## 📄 Licencia y Términos de Uso

### 📜 Licencia MIT

Este proyecto está licenciado bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

### ⚖️ Términos de Uso

1. **Uso Educativo**: Solo para fines educativos y de concientización
2. **Consentimiento**: Obtener consentimiento antes de usar en demostraciones
3. **Responsabilidad**: El usuario asume toda responsabilidad legal
4. **No Malicioso**: Prohibido el uso para actividades maliciosas
5. **Cumplimiento Legal**: Cumplir con todas las leyes locales aplicables

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

### Versión 1.0.0 (Actual)

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