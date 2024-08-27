<?php

namespace App\Http\Controllers;


use App\Jobs\SendEmailJob;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\User;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class BorrowController extends Controller
{
    use HttpResponses;

    /**
     * @OA\Get(
     *     path="/api/borrows",
     *     summary="Get a list of all borrows with users and books",
     *     tags={"Borrows"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of borrows with users and books",
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Borrow::getAllBorrowsWithUsersAndBooks());
    }

    /**
     * @OA\Post(
     *     path="/api/borrows",
     *     summary="Create a new borrow",
     *     tags={"Borrows"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "book_id", "borrowed_at"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="book_id", type="integer", example=1),
     *             @OA\Property(property="borrowed_at", type="string", format="date", example="2024-08-26")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Borrow record created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book borrowed with success!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book could not be borrowed!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'book_id' => 'required|integer',
            'borrowed_at' => 'required|date',
        ]);

        try {
            $borrow = Borrow::create($request->all());

            $user = User::find($borrow->user_id);
            $book = Book::find($borrow->book_id);

            $queue_data['email'] = $user->getEmailById($user);
            $queue_data['book'] = $book->name;
            $queue_data['date'] = $borrow->borrowed_at;

            self::enqueue($queue_data);

            return $this->response('Book borrowed with success!', 201, $borrow->toArray());
        } catch (Exception $e) {
            return $this->error('Book could not be borrow!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/borrows/{id}",
     *     summary="Get a specific borrow by ID",
     *     tags={"Borrows"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the borrow record"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Borrow record details",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Borrow record not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Borrow record not found!"),
     *             @OA\Property(property="error", type="string", example="Borrow record with the specified ID does not exist.")
     *         )
     *     )
     * )
     */
    public function show(Borrow $borrow): JsonResponse
    {
        return response()->json($borrow->getOneBorrowWithUserAndBook($borrow), 200);
    }

    /**
     * @OA\Put(
     *     path="/api/borrows/{id}",
     *     summary="Update a specific borrow",
     *     tags={"Borrows"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the borrow"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "book_id", "borrowed_at", "returned_at"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="book_id", type="integer", example=1),
     *             @OA\Property(property="borrowed_at", type="string", format="date", example="2024-08-26"),
     *             @OA\Property(property="returned_at", type="string", format="date", example="2024-09-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Borrow record updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book borrow updated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book borrow could not be updated!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Borrow $borrow): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'book_id' => 'required|integer',
            'borrowed_at' => 'required|date',
            'returned_at' => 'required|date',
        ]);

        try {
            $borrow->update($request->all());

            return $this->response('Book borrow updated successfully!', 200);
        } catch (Exception $e) {
            return $this->error('Book borrow could not be update!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/borrows/{id}",
     *     summary="Delete a specific borrow",
     *     tags={"Borrows"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the borrow"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Borrow record deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book borrow deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Borrow record not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Borrow record not found!"),
     *             @OA\Property(property="error", type="string", example="Borrow record with the specified ID does not exist.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book borrow could not be deleted!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function destroy(Borrow $borrow): JsonResponse
    {
        try {
            $borrow->delete();

            return $this->response('Book borrow deleted successfully!', 200);
        } catch (Exception $e) {
            return $this->error('Book borrow could not be deleted!', 500, $e->getMessage());
        }
    }

    static function enqueue(array $data): bool
    {
        $job = new SendEmailJob($data);

        if (dispatch($job)) {
            return true;
        }

        return false;
    }

    /**
     * @OA\Components(
     *     @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         type="http",
     *         scheme="bearer",
     *         bearerFormat="JWT"
     *     ),
     *
     *     @OA\Schema(
     *         schema="Borrow",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="user_id", type="integer", example=1),
     *         @OA\Property(property="book_id", type="integer", example=1),
     *         @OA\Property(property="borrowed_at", type="string", format="date", example="2024-08-26"),
     *         @OA\Property(property="returned_at", type="string", format="date", example="2024-09-01"),
     *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-26T00:00:00Z"),
     *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-26T00:00:00Z")
     *     )
     * )
     */
}
