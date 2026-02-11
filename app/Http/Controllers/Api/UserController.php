<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Post(
        path: "/api/register",
        tags: ["Auth"],
        summary: "Register",
        parameters: [
            new OA\Parameter(name: "Accept", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "Content-Type", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    "name" => "Abdelkader",
                    "email" => "abdelkader@example.com",
                    "password" => "Password123!"
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Créé",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Utilisateur créé avec succès"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User"),
                        new OA\Property(property: "token", type: "string", example: "1|xxxxxxxxxxxxxxxxxxxxxxxx")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
            )
        ]
    )]
    public function register(Request $request){
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
    #[OA\Post(
        path: "/api/login",
        tags: ["Auth"],
        summary: "Login",
        parameters: [
            new OA\Parameter(name: "Accept", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "Content-Type", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    "email" => "abdelkader@example.com",
                    "password" => "Password123!"
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "OK",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Connexion établie"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User"),
                        new OA\Property(property: "token", type: "string", example: "1|xxxxxxxxxxxxxxxxxxxxxxxx")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Identifiants invalides / validation error",
                content: new OA\JsonContent(
                    type: "object",
                    example: [
                        "message" => "The given data was invalid.",
                        "errors" => [
                            "email" => ["Identifiants incorects."]
                        ]
                    ]
                )
            )
        ]
    )]
    public function login(Request $request){
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $user=User::where('email', $validated['email'])->first();
        if(!$user || !Hash::check($validated['password'], $user->password)){
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorects.'],
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Connexion établie',
            'user' => $user,
            'token' => $token,
        ]);
    }
    #[OA\Post(
        path: "/api/logout",
        tags: ["Auth"],
        summary: "Logout",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "Accept", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "Authorization", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxx"),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "OK",
                content: new OA\JsonContent(
                    type: "object",
                    example: ["message" => "Déconnexion réussie."]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Non authentifié",
                content: new OA\JsonContent(ref: "#/components/schemas/UnauthenticatedError")
            )
        ]
    )]
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }
}
