# Proyecto Laravel API - Pedidos

Esta API implementa un sistema de gestión de pedidos usando **Laravel 10**, **PostgreSQL**, **Redis** y **Docker**. La documentación se genera con **Swagger (l5-swagger)**.

---

## 🐳 Levantar el proyecto con Docker

### 1. Clonar el repositorio

```bash
git clone <URL_DEL_REPOSITORIO>
cd <NOMBRE_DEL_REPOSITORIO>
```

### 2. Copiar el archivo de entorno

```bash
cp .env.example .env
```
Configurar variables de entorno según tu sistema:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=restaurant_orders
DB_USERNAME=laravel_user
DB_PASSWORD=secret
```

### 3. Levantar los contenedores con Docker
```bash
docker-compose up -d --build
```
### 4. Instalar dependencias y preparar la aplicación

```bash
docker exec -it laravel_app composer install
docker exec -it laravel_app php artisan key:generate

docker exec -it laravel_app chmod -R 775 storage/
docker exec -it laravel_app chown -R www-data:www-data storage/
docker exec -it laravel_app chmod -R 775 bootstrap/cache/
docker exec -it laravel_app chown -R www-data:www-data bootstrap/cache/

docker exec -it laravel_app php artisan migrate

docker exec -it laravel_app php artisan db:seed --class=OrderSeeder
```

### 5. Generar la documentación con Swagger

```bash
docker exec -it laravel_app php artisan l5-swagger:generate
```
## 📚 Documentación de la API

La documentación completa de la API estará disponible en:

🔗 **http://localhost/api/documentation**

## 📖 Estilo de Documentación Implementado

La API utiliza **atributos PHP 8** para la documentación Swagger, que es el enfoque más moderno y mantenible:

```php
#[OA\Get(
    path: "/orders",
    summary: "Obtener todos los pedidos activos",
    tags: ["Pedidos"],
    description: "Devuelve la lista de pedidos que no han sido entregados (status: initiated o sent)",
    responses: [
        new OA\Response(
            response: 200,
            description: "Listado de pedidos activos",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "data",
                        type: "array",
                        items: new OA\Items(ref: "#/components/schemas/Order")
                    )
                ]
            )
        )
    ]
)]
```
Ventajas de este enfoque:
- Sintaxis moderna con atributos PHP 8
- Mejor legibilidad y mantenimiento
- Validación en tiempo de desarrollo

## Endpoints principales

| Método | Ruta                   | Descripción                     |
|--------|------------------------|---------------------------------|
| GET    | `/orders`              | Listar todos los pedidos activos |
| POST   | `/orders`              | Crear un nuevo pedido           |
| GET    | `/orders/{id}`         | Ver detalle de un pedido        |
| POST   | `/orders/{id}/advance` | Avanzar estado del pedido       |

## 🧪 Pruebas Unitarias

El proyecto incluye pruebas unitarias para garantizar el correcto funcionamiento:
```bash
docker exec -it laravel_app php artisan test tests/Unit/OrderTest.php

```

## 📝 Validaciones

- **`client_name`**: requerido, string, mínimo 1 carácter.
- **`items`**: requerido, array con al menos un elemento.
- **`items.*.description`**: requerido, string, mínimo 1 carácter.
- **`items.*.quantity`**: requerido, entero mayor a 0.
- **`items.*.unit_price`**: requerido, numérico, mayor o igual a 0.

Los errores devuelven JSON con código `422`:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "client_name": ["El nombre del cliente es obligatorio."],
    "items.0.description": ["La descripción del item es obligatoria."]
  }
}
```

## 📦 Tecnologías

- **PHP 8.2** + **Laravel 10**
- **PostgreSQL 13**
- **Redis 6**
- **Nginx**
- **Docker & Docker Compose**
- **Swagger / OpenAPI** (l5-swagger)

## 💡 Buenas prácticas implementadas

- Separación de controllers, requests y services.
- Validación centralizada con FormRequest y mensajes personalizados.
- Documentación OpenAPI generada automáticamente.
- Dockerizado para entornos consistentes.

##  Preguntas opcionales:

### 1. ¿Cómo asegurarías que esta API escale ante alta concurrencia?

- Usando Redis para caching de datos frecuentes y resultados de consultas pesadas.
- Balanceando carga con Nginx y múltiples contenedores app.
- Usando colas (Laravel Queues) para operaciones pesadas asincrónicas.
- Optimizando consultas y usando índices en PostgreSQL.

### 2. ¿Qué estrategia seguirías para desacoplar la lógica del dominio de Laravel/Eloquent?

- Implementando Services y Repositories, para separar la lógica de negocio de la persistencia.
- Usando DTOs (Data Transfer Objects) para comunicar controladores y servicios sin exponer directamente modelos de Eloquent.
- Aplicando Interfaces para los repositorios, permitiendo cambiar la implementación (PostgreSQL, MongoDB, API externa) sin tocar la lógica del dominio.

### 3. ¿Cómo manejarías versiones de la API en producción?

- Manteniendo rutas versionadas: `/api/v1/orders`, `/api/v2/orders`.
- Usando controladores por versión, por ejemplo `App\Http\Controllers\V1\OrderController`.
- Documentando cada versión en Swagger por separado.
- Manteniendo compatibilidad hacia atrás mientras se implementan nuevas funcionalidades.