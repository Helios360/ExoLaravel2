<?php

namespace App\OpenApi;
use OpenApi\Attributes as OA;

#[OA\OpenApi]
#[OA\Info(
    title:"Exolaravel API",
    version:"1.0.0",
    description:"Doc"
)]
class OpenApiSpec {}
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Token"
)]
class OpenApiSecurity {}