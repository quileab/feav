# Facturador Pro - Sistema de Facturación Electrónica (AFIP)

Sistema maduro y profesional de facturación electrónica integrado con AFIP (ARCA), diseñado para la gestión eficiente de ventas, clientes y stock. Desarrollado sobre **Laravel 12**, **Filament v5**, **Livewire v4** y **Flux UI**.

## 🚀 Funcionalidades Principales

### 🧾 Facturación Electrónica (AFIP)
- **Emisión de Comprobantes**: Soporte completo para Facturas A, B y C.
- **Notas de Crédito y Débito**: Emisión simplificada con vinculación automática de comprobantes asociados (`CbtesAsoc`).
- **QR Interactivo**: Generación de códigos QR según especificaciones vigentes de AFIP para comprobantes electrónicos.
- **Descarga PDF**: Generación de facturas en PDF con diseño profesional, logotipos institucionales y desgloses impositivos precisos.
- **Estado de Servicio**: Monitor en tiempo real de la conexión con los servidores de AFIP y validez de certificados.

### 📦 Gestión de Productos y Stock
- **Búsqueda Avanzada**: Buscador inteligente por múltiples términos (palabras clave) y código de barras.
- **Control de Inventario**: Visualización de stock en tiempo real durante la facturación según el depósito seleccionado.
- **Precios Dinámicos**: Soporte para múltiples listas de precios (P1/P2) y cálculo automático basado en porcentaje de ganancia sobre costo.
- **Validación de Descuentos**: Sistema de topes máximos de descuento por producto para proteger los márgenes comerciales.

### 👥 Gestión de Clientes
- **Listado Inteligente**: Resalte visual para clientes Responsables Inscriptos (Factura A).
- **Acceso Rápido**: Botón directo de facturación desde la ficha del cliente con pre-selección automática de tipo de comprobante.

### 🛒 Carritos Aparcados (Drafts)
- **Persistencia en JSON**: Capacidad de guardar carritos de venta pendientes por cliente y fecha.
- **Gestión Global**: Panel centralizado para recuperar o eliminar carritos abandonados, facilitando el cierre de ventas pendientes.

## 🛠️ Requisitos Técnicos
- PHP 8.2 o superior.
- Base de datos SQLite (configurada por defecto).
- Certificados AFIP (.crt y .key) para el entorno correspondiente (homologación/producción).

## 📥 Instalación

1. Clonar el repositorio:
   ```bash
   git clone <URL_DEL_REPOSITORIO>
   ```
2. Instalar dependencias:
   ```bash
   composer install
   npm install
   npm run build
   ```
3. Configurar el archivo `.env` con tus credenciales y rutas de certificados.
4. Ejecutar migraciones:
   ```bash
   php artisan migrate --seed
   ```
5. Iniciar el servidor:
   ```bash
   php artisan serve
   ```

## 📜 Licencia
Este proyecto está bajo la licencia **GNU Affero General Public License v3 (AGPL-3.0)**. Consulta el archivo `LICENSE` para más detalles.

---
*Desarrollado con precisión para el cumplimiento fiscal argentino.*
