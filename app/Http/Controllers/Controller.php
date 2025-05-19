<?php

namespace App\Http\Controllers;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *    title="Safa API's",
 *    version="1.0.0",
 *    description="Swagger with Laravel",
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *     @OA\Server(
 *         url="http://127.0.0.1:8000/api/v1/",
 *         description="Local Server"
 *     ),
 *     @OA\Server(
 *         url="https://api.dev.example.com/api/v1/",
 *         description="Development Server"
 *     ),
 *     @OA\Server(
 *         url="https://api.staging.example.com/api/v1/",
 *         description="Staging Server"
 *     ),
 *     @OA\Server(
 *         url="https://api.example.com/api/v1/",
 *         description="Production Server"
 *     )
 * )
 *  @OA\Tag(
 *         name="Authentication",
 *         description="Authentication related endpoints"
 *     ),
 *     @OA\Tag(
 *         name="User",
 *         description="User related endpoints"
 *     )
 * )

 */
abstract class Controller
{
    //
}
