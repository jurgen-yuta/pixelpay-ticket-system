
# Prueba Técnica PixelPay - Sistema de Gestión de Tickets (Laravel + PHP + Vue.js)

Este proyecto implementa un sistema completo de gestión de tickets utilizando **Laravel** para el backend API y **Vue.js** para el frontend. El sistema fue desarrollado en dos fases: desarrollo de la API y QA, seguido por la implementación de la interfaz gráfica y la automatización de la instalación, cumpliendo con todos los requerimientos solicitados.

## Entidades Principales

El sistema permite registrar usuarios y tickets asociados a cada usuario. Las entidades principales son:

  * **User**: `id`, `name`, `email`.
  * **Ticket**: `id`, `user_id`, `title`, `description`, `status`.

-----

## 🚀 Objetivo Final Cumplido

El sistema está diseñado para una instalación y un despliegue totalmente automáticos.

**Al correr el comando `php artisan start`, el sistema se instala completamente y muestra en el navegador la interfaz para manejar los tickets.**

## 🛠 Cómo Ejecutar el Proyecto (Instalación Automática)

La aplicación ahora utiliza un comando personalizado que automatiza toda la configuración, incluyendo la base de datos SQLite y el inicio del servidor.

### **Requisitos**

* PHP 8.1 o superior.
* Composer.
* Node.js (v16+) y NPM o Yarn (para las dependencias de Vue.js).

### **Pasos para Instalar y Arrancar**

1.  **Clonar el repositorio:**
    ```bash
    git clone [https://github.com/jurgen-yuta/pixelpay-ticket-system.git](https://github.com/jurgen-yuta/pixelpay-ticket-system.git)
    cd pixelpay-ticket-system
    ```
2.  **Instalar todas las dependencias (PHP y Frontend):**
    Este paso es crucial para descargar Laravel, Vue.js y sus dependencias.
    ```bash
    composer install
    npm install
    npm run dev # Compila los assets de Vue.js para el servidor de desarrollo
    ```
3.  **Inicio Automático (Comando `php artisan start`):**
    ```bash
    php artisan start
    ```

> 💡 **Detalles del Comando `php artisan start`:**
> El comando realiza automáticamente la siguiente secuencia de pasos para dejar la aplicación lista:
> 1. Crea el archivo **`.env`** si no existe, configurado con base de datos **SQLite**.
> 2. Genera el archivo físico `database/database.sqlite`.
> 3. Ejecuta `php artisan key:generate`.
> 4. Ejecuta las migraciones y los *seeders* (`php artisan migrate:fresh --seed`), generando **5 usuarios y 50 tickets de prueba**.
> 5. Finalmente, inicia el servidor de desarrollo (`php artisan serve`) para que la aplicación abra la interfaz lista para usar en `http://127.0.0.1:8000/dashboard`.

-----

## ✨ Funcionalidad Implementada

### Interfaz Gráfica con Vue.js

La interfaz está hecha con Vue.js, integrada en Laravel y es la vista inicial que se muestra al ejecutar el servidor.

* **Crear Ticket:** Se puede crear un nuevo ticket con título y descripción.
* **Listar Tickets:** Muestra todos los tickets creados en una lista.
* **Actualizar Estado:** Permite cambiar el estado de un ticket entre: `open` → `in progress` → `closed`.

### Comando Artisan Personalizado

* **Comando:** `php artisan start`
* **Función:** Automatización completa del *setup* del entorno de desarrollo.

-----

## Endpoints de API Implementados

El API es consumida por el frontend de Vue.js y cumple con los requerimientos iniciales.

| Método | Ruta | Función |
| :--- | :--- | :--- |
| **GET** | `/api/tickets` | Retorna la lista de todos los tickets. |
| **POST** | `/api/tickets` | Crea un nuevo ticket. |
| **GET** | `/api/tickets/{id}` | Retorna el ticket con los datos del usuario asociado. |
| **PUT/PATCH** | `/api/tickets/{id}` | Actualiza el estado de un ticket. |

### Requerimientos de Creación (`POST /api/tickets`):

  * `title` y `description` son requeridos.
  * `user_id` debe existir.
  * `status` se asigna por defecto como `'open'`.

-----

## Aseguramiento de Calidad (QA)

Las pruebas funcionales (Feature Tests) se encuentran en `tests/Feature/TicketTest.php` y validan el correcto funcionamiento de los endpoints de la API.

### Cómo Correr las Pruebas

Para ejecutar la *suite* de pruebas de QA:

```bash
php artisan test --filter TicketTest
```

| Prueba | Tipo | Cumplimiento |
| :--- | :--- | :--- |
| `test_can_create_ticket_successfully` | Positiva | Valida código `201/200` y persistencia en la BD. |
| `test_creation_fails_without_required_fields` | Negativa | Valida errores de validación (`422`) al enviar un título vacío o falta de `user_id`. |
| `test_creation_fails_with_non_existent_user_id` | Negativa | Valida el error controlado (`422`) si el `user_id` no existe. |

### Breve Descripción del Bug Encontrado y su Corrección

  * **Bug**: Al intentar ejecutar `php artisan migrate:fresh --seed`, el sistema arrojaba `BadMethodCallException: Call to undefined method App\Models\User::tickets()`.
  * **Causa**: El Factory/Seeder requería una relación para crear `Tickets` asociados, pero la función `tickets()` no estaba definida en el modelo `User.php`.
  * **Corrección**: Se añadió la función de relación **`hasMany(Ticket::class)`** al modelo `App\Models\User`.

-----

## Entregables Finales

El proyecto es entregado a través del repositorio Git público e incluye todos los archivos y la documentación requerida:

*   **Código Fuente Completo** (sin la carpeta `vendor`).
*   **Migraciones necesarias** (equivalente al archivo `.sql`).
*   **Este archivo `README.md`** documentando la solución.
*   **Colección de Postman exportada** (`PixelPay_Tickets_API.postman_collection.json`) con pruebas de los endpoints.


