<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'publication',
    ];

    /**
     * @return Collection
     */
    static function getAllBooksWithAuthor(): Collection
    {
        return Book::select('books.id','books.title', 'books.publication', 'authors.name')
            ->join('book_authors', 'book_authors.book_id', '=', 'books.id')
            ->join('authors', 'authors.id', '=', 'book_authors.author_id')
            ->get();
    }
}
