<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookAuthor;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class BookController extends Controller
{
    use HttpResponses;

    /**
     * @OA\Get(
     *     path="/api/books",
     *     summary="Get a list of all books with authors",
     *     tags={"Books"},
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of books with authors",
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Book::getAllBooksWithAuthor());
    }

    /**
     * @OA\Post(
     *     path="/api/books",
     *     summary="Create a new book",
     *     tags={"Books"},
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "publication", "author_id"},
     *             @OA\Property(property="title", type="string", example="Le Petit Prince"),
     *             @OA\Property(property="publication", type="integer", example=1943),
     *             @OA\Property(property="author_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book created successfully!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book could not be created!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'publication' => 'required|digits:4|integer',
            'author_id' => 'required|integer',
        ]);

        try {
            $book = Book::create([
                'title' => $request->title,
                'publication' => $request->publication,
            ]);

            BookAuthor::create([
                'book_id' => $book->id,
                'author_id' => $request->author_id,
            ]);

            return $this->response('Book created successfully!', 201, $book->toArray());
        } catch (Exception $e) {
            return $this->error('Book could not be created!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/books/{id}",
     *     summary="Get a specific book by ID",
     *     tags={"Books"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the book"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book details",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book not found!"),
     *             @OA\Property(property="error", type="string", example="Book with the specified ID does not exist.")
     *         )
     *     )
     * )
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json($book, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="Update a specific book",
     *     tags={"Books"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the book"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "publication", "author_id"},
     *             @OA\Property(property="title", type="string", example="Le Petit Prince"),
     *             @OA\Property(property="publication", type="integer", example=1943),
     *             @OA\Property(property="author_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book updated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book could not be updated!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'publication' => 'required|digits:4|integer',
            'author_id' => 'required|integer',
        ]);

        try {
            $book->update([
                'title' => $request->title,
                'publication' => $request->publication,
            ]);

            $book_author = BookAuthor::getBookAuthor($book->id);

            if ($book_author) {
                $book_author->update([
                    'book_id' => $book->id,
                    'author_id' => $request->author_id,
                ]);
            }

            return $this->response('Book updated successfully!', 200);
        } catch (Exception $e) {
            return $this->error('Book could not be updated!', 500, $e->getMessage());
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     summary="Delete a specific book",
     *     tags={"Books"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the book"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book not found!"),
     *             @OA\Property(property="error", type="string", example="Book with the specified ID does not exist.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book could not be deleted!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function destroy(Book $book): JsonResponse
    {
        try {
            $book->delete();

            return $this->response('Book deleted successfully!', 200);
        } catch (Exception $e) {
            return $this->error('Book could not be deleted!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Components(
     *     @OA\Schema(
     *         schema="Book",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="title", type="string", example="The Great Gatsby"),
     *         @OA\Property(property="publication", type="integer", example=1925),
     *         @OA\Property(property="author_id", type="integer", example=1),
     *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-26T00:00:00Z"),
     *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-26T00:00:00Z")
     *     )
     * )
     */
}
