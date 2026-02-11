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
        path: "/books",
        tags: ["Books"],
        summary: "Index (liste paginée)",
        parameters: [
            new OA\Parameter(name: "Accept", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "OK",
                content: new OA\JsonContent(
                    type: "object",
                    example: [
                        "data" => [
                            [
                                "id" => 11,
                                "title" => "Clean Code",
                                "author" => "Robert C. Martin",
                                "summary" => "A handbook of agile software craftsmanship.",
                                "isbn" => "9780132350884",
                                "_links" => [
                                    'self'=> 'http://localhost:8000/api/books/1',
                                    'update'=> 'http://localhost:8000/api/books/1',
                                    'delete'=> 'http://localhost:8000/api/books/1',
                                    'all'=> 'http://localhost:8000/api/books'
                                ],
                            ]
                        ],
                        "links" => ["first" => "...", "last" => "...", "prev" => null, "next" => "..."],
                        "meta" => ["current_page" => 1, "per_page" => 2, "total" => 10]
                    ]
                )
            )
        ]
    )]
    #[OA\Post(
        path: "/books",
        tags: ["Books"],
        summary: "Store (création)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "Accept", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "Content-Type", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "Authorization", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxx"),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    "title" => "Clean Architecture",
                    "author" => "Robert C. Martin",
                    "summary" => "A guide to software structure and design.",
                    "isbn" => "9780134494166",
                    "_links" => [
                                    'self'=> 'http://localhost:8000/api/books/1',
                                    'update'=> 'http://localhost:8000/api/books/1',
                                    'delete'=> 'http://localhost:8000/api/books/1',
                                    'all'=> 'http://localhost:8000/api/books'
                                ],
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "OK (créé)",
                content: new OA\JsonContent(
                    type: "object",
                    example: [
                        "data" => [
                            "id" => 12,
                            "title" => "Clean Architecture",
                            "author" => "Robert C. Martin",
                            "summary" => "A guide to software structure and design.",
                            "isbn" => "9780134494166",
                            "_links" => [
                                'self'=> 'http://localhost:8000/api/books/1',
                                'update'=> 'http://localhost:8000/api/books/1',
                                'delete'=> 'http://localhost:8000/api/books/1',
                                'all'=> 'http://localhost:8000/api/books'
                            ],
                        ]
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié", content: new OA\JsonContent(ref: "#/components/schemas/UnauthenticatedError")),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
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
        path: "/books/{book}",
        tags: ["Books"],
        summary: "Show (détail)",
        parameters: [
            new OA\Parameter(name: "Accept", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "book", in: "path", required: true, schema: new OA\Schema(type: "integer"), example: 12),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "OK",
                content: new OA\JsonContent(
                    type: "object",
                    example: [
                        "data" => [
                            "id" => 12,
                            "title" => "Clean Architecture",
                            "author" => "Robert C. Martin",
                            "summary" => "A guide to software structure and design.",
                            "isbn" => "9780134494166",
                            "_links" => [
                                'self'=> 'http://localhost:8000/api/books/1',
                                'update'=> 'http://localhost:8000/api/books/1',
                                'delete'=> 'http://localhost:8000/api/books/1',
                                'all'=> 'http://localhost:8000/api/books'
                            ],
                        ]
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Non trouvé", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundError")),
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
        path: "/books/{book}",
        tags: ["Books"],
        summary: "Update",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "Accept", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "Content-Type", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "Authorization", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxx"),
            new OA\Parameter(name: "book", in: "path", required: true, schema: new OA\Schema(type: "integer"), example: 12),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    "title" => "Clean Architecture (2nd Edition)",
                    "author" => "Robert C. Martin",
                    "summary" => "Updated guide.",
                    "isbn" => "9780134494166"
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "OK",
                content: new OA\JsonContent(
                    type: "object",
                    example: [
                        "data" => [
                            "id" => 12,
                            "title" => "Clean Architecture (2nd Edition)",
                            "author" => "Robert C. Martin",
                            "summary" => "Updated guide.",
                            "isbn" => "9780134494166",
                            "_links" => [
                                'self'=> 'http://localhost:8000/api/books/1',
                                'update'=> 'http://localhost:8000/api/books/1',
                                'delete'=> 'http://localhost:8000/api/books/1',
                                'all'=> 'http://localhost:8000/api/books'
                            ],
                        ]
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié", content: new OA\JsonContent(ref: "#/components/schemas/UnauthenticatedError")),
            new OA\Response(response: 404, description: "Non trouvé", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundError")),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
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
        path: "/books/{book}",
        tags: ["Books"],
        summary: "Destroy",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "Accept", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "application/json"),
            new OA\Parameter(name: "Authorization", in: "header", required: true, schema: new OA\Schema(type: "string"), example: "Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxx"),
            new OA\Parameter(name: "book", in: "path", required: true, schema: new OA\Schema(type: "integer"), example: 12),
        ],
        responses: [
            new OA\Response(response: 204, description: "No Content"),
            new OA\Response(response: 401, description: "Non authentifié", content: new OA\JsonContent(ref: "#/components/schemas/UnauthenticatedError")),
            new OA\Response(response: 404, description: "Non trouvé", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundError")),
        ]
    )]
    public function destroy(Book $book){
        Cache::forget("book:{$book->id}");
        $book->delete();
        Cache::forget("book:{$book->id}");
        return response()->noContent();
    }
}
