<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // varible global untuk custom header
    public const Headers = [
        'X-Value' => 'MR. DAUD',
        'X-DATE' => '2025-03-24',
    ];

    public function register(Request $request)
    {
        $input = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        // buat user
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => bcrypt($input['password']),
        ]);
        // buat token
        $token = $user->createToken('IniAdalahKeyRahasia')->plainTextToken;
        $data = [
            'status' => Response::HTTP_CREATED,
            'message' => 'User berhasil dibuat',
            'data' => $user,
            'token' => $token,
            'type' => 'Bearer'
        ];
        return response()->json($data, Response::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *     path="/api/users/login",
     *     operationId="Login",
     *     tags={"Users"},
     *     summary="User Login",
     *     description="User Login here",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email", "password"},
     *               @OA\Property(property="email", type="string", example="isan@gmail.com"),
     *               @OA\Property(property="password", type="string", example="123456"),
     *            ),
     *        ),
     *        @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *               type="object",
     *               required={"email", "password"},
     *               @OA\Property(property="email", type="string", example="isan@gmail.com"),
     *               @OA\Property(property="password", type="string", example="123456"),
     *            ),
     *        ),
     *    ),
     *    @OA\Response(
     *        response=201,
     *        description="Login Successfully",
     *        @OA\JsonContent()
     *    ),
     *    @OA\Response(
     *        response=200,
     *        description="Login Successfully",
     *        @OA\JsonContent()
     *    ),
     *    @OA\Response(
     *        response=422,
     *        description="Unprocessable Entity",
     *        @OA\JsonContent()
     *    ),
     *    @OA\Response(response=400, description="Bad request"),
     *    @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function login(Request $request)
    {
        $input = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // authentikasi user
        $user = User::where('email', $input['email'])->first();

        // user tidak ditemukan & password tidak sesuai
        if (!$user || !Hash::check($input['password'], $user->password)) {
            return response()->json([
                'status' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }
        $token = $user->createToken('IniAdalahKeyRahasia')->plainTextToken;
        $data = [
            'status' => Response::HTTP_OK,
            'message' => 'User berhasil login',
            'data' => $user,
            'token' => $token,
            'type' => 'Bearer'
        ];
        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     operationId="User",
     *     tags={"Users"},
     *     summary="User detail",
     *     description="Use Bearer Token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function user()
    {
        $data = [
            'status' => Response::HTTP_OK,
            'message' => 'Detail user',
            'data' => auth()->user(),
        ];
        return response()->json($data, Response::HTTP_OK);
    }

    public function logout()
    {
        auth()->user()->tokens->each(function ($token) {
            $token->delete();
        });
        $data = [
            'status' => Response::HTTP_OK,
            'message' => 'Berhasil logout',
        ];
        return response()->json($data, Response::HTTP_OK);
    }
}
