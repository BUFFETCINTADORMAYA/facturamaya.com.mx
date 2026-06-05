<?php
/**
 * README - Instalación y Configuración de Yaxtun ERP
 */

?>
# 🌟 YAXTUN ERP - Plugin WordPress

## Características Principales

✅ **Facturación Profesional**
- SAT Compatible (CFDI 4.0)
- 8 formatos de exportación (PDF, XLSX, XML, CSV, JSON, ZIP, HTML, ODS)
- Integración directa con Facturama API
- Timbres ilimitados

✅ **Inteligencia Artificial**
- Claude (Anthropic)
- Gemini (Google)
- Conversación segura por contexto
- Transcripción de voz (OpenAI Whisper)
- Restricciones por rol de usuario

✅ **Seguridad**
- Autenticación sin contraseña en dispositivo local
- PIN de 6 dígitos para otros dispositivos
- Encriptación de datos sensibles
- Control granular de permisos
- Auditoría de acciones

✅ **Compatible**
- WordPress 5.0+
- PHP 7.4+
- Hostinger compartido
- Tema Astra

---

## Instalación Rápida

### 1. Descargar
Desde GitHub: https://github.com/BUFFETCINTADORMAYA/facturamaya.com.mx

### 2. Subir a WordPress
```
Dashboard WordPress → Plugins → Añadir nuevo
↓
Subir archivo → yaxtun-erp.zip
↓
Activar
```

### 3. Configuración Inicial

#### Paso 1: Datos de la Empresa
```
Yaxtun ERP → Configuración
- Nombre de la Empresa
- RFC
- Domicilio
```

#### Paso 2: Conectar Facturama
```
Yaxtun ERP → Configuración → API Keys

Usuario Facturama: tu_usuario@facturama.com.mx
Contraseña Facturama: tu_contraseña

[Probar Conexión] ← Si sale verde, ¡listo!
```

#### Paso 3: Configurar IAs
```
Yaxtun ERP → Configuración → API Keys

☐ Claude (Anthropic)
  API Key: sk-ant-...
  Obtén aquí: https://console.anthropic.com/

☐ Gemini (Google)
  API Key: AIzaSy...
  Obtén aquí: https://makersuite.google.com/

☐ OpenAI (Whisper - Voz)
  API Key: sk-...
  Obtén aquí: https://platform.openai.com/
```

---

## Acceso y Autenticación

### En tu Computadora (Dispositivo Local)
```
✅ Acceso DIRECTO sin contraseña
- Solo ingresa a WordPress normalmente
- El plugin te reconoce como admin local
- Acceso inmediato a Yaxtun ERP
```

### En Otro Dispositivo (Celular, Otra Computadora)
```
🔒 Requiere PIN de 6 dígitos

1. Accede a Yaxtun ERP
2. Ingresa tu email: alejandrojimenezmtzz@gmail.com
3. Ingresa tu PIN: xxxxxx (6 dígitos)
4. ¡Acceso otorgado por 24 horas!
```

### ¿Olvidaste tu PIN?
```
Dashboard WordPress (Como Admin en tu PC)
↓
Yaxtun ERP → Mi Cuenta → Generar Nuevo PIN
↓
Copia el nuevo PIN
↓
Úsalo en otro dispositivo
```

---

## Tus Credenciales de Acceso

**Email**: alejandrojimenezmtzz@gmail.com
**Contraseña**: [Generada automáticamente - revisa tu email]
**PIN**: [Generado automáticamente - disponible en admin]

---

## Uso Rápido

### Crear Nueva Factura
```
1. Yaxtun ERP → Facturas → + Nueva Factura
2. Selecciona cliente
3. Añade conceptos/líneas
4. Totales se calculan automáticamente
5. Selecciona estado (Borrador, Enviada, Pagada)
6. Guardar
```

### Exportar Factura
```
1. Yaxtun ERP → Facturas
2. Selecciona factura
3. Click en "Exportar" → Selecciona formato
   - PDF (impresión)
   - XLSX (Excel)
   - XML (SAT)
   - CSV (importación)
   - JSON (programadores)
   - ZIP (todos los formatos)
   - HTML (visualización web)
   - ODS (LibreOffice)
```

### Usar IA Assistant
```
1. Yaxtun ERP → IA Assistant
2. Selecciona proveedor (Claude, Gemini, Anthropic)
3. Escribe tu pregunta
4. Puedes usar voz 🎤
5. La IA responde sobre tu empresa (limitado por seguridad)
```

---

## Preguntas Frecuentes

**P: ¿Puedo usar Yaxtun sin contraseña siempre?**
R: Solo en tu PC/laptop. En otros dispositivos necesitas PIN por seguridad.

**P: ¿Los timbres son realmente ilimitados?**
R: Sí, depende de tu plan en Facturama. Este plugin no limita nada.

**P: ¿Puedo cambiar mi PIN?**
R: Sí, en Yaxtun ERP → Mi Cuenta → Cambiar PIN

**P: ¿Las IAs pueden ver mis datos privados?**
R: No, tienen restricciones de seguridad. Solo pueden hablar sobre tu empresa.

**P: ¿Funciona en Hostinger?**
R: Sí, está optimizado para servidor compartido. Activa "Modo Compatible" en settings.

---

## Soporte

📧 Email: alejandrojimenezmtzz@gmail.com
💬 WhatsApp: +52 (Tu número)
🌐 Web: https://facturamaya.com.mx
🐛 GitHub: https://github.com/BUFFETCINTADORMAYA/facturamaya.com.mx

---

**Versión**: 2.0.0
**Licencia**: MIT
**Última actualización**: Junio 2026
