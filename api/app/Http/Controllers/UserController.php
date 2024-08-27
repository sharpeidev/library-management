<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    use HttpResponses;

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="List all users",
     *     tags={"Users"},
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(User::all());
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}",
     *     summary="Get a specific user",
     *     tags={"Users"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the user"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{user}",
     *     summary="Update a specific user",
     *     tags={"Users"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the user"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Jonas Magalhães"),
     *             @OA\Property(property="email", type="string", example="jonas.magalhaes@email.com"),
     *             @OA\Property(property="password", type="string", example="newpass123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User updated successfully!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User could not be updated!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:3',
        ]);

        $data = $request->all();

        $data['password'] = bcrypt($data['password']);

        try {
            $user->update($data);

            return $this->response('User updated successfully!', 200, $request->all());
        } catch (Exception $e) {
            return $this->error('User could not be updated!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{user}",
     *     summary="Delete a specific user",
     *     tags={"Users"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the user"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User could not be deleted!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $user->delete();

            return $this->response('User deleted successfully!', 200);
        } catch (Exception $e) {
            return $this->error('User could not be deleted!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Components(
     *     @OA\Schema(
     *         schema="User",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="Jonas MAgalhães"),
     *         @OA\Property(property="email", type="string", example="jonas.magalhaes@email.com"),
     *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-26T00:00:00Z"),
     *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-26T00:00:00Z")
     *     )
     * )
     */
}
