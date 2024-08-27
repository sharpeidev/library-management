<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(title="Library Management API", version="1.0")
 */
class AuthController extends Controller
{
    use HttpResponses;

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Jonas Magalhães"),
     *             @OA\Property(property="email", type="string", format="email", example="jonas.magalhaes@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="pass123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation errors occurred"),
     *             @OA\Property(property="errors", type="object", additionalProperties=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User could not be registered!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:3',
        ]);

        $data = $request->all();
        $data['password'] = bcrypt($data['password']);

        try {
            $user = User::create($data);

            return $this->response('User created successfully!', 201, $user->toArray());
        } catch(Exception $e) {
            return $this->error('User could not be created', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login and get a JWT token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="jonas.magalhaes@email.com"),
     *             @OA\Property(property="password", type="string", example="pass123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful and token returned",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="your-jwt-token"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized!"),
     *             @OA\Property(property="error", type="string", example="Login credentials are invalid!")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return $this->error('Unauthorized!', 401, 'Login credentials are invalid!');
        }

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout the user",
     *     tags={"Auth"},
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout error!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return $this->response('Logout successfully!', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh JWT token",
     *     tags={"Auth"},
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="your-new-jwt-token"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token refresh error!"),
     *             @OA\Property(property="error", type="string", example="Error details here")
     *         )
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }

    /**
     * @OA\Components(
     *     @OA\Schema(
     *         schema="User",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="Jonas Magalhães"),
     *         @OA\Property(property="email", type="string", example="jonas.magalhaes@email.com"),
     *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-26T00:00:00Z"),
     *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-26T00:00:00Z")
     *     )
     * )
     */
}
