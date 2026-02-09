<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\BookResource;
use App\Http\Controllers\Controller;
use App\Models\Book;

class BookController extends Controller
{
    public function index(){
        return BookResource::collection(Book::query()->latest()->paginate(10));
    }
    public function store(Request $request){
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'author' => ['required', 'string', 'min:3', 'max:100'],
            'summary' => ['required', 'string', 'min:10', 'max:500'],
            'isbn' => ['required', 'string', 'size:13', 'unique:books,isbn'],
        ]);
        $book = Book::create($validated);
        return new BookRessource($book);
    }
    public function show(Book $book){
        return new BookResource($book);
    }
    public function update(Request $request, Book $book){
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'author' => ['required', 'string', 'min:3', 'max:100'],
            'summary' => ['required', 'string', 'min:10', 'max:500'],
            'isbn' => ['required', 'string', 'size:13', Rule::unique('books', 'isbn')->ignore($book->id)],
        ]);
        $book->update($validated);
        return new BookRessource($book);
    }
    public function destroy(Book $book){
        $book->delete();
        return response()->noContent();
    }
}
