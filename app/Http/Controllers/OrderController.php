<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Pedidos", description: "Operaciones relacionadas con pedidos")]
class OrderController extends Controller
{
    private OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

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
    public function index()
    {
        $orders = $this->service->listActive();
        return response()->json(['data' => $orders], Response::HTTP_OK);
    }

    #[OA\Post(
        path: "/orders",
        summary: "Crear un nuevo pedido",
        tags: ["Pedidos"],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Datos para crear un pedido",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "client_name",
                        type: "string",
                        example: "Juan Pérez",
                        description: "Nombre del cliente"
                    ),
                    new OA\Property(
                        property: "items",
                        type: "array",
                        description: "Lista de items del pedido",
                        items: new OA\Items(
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "description",
                                    type: "string",
                                    example: "Producto A",
                                    description: "Descripción del item"
                                ),
                                new OA\Property(
                                    property: "quantity",
                                    type: "integer",
                                    example: 2,
                                    description: "Cantidad del item"
                                ),
                                new OA\Property(
                                    property: "unit_price",
                                    type: "number",
                                    format: "float",
                                    example: 10.50,
                                    description: "Precio unitario del item"
                                )
                            ],
                            required: ["description", "quantity", "unit_price"]
                        )
                    ),
                ],
                required: ["client_name", "items"],
                example: [
                    "client_name" => "Juan Pérez",
                    "items" => [
                        [
                            "description" => "Producto A",
                            "quantity" => 2,
                            "unit_price" => 10.50
                        ],
                        [
                            "description" => "Producto B",
                            "quantity" => 1,
                            "unit_price" => 25.00
                        ]
                    ]
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Pedido creado exitosamente",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "data",
                            ref: "#/components/schemas/Order"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validación fallida",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "The given data was invalid."
                        ),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "client_name",
                                    type: "array",
                                    items: new OA\Items(type: "string")
                                ),
                                new OA\Property(
                                    property: "items",
                                    type: "array",
                                    items: new OA\Items(type: "string")
                                )
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function store(StoreOrderRequest $request)
    {

        $validated = $request->validated();
        $order = $this->service->createOrder($validated['client_name'], $validated['items']);
        return response()->json(['data' => $order], Response::HTTP_CREATED);
    }

    #[OA\Post(
        path: "/orders/{id}/advance",
        summary: "Avanzar estado de una orden",
        tags: ["Pedidos"],
        description: "Transición: initiated → sent → delivered. 
                  Si llega a delivered, la orden se elimina de la base de datos y del caché.",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la orden",
                schema: new OA\Schema(type: "integer", format: "int64"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Orden actualizada correctamente o eliminada si fue entregada",
                content: [
                    new OA\MediaType(
                        mediaType: "application/json",
                        schema: new OA\Schema(
                            oneOf: [
                                new OA\Schema(
                                    type: "object",
                                    properties: [
                                        new OA\Property(
                                            property: "message",
                                            type: "string",
                                            example: "Order delivered and removed."
                                        )
                                    ]
                                ),
                                new OA\Schema(
                                    type: "object",
                                    properties: [
                                        new OA\Property(
                                            property: "data",
                                            ref: "#/components/schemas/Order"
                                        )
                                    ]
                                )
                            ]
                        )
                    )
                ]
            ),
            new OA\Response(
                response: 400,
                description: "Error de transición de estado",
                content: [
                    new OA\MediaType(
                        mediaType: "application/json",
                        schema: new OA\Schema(
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "error",
                                    type: "string",
                                    example: "Cannot advance from status delivered"
                                )
                            ]
                        )
                    )
                ]
            ),
            new OA\Response(
                response: 404,
                description: "Orden no encontrada",
                content: [
                    new OA\MediaType(
                        mediaType: "application/json",
                        schema: new OA\Schema(
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "error",
                                    type: "string",
                                    example: "Order not found"
                                )
                            ]
                        )
                    )
                ]
            )
        ]
    )]
    public function advance($id)
    {
        try {
            $result = $this->service->advanceStatus((int)$id);
            if ($result === true) {
                return response()->json(['message' => 'Order delivered and removed.'], Response::HTTP_OK);
            }
            return response()->json(['data' => $result], Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[OA\Get(
        path: "/orders/{id}",
        summary: "Ver detalle de una orden",
        tags: ["Pedidos"],
        description: "Muestra datos completos incluyendo items, totales y estado actual.",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la orden",
                schema: new OA\Schema(type: "integer", format: "int64"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle de la orden con items y logs",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "data",
                            ref: "#/components/schemas/OrderWithRelations"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Orden no encontrada",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "error",
                            type: "string",
                            example: "Order not found"
                        )
                    ]
                )
            )
        ]
    )]
    public function show($id)
    {
        $order = $this->service->getById((int)$id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $order->load('items', 'logs')], Response::HTTP_OK);
    }
}
