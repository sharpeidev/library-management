<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'borrowed_at',
        'returned_at',
    ];

    /**
     * @return Collection
     */
    static function getAllBorrowsWithUsersAndBooks(): Collection
    {
        return Borrow::select('borrows.id','books.title AS book','users.name AS user',
            'borrows.borrowed_at','borrows.returned_at')
            ->join('books', 'books.id', '=', 'borrows.book_id')
            ->join('users', 'users.id', '=', 'borrows.user_id')
            ->get();
    }

    /**
     * @param Borrow $borrow
     * @return Collection
     */
    public function getOneBorrowWithUserAndBook(Borrow $borrow): Collection
    {
        return Borrow::select('borrows.id','books.title AS book','users.name AS user',
            'borrows.borrowed_at','borrows.returned_at')
            ->join('books', 'books.id', '=', 'borrows.book_id')
            ->join('users', 'users.id', '=', 'borrows.user_id')
            ->where('borrows.id',$borrow->id)
            ->get();
    }
}
