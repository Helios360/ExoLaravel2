<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'author' => Str::upper($this->author),
            'summary' => $this->summary,
            'isbn' => $this->isbn,
            '_links' => [
                'self' => route('books.show', $this->resource),
                'update' => route('books.update', $this->resource),
                'delete' => route('books.destroy', $this->resource),
                'all' => route('books.index'),
            ],
        ];
    }
}
