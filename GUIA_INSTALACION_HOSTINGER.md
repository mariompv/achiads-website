# 🚀 Guía de Instalación Completa - ACHIADS en Hostinger

## ⚡ Instalación Rápida (15 minutos)

### ✅ Lo que necesitas antes de empezar:
- [ ] Cuenta de Hostinger activa
- [ ] Dominio configurado (ej: achiads.cl)
- [ ] Todos los archivos del sitio web descargados

---

## 📋 PASO 1: Preparar los archivos en tu computadora

### 1.1 Crear la estructura de carpetas

Crea esta estructura en tu computadora:

```
📁 achiads-website/
├── 📄 index.html
├── 📄 .htaccess
├── 📄 robots.txt
├── 📄 sitemap.xml
├── 📄 favicon.svg
├── 📄 404.html
├── 📄 500.html
├── 📄 contact_form.php
├── 📄 manifest.json
├── 📁 assets/
│   ├── 📁 css/
│   │   └── 📄 styles.css
│   ├── 📁 js/
│   │   └── 📄 main.js
│   └── 📁 images/ (vacía por ahora)
```

### 1.2 Colocar cada archivo en su lugar

- ✅ `index.html` → raíz de la carpeta
- ✅ `assets/css/styles.css` → dentro de assets/css/
- ✅ `assets/js/main.js` → dentro de assets/js/
- ✅ Todos los demás archivos → raíz de la carpeta

---

## 🌐 PASO 2: Acceder a tu panel de Hostinger

### 2.1 Iniciar sesión
1. **Ve a:** [hostinger.com](https://www.hostinger.com)
2. **Clic en:** "Iniciar sesión"
3. **Ingresa:** tu email y contraseña
4. **Clic en:** "Iniciar sesión"

### 2.2 Acceder al Administrador de Archivos
1. **En el panel principal, busca:** "Administrador de archivos"
2. **Clic en:** "Administrador de archivos"
3. **Aparecerá:** una interfaz similar a un explorador de archivos

---

## 📁 PASO 3: Subir archivos a Hostinger

### 3.1 Navegar a la carpeta correcta
1. **Busca la carpeta:** `public_html`
2. **Doble clic** para abrirla
3. **Verás:** posiblemente algunos archivos existentes

### 3.2 Limpiar archivos anteriores (opcional)
Si hay archivos como `index.html` o `index.php` antiguos:
1. **Selecciona** los archivos antiguos
2. **Clic derecho** → "Eliminar"
3. **Confirma** la eliminación

### 3.3 Subir los archivos principales

#### Método A: Arrastrar y soltar (Recomendado)
1. **Abre** el explorador de archivos de tu computadora
2. **Selecciona** todos los archivos de la raíz de `achiads-website/`:
   - index.html
   - .htaccess
   - robots.txt
   - sitemap.xml
   - favicon.svg
   - 404.html
   - 500.html
   - contact_form.php
   - manifest.json
3. **Arrastra** todos estos archivos a la ventana del Administrador de Archivos de Hostinger
4. **Espera** a que se complete la subida (barra de progreso)

#### Método B: Botón de subida
1. **Clic en:** "Subir archivos" o "Upload"
2. **Selecciona** todos los archivos de la raíz
3. **Clic en:** "Abrir" o "Upload"
4. **Espera** a que se complete la subida

### 3.4 Crear y subir la carpeta assets

1. **En el Administrador de Archivos, clic en:** "Nueva carpeta" o "New Folder"
2. **Nombra la carpeta:** `assets`
3. **Entra** a la carpeta assets (doble clic)
4. **Crea** las subcarpetas:
   - Carpeta: `css`
   - Carpeta: `js`
   - Carpeta: `images`

5. **Entra a la carpeta `css`:**
   - **Sube** el archivo `styles.css`

6. **Vuelve a assets y entra a la carpeta `js`:**
   - **Sube** el archivo `main.js`

---

## ⚙️ PASO 4: Configurar el dominio y SSL

### 4.1 Verificar configuración del dominio
1. **En el panel de Hostinger, ve a:** "Dominios"
2. **Busca tu dominio** (ej: achiads.cl)
3. **Verifica que aparezca:** "Activo" o "Active"

### 4.2 Activar SSL (HTTPS)
1. **En "Dominios", busca:** tu dominio
2. **Clic en:** "Administrar"
3. **Busca la sección:** "SSL"
4. **Si no está activado:**
   - **Clic en:** "Activar SSL gratuito"
   - **Espera** 10-15 minutos para que se active

---

## 🧪 PASO 5: Probar tu sitio web

### 5.1 Acceder a tu sitio
1. **Abre una nueva pestaña** en tu navegador
2. **Ve a:** `https://tu-dominio.com` (reemplaza con tu dominio real)
3. **Deberías ver:** la página principal de ACHIADS

### 5.2 Probar páginas importantes
- ✅ **Página principal:** `https://tu-dominio.com`
- ✅ **Error 404:** `https://tu-dominio.com/pagina-inexistente`
- ✅ **Navegación:** Clic en los links del menú
- ✅ **Responsive:** Redimensiona la ventana o usa móvil

---

## 📧 PASO 6: Configurar el formulario de contacto

### 6.1 Verificar PHP
1. **En el panel de Hostinger, ve a:** "Configuración avanzada"
2. **Busca:** "Versión PHP"
3. **Asegúrate** de que sea PHP 7.4 o superior

### 6.2 Editar configuración del formulario
1. **En el Administrador de Archivos, abre:** `contact_form.php`
2. **Busca estas líneas:**
   ```php
   'email_to' => 'info@achiads.cl',
   'email_from' => 'noreply@achiads.cl',
   ```
3. **Cambia** los emails por los tuyos:
   ```php
   'email_to' => 'tu-email@tu-dominio.cl',
   'email_from' => 'noreply@tu-dominio.cl',
   ```
4. **Guarda** el archivo

### 6.3 Crear carpeta de logs
1. **En `public_html`, crea** una nueva carpeta llamada: `logs`
2. **Clic derecho** en la carpeta `logs`
3. **Selecciona:** "Permisos" o "Permissions"
4. **Establece permisos:** 755

---

## 📊 PASO 7: Configurar Google Analytics (Opcional)

### 7.1 Crear cuenta de Google Analytics
1. **Ve a:** [analytics.google.com](https://analytics.google.com)
2. **Inicia sesión** con tu cuenta Google
3. **Clic en:** "Empezar a medir"
4. **Sigue** el asistente para crear una propiedad
5. **Copia** tu ID de medición (ej: G-XXXXXXXXXX)

### 7.2 Agregar Analytics al sitio
1. **En el Administrador de Archivos, abre:** `index.html`
2. **Busca:** `GA_MEASUREMENT_ID`
3. **Reemplaza** con tu ID real:
   ```html
   gtag('config', 'G-TU-ID-REAL-AQUI');
   ```
4. **Guarda** el archivo

---

## 🎨 PASO 8: Personalización básica

### 8.1 Cambiar información del equipo
1. **Abre:** `index.html`
2. **Busca la sección:** "Nuestro Equipo"
3. **Reemplaza:**
   - Nombres por los reales
   - Roles por los reales
   - Iniciales en los avatares

### 8.2 Actualizar datos de contacto
1. **En `index.html`, busca el footer**
2. **Actualiza:**
   - Email: `info@tu-dominio.cl`
   - Teléfono: tu número real
   - Dirección: tu ubicación real

### 8.3 Agregar tus imágenes (futuro)
1. **Prepara imágenes** en formato WebP o JPG
2. **Sube** a la carpeta `assets/images/`
3. **Actualiza** las referencias en `index.html`

---

## ✅ CHECKLIST FINAL

Antes de anunciar tu sitio, verifica:

### Funcionalidad básica
- [ ] El sitio carga correctamente
- [ ] Todos los links del menú funcionan
- [ ] El sitio se ve bien en móvil
- [ ] Las páginas de error (404/500) funcionan

### Configuración
- [ ] SSL (HTTPS) está activado
- [ ] Información de contacto actualizada
- [ ] Formulario de contacto configurado
- [ ] Google Analytics configurado (opcional)

### Contenido
- [ ] Información del equipo actualizada
- [ ] Proyectos reflejan tu organización
- [ ] Links de redes sociales actualizados
- [ ] Email de contacto es real y funciona

---

## 🆘 Solución de Problemas Comunes

### ❌ "El sitio muestra una página en blanco"
**Solución:**
1. Verifica que `index.html` esté en la raíz de `public_html`
2. Verifica que el archivo no esté corrupto
3. Revisa los permisos del archivo (debe ser 644)

### ❌ "CSS no se carga (sitio sin estilos)"
**Solución:**
1. Verifica que `assets/css/styles.css` exista
2. Revisa la estructura de carpetas
3. Verifica permisos de la carpeta assets (755)

### ❌ "Formulario no envía emails"
**Solución:**
1. Verifica que PHP esté habilitado
2. Revisa la configuración de email en `contact_form.php`
3. Verifica que la carpeta `logs` exista con permisos 755
4. Contacta soporte de Hostinger para configuración de mail

### ❌ "Error 500 en todo el sitio"
**Solución:**
1. Revisa el archivo `.htaccess` - puede tener configuraciones incompatibles
2. Temporalmente renombra `.htaccess` a `.htaccess-backup` y prueba
3. Contacta soporte de Hostinger

---

## 📞 Obtener Ayuda

### Soporte de Hostinger
- **Chat en vivo:** disponible 24/7 en tu panel
- **Base de conocimientos:** [help.hostinger.com](https://help.hostinger.com)
- **Tickets:** desde tu panel de control

### Herramientas útiles
- **Probar velocidad:** [pagespeed.web.dev](https://pagespeed.web.dev)
- **Validar HTML:** [validator.w3.org](https://validator.w3.org)
- **Probar SSL:** [ssllabs.com/ssltest](https://www.ssllabs.com/ssltest/)

---

## 🎉 ¡Felicitaciones!

Tu sitio web de ACHIADS ya está en línea. 

**Próximos pasos sugeridos:**
1. 📱 Compartir en redes sociales
2. 📧 Enviar a contactos clave
3. 🔍 Registrar en Google Search Console
4. 📊 Monitorear analytics semanalmente
5. 🔄 Actualizar contenido regularmente

**¡Tu sitio web está listo para impulsar la misión de ACHIADS! 🚀**