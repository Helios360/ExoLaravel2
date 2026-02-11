<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\BookResource;
use App\Http\Controllers\Controller;
use App\Models\Book;
use OpenApi\Attributes as OA;

class BookController extends Controller
{
    #[OA\Get(
        path: "/api/books",
        summary: "Lister les livres",
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function index(){
        return BookResource::collection(Book::query()->latest()->paginate(2));
    }
    #[OA\Post(
        path: "/api/books",
        summary: "Créer un livre",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    "title" => "Clean Code",
                    "author" => "Robert C. Martin",
                    "summary" => "A handbook of agile software craftsmanship.",
                    "isbn" => "9780132350884"
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Créé"),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request){
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'author' => ['required', 'string', 'min:3', 'max:100'],
            'summary' => ['required', 'string', 'min:10', 'max:500'],
            'isbn' => ['required', 'string', 'size:13', 'unique:books,isbn'],
        ]);
        $book = Book::create($validated);
        return new BookResource($book);
    }
    #[OA\Get(
        path: "/api/books/{book}",
        summary: "Détail d’un livre",
        parameters: [
            new OA\Parameter(name: "book", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK"),
            new OA\Response(response: 404, description: "Non trouvé")
        ]
    )]
    public function show(Book $book){
        $cacheKey = "book:{$book->id}";
        $data = Cache::remember($cacheKey, 60*60, function () use ($book) {
            return Book::query()->select(['id', 'title', 'author', 'summary', 'isbn'])->findOrFail($book->id);
        });
        
        return new BookResource($data);
    }
    #[OA\Put(
        path: "/api/books/{book}",
        summary: "Mettre à jour un livre",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "book", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    "title" => "Clean Architecture",
                    "author" => "Robert C. Martin",
                    "summary" => "Updated guide.",
                    "isbn" => "9780134494166"
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "OK"),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Non trouvé"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function update(Request $request, Book $book){
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'author' => ['required', 'string', 'min:3', 'max:100'],
            'summary' => ['required', 'string', 'min:10', 'max:500'],
            'isbn' => ['required', 'string', 'size:13', Rule::unique('books', 'isbn')->ignore($book->id)],
        ]);
        $book->update($validated);
        Cache::forget("book:{$book->id}");
        return new BookResource($book);
    }
    #[OA\Delete(
        path: "/api/books/{book}",
        summary: "Supprimer un livre",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "book", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 204, description: "Supprimé"),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Non trouvé")
        ]
    )]
    public function destroy(Book $book){
        Cache::forget("book:{$book->id}");
        $book->delete();
        Cache::forget("book:{$book->id}");
        return response()->noContent();
    }
}
