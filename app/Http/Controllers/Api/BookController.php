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
        path: "/api/users",
        summary: "Liste des utilisateurs",
        responses: [
            new OA\Response(
            response: 200,
            description: "Succès"
        )]
    )]
    public function index(){
        return BookResource::collection(Book::query()->latest()->paginate(2));
    }
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
    public function show(Book $book){
        $cacheKey = "book:{$book->id}";
        $data = Cache::remember($cacheKey, 60*60, function () use ($book) {
            return Book::query()->select(['id', 'title', 'author', 'summary', 'isbn'])->findOrFail($book->id);
        });
        
        return new BookResource($data);
    }
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
    public function destroy(Book $book){
        Cache::forget("book:{$book->id}");
        $book->delete();
        Cache::forget("book:{$book->id}");
        return response()->noContent();
    }
}
