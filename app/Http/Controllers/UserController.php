<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    #[OA\Post(
        path: "/api/register",
        summary: "Créer un compte",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    "name" => "John",
                    "email" => "john@mail.com",
                    "password" => "password123"
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Créé"),
            new OA\Response(response: 422, description: "Validation error")
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
        summary: "Connexion",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    "email" => "john@mail.com",
                    "password" => "password123"
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "OK"),
            new OA\Response(response: 422, description: "Credentials invalides")
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
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }
}
