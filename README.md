# Hotel Luna Azul - Sistema de Gestión de Huéspedes y Reservas

Este es un sistema web completo diseñado para automatizar y gestionar el flujo de huéspedes, habitaciones y reservas del **Hotel Luna Azul**, eliminando el registro manual y previniendo problemas como la doble asignación de habitaciones o la pérdida de información.

El sistema está desarrollado desde cero aplicando el patrón de arquitectura **MVC (Modelo-Vista-Controlador)** en **PHP nativo**, con base de datos **MySQL**, una interfaz de usuario minimalista con diseño **Matte Dark (Vanilla CSS3)**, validaciones asíncronas mediante **JavaScript / AJAX**, y un motor de analítica de datos en **Python 3**.

---

## Tecnologías Utilizadas

1. **Back-End**: PHP (arquitectura orientada a objetos bajo el patrón MVC, PDO para base de datos y sesiones nativas).
2. **Front-End**: HTML5 semántico, CSS3 personalizado (diseño responsivo con enfoque mate oscuro, sin dependencias externas) y JavaScript (ES6+ para validaciones, AJAX y diálogos interactivos).
3. **Base de Datos**: MySQL / MariaDB (modelo relacional, integridad referencial y disparadores cascade).
4. **Analítica**: Python 3 (análisis demográfico y de reservas mediante parseo de flujos JSON).

---

## Estructura del Proyecto (MVC)

La arquitectura está distribuida de forma limpia e intuitiva en las siguientes carpetas:

```
ACA Hotel luna azul/
├── config/
│   └── database.php           # Conexión PDO Singleton a la Base de Datos
├── controllers/
│   ├── AuthController.php     # Control de sesiones, inicios de sesión y registros
│   ├── GuestController.php    # Control de flujos de Huéspedes (CRUD y AJAX)
│   ├── ReservationController.php # Control de Reservas y disponibilidad AJAX
│   └── DashboardController.php # Compilador de estadísticas y ejecutor de Python
├── dtos/
│   ├── GuestDTO.php           # Objeto de transferencia de datos de huéspedes (sin ID expuesto)
│   └── ReservationDTO.php     # Objeto de transferencia de datos de reservas (sin ID expuesto)
├── models/
│   ├── User.php               # Operaciones SQL del administrador
│   ├── Guest.php              # Operaciones SQL de huéspedes
│   └── Reservation.php        # Lógica SQL de reservas y prevención de sobreasignaciones
├── views/
│   ├── layouts/               # Plantillas del layout (header con menú lateral y footer)
│   ├── auth/                  # Pantallas de Login y Registro de usuarios
│   ├── guests/                # Formularios y listados de Gestión de Huéspedes
│   ├── reservations/          # Formularios y listados de Control de Reservas
│   └── dashboard.php          # Panel general con widgets y consola de reportes Python
├── public/
│   ├── css/
│   │   └── style.css          # Estilos globales "Matte Dark" (sin neones / estilo premium)
│   ├── js/
│   │   └── main.js            # Validaciones, modales nativos y peticiones asíncronas AJAX
│   ├── index.php              # Enrutador Front Controller (único punto de entrada)
│   └── setup_admin.php        # Script de autosembrado y diagnósticos de base de datos
├── scripts/
│   └── report.py              # Script en Python 3 para análisis estadístico
└── database.sql               # Esquema de tablas, restricciones y datos de semilla
```

---

## Características Principales

### 1. Seguridad y Arquitectura DTO (Data Transfer Object)
* **Protección contra IDOR / Enumeración**: La aplicación **no expone los IDs numéricos autoincrementables** de la base de datos en las URLs ni en los formularios. En su lugar, el sistema genera y utiliza **UUIDs** (identificadores únicos aleatorios de 36 caracteres) mapeados mediante DTOs para referenciar tanto huéspedes como reservas de forma pública.
* **Control de Sesiones**: Las páginas internas están protegidas; si un usuario no autenticado intenta acceder a ellas, es redirigido automáticamente a la pantalla de login. Las contraseñas en base de datos se guardan encriptadas con el algoritmo seguro `BCRYPT`.

### 2. Módulo de Huéspedes (CRUD Completo)
* Almacenamiento de todos los campos solicitados: Nombre completo, tipo y número de documento, dirección, teléfonos, edad, correo electrónico, contacto de emergencia y parentesco.
* Filtro de búsqueda en tiempo real mediante JavaScript en la tabla de listado.

### 3. Módulo de Reservas e Impedimento de Dobles Asignaciones
* **Asignación por Habitación Física**: Permite reservar habitaciones por número físico específico.
* **Validación de Fechas**: Se impide que el check-in sea anterior al día de hoy, o que el check-out sea anterior o igual al check-in.
* **Control de Salidas y Entradas**: Los check-outs son a las 10:00 AM y los check-ins a las 12:00 PM. El sistema permite registrar una nueva entrada el mismo día en que sale otra reserva en la misma habitación sin causar conflicto de disponibilidad.
* **Prevención de Doble Reserva (Algoritmo de Solapamiento)**: Al elegir fechas, el backend valida mediante intervalos excluyentes si la habitación está reservada.
* **Carga Dinámica Asíncrona (AJAX)**: Al elegir las fechas de ingreso y salida, el selector de habitaciones se actualiza automáticamente trayendo **únicamente las habitaciones libres** para ese rango de fechas.

### 4. Reglas de Capacidad Máxima
Para evitar la sobreocupación, el sistema valida (en JS en tiempo real y en PHP al guardar) la capacidad permitida por habitación:
* **Sencilla**: Máximo 1 persona.
* **Doble**: Máximo 2 personas.
* **Suite**: Máximo 4 personas.
* **Familiar**: Máximo 6 personas.

### 5. Registro Rápido con `<dialog>` de HTML5
Si al momento de crear una reserva el huésped no está registrado en el sistema, el usuario puede abrir el modal interactivo nativo (usando la etiqueta `<dialog>` de HTML5, compatible con accesibilidad). El modal registra al huésped por AJAX y lo selecciona automáticamente en el formulario de reserva sin recargar la página.

### 6. Reporte Analítico con Python 3
En el dashboard hay una sección dedicada a la analítica de datos. Al presionar **Generar Reporte Analítico**:
1. PHP exporta de forma segura los datos de reservas activos a un archivo temporal JSON.
2. PHP ejecuta en segundo plano el script `report.py` en Python.
3. El script de Python procesa los datos calculando la duración promedio de estadías, la distribución demográfica de huéspedes por rangos de edad y la demanda de tipos de habitación.
4. Python retorna un reporte en formato de texto plano estructurado que se despliega inmediatamente en una consola interactiva en el Dashboard.

---

## Instrucciones de Instalación y Despliegue

### Requisitos Previos:
* Servidor web con PHP 7.4 o superior (con extensión `PDO` y `pdo_mysql` activas).
* Servidor de Base de Datos MySQL o MariaDB.
* Python 3 instalado.

### Paso 1: Configurar la Base de Datos
Importe las tablas y los datos de prueba iniciales en su servidor MySQL:
```bash
mysql -u root -p < database.sql
```

### Paso 2: Configurar las Credenciales
Abra el archivo `config/database.php` y configure el host, usuario y contraseña de su servidor MySQL local. Ejemplo:
```php
private $host = '127.0.0.1';
private $db_name = 'hotel_luna_azul';
private $username = 'root';
private $password = 'LunaAzul2026#'; // Ingrese la contraseña de su base de datos aquí
```

### Paso 3: Iniciar el Servidor PHP
Ejecute el servidor de desarrollo integrado en PHP apuntando a la carpeta pública (`public`):
```bash
php -S localhost:8000 -t public
```

### Paso 4: Validar y Autoconfigurar
Abra su navegador y acceda a la herramienta de diagnóstico:
👉 **`http://localhost:8000/setup_admin.php`**

*Este script validará si PHP se conecta exitosamente a la base de datos y restaurará/creará automáticamente el usuario administrador.*

---

## 🔑 Credenciales de Acceso por Defecto
* **Usuario**: `admin`
* **Contraseña**: `admin123`

*(También puede usar la opción **"Regístrese aquí"** en la pantalla de acceso para crear nuevas cuentas).*
