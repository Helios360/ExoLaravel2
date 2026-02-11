<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        title: "Library API",
        version: "1.0.0",
        description: "API d'authentification et gestion de livres (Laravel + Sanctum)."
    ),
    servers: [
        new OA\Server(url: "http://localhost:8000/api/v1", description: "Local"),
    ]
)]/*
#[OA\Tag(name: "Auth", description: "Register / Login / Logout")]
#[OA\Tag(name: "Books", description: "CRUD Books")]
*/
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Token",
    description: "Sanctum token. Example: Authorization: Bearer <token>"
)]

#[OA\Schema(
    schema: "User",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Abdelkader"),
        new OA\Property(property: "email", type: "string", example: "abdelkader@example.com"),
        new OA\Property(property: "created_at", type: "string", nullable: true, example: "2026-02-11T10:00:00.000000Z"),
        new OA\Property(property: "updated_at", type: "string", nullable: true, example: "2026-02-11T10:00:00.000000Z"),
    ]
)]
#[OA\Schema(
    schema: "Book",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 11),
        new OA\Property(property: "title", type: "string", example: "Clean Code"),
        new OA\Property(property: "author", type: "string", example: "Robert C. Martin"),
        new OA\Property(property: "summary", type: "string", example: "A handbook of agile software craftsmanship."),
        new OA\Property(property: "isbn", type: "string", example: "9780132350884"),
    ]
)]
#[OA\Schema(
    schema: "ValidationError",
    type: "object",
    properties: [
        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
        new OA\Property(
            property: "errors",
            type: "object",
            example: ["email" => ["The email field is required."]]
        ),
    ]
)]
#[OA\Schema(
    schema: "UnauthenticatedError",
    type: "object",
    properties: [
        new OA\Property(property: "message", type: "string", example: "Unauthenticated.")
    ]
)]
#[OA\Schema(
    schema: "NotFoundError",
    type: "object",
    properties: [
        new OA\Property(property: "message", type: "string", example: "No query results for model [App\\Models\\Book] 999.")
    ]
)]
class OpenApiSpec {}
