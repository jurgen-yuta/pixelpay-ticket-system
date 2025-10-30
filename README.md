
# Prueba Técnica PixelPay - Sistema de Gestión de Tickets (PHP/Laravel)

Este proyecto implementa un sistema de gestión de tickets utilizando Laravel, centrado en el desarrollo de la API y el aseguramiento de calidad (QA), cumpliendo con las tareas de la prueba técnica.

## Entidades Principales

El sistema permite registrar usuarios y tickets asociados a cada usuario. Las entidades principales son:

  * **User**: `id`, `name`, `email`.
  * **Ticket**: `id`, `user_id`, `title`, `status`.

-----

## Cómo Ejecutar el Proyecto

Este proyecto requiere PHP 8.1 o superior y Laravel 12.

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/jurgen-yuta/pixelpay-ticket-system.git
    cd pixelpay-ticket-system
    ```
2.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```
3.  **Configuración de Entorno:**
      * Renombra `env.example` a `.env`.
      * Crea la base de datos `pixelpay_tickets` en tu servidor MySQL.
      * Modifica el archivo **`.env`** con tus credenciales de base de datos.
4.  **Migraciones y Seeding (Poblar la BD):**
    ```bash
    php artisan migrate:fresh --seed
    ```
      * *Resultado:* Este comando ejecuta las migraciones y genera 5 usuarios y 50 tickets de ejemplo, esenciales para las pruebas.

-----

## Endpoints de API Implementados

El API se encuentra definida en `routes/api.php` y cumple con las tareas de desarrollo (Parte 1).

| Método | Ruta | Función |
| :--- | :--- | :--- |
| **POST** | `/api/tickets` | Crea un nuevo ticket. |
| **GET** | `/api/tickets/{id}` | Retorna el ticket con los datos del usuario asociado. |

### Requerimientos de Creación (`POST /api/tickets`):

  * `title` es requerido.
  * `user_id` debe existir.
  * `status` se asigna por defecto como `'open'`.

-----

## Aseguramiento de Calidad (QA)

Las pruebas funcionales (Feature Tests) se encuentran en `tests/Feature/TicketTest.php` y validan el correcto funcionamiento del sistema.

### Cómo Correr las Pruebas

Para ejecutar la *suite* de pruebas de QA que valida la creación y las validaciones:

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

1.  **Código Fuente Completo** (sin la carpeta `vendor`).
2.  **Migraciones necesarias** (equivalente al archivo `.sql`).
3.  **Este archivo `README.md`** documentando la solución.
4.  **Colección de Postman exportada** (`PixelPay_Tickets_API.postman_collection.json`) con pruebas de los endpoints.

