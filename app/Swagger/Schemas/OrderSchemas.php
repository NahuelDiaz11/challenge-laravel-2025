<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Order",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "client_name", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "status", type: "string", enum: ["initiated", "sent", "delivered"], example: "initiated"),
        new OA\Property(property: "total", type: "number", format: "float", example: 46.00),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]

#[OA\Schema(
    schema: "OrderItem",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "order_id", type: "integer", example: 1),
        new OA\Property(property: "description", type: "string", example: "Producto A"),
        new OA\Property(property: "quantity", type: "integer", example: 2),
        new OA\Property(property: "unit_price", type: "number", format: "float", example: 10.50),
        new OA\Property(property: "line_total", type: "number", format: "float", example: 21.00),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]

#[OA\Schema(
    schema: "OrderLog",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "order_id", type: "integer", example: 1),
        new OA\Property(property: "from_status", type: "string", example: "initiated"),
        new OA\Property(property: "to_status", type: "string", example: "sent"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]

#[OA\Schema(
    schema: "OrderWithRelations",
    type: "object",
    allOf: [
        new OA\Schema(ref: "#/components/schemas/Order"),
        new OA\Schema(
            type: "object",
            properties: [
                new OA\Property(
                    property: "items",
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/OrderItem")
                ),
                new OA\Property(
                    property: "logs",
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/OrderLog")
                )
            ]
        )
    ]
)]
class OrderSchemas {}
