<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookAuthor extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'author_id'
    ];

    /**
     * @param int $book_id
     * @return BookAuthor|null
     */
    static function getBookAuthor(int $book_id): ?BookAuthor
    {
        return BookAuthor::query()
            ->where('book_id', $book_id)
            ->first();
    }
}
