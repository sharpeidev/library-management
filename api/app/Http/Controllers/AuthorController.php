<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class AuthorController extends Controller
{
    use HttpResponses;

    /**
     * @OA\Get(
     *     security={{"bearer_token":{}}},
     *     path="/api/authors",
     *     summary="List all authors",
     *     tags={"Authors"},
     *     @OA\Response(
     *         response=200,
     *         description="List of authorss",
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Author::all());
    }

    /**
     * @OA\Post(
     *     security={{"bearer_token":{}}},
     *     path="/api/authors",
     *     summary="Create a new author",
     *     tags={"Authors"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "birthday"},
     *             @OA\Property(property="name", type="string", example="Jonas Magalhães"),
     *             @OA\Property(property="birthday", type="string", format="date", example="1979-09-19")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Author created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author created successfully!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author could not be created!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'birthday' => 'required|date|date_format:Y-m-d',
        ]);

        try {
            $author = Author::create($request->all());

            return $this->response('Author created successfully!', 201, $author->toArray());

        } catch (Exception $e) {
            return $this->error('Author could not be created!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     security={{"bearer_token":{}}},
     *     path="/authors/{author}",
     *     summary="Get a specific author",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="author",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the author"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author details",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author not found")
     *         )
     *     )
     * )
     */
    public function show(Author $author): JsonResponse
    {
        return response()->json($author, 200);
    }

    /**
     * @OA\Put(
     *     security={{"bearer_token":{}}},
     *     path="/authors/{author}",
     *     summary="Update a specific author",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="author",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the author"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "birthday"},
     *             @OA\Property(property="name", type="string", example="Jonas Magalhães"),
     *             @OA\Property(property="birthday", type="string", format="date", example="1979-09-19")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author updated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author could not be updated!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Author $author): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'birthday' => 'required|date|date_format:Y-m-d',
        ]);

        try {
            $author->update($request->all());

            return $this->response('Author updated successfully!', 200);
        } catch (Exception $e) {
            return $this->error('Author could not be updated!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     security={{"bearer_token":{}}},
     *     path="/authors/{author}",
     *     summary="Delete a specific author",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="author",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the author"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author could not be deleted!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function destroy(Author $author): JsonResponse
    {
        try {
            $author->delete();

            return $this->response('Author deleted successfully!', 200);
        } catch (Exception $e) {
            return $this->error('Author could not be deleted!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Components(
     *     @OA\Schema(
     *         schema="Author",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="Jonas Magalhães"),
     *         @OA\Property(property="birthday", type="string", format="date", example="1979-09-19"),
     *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-26T00:00:00Z"),
     *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-26T00:00:00Z")
     *     )
     * )
     */
}
