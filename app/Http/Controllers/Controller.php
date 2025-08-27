<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "API de Pedidos",
    description: "Documentación de la API de pedidos con Swagger y Laravel"
)]
#[OA\Server(
    url: "http://localhost/api",
    description: "Servidor local"
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
