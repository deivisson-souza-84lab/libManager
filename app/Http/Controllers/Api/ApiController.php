<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    /**
     * Método para calcular e formatar a data de expiração do Token.
     * @return string retorna a data em formato de texto.
     */
    private function expiresDate()
    {
        $expires_in = auth()->factory()->getTTL() * 60;
        $dateTimeNow = new DateTime();
        $dateTimeNow->modify("+{$expires_in} seconds");
        return $dateTimeNow->format('Y-m-d H:i:s');
    }

    /**
     * Método de registro de novos usuários na API
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validação dos dados da requisição
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        // Gravação de usuários por meio da model User
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        // Retorno da requisição
        return response()->json([
            'status' => true,
            'message' => 'usuário registrado com sucesso',
            'data' => []
        ]);
    }

    /**
     * Método de login de ususários na API
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validação dos dados da requisição
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $token = auth()->attempt([
            "email" => $request->email,
            "password" => $request->password
        ]);

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Login inváido',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Usuário logado.',
            'token' => $token,
            'expires_in' => $this->expiresDate()
        ]);
    }

    /**
     * Método de visualização do perfil de usuário (JWT Auth Token)
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        $userData = request()->user();

        return response()->json([
            'status' => true,
            'message' => 'Dados do perfil.',
            'email' => $userData->email,
            'user' => $userData->name,
            'updated_at' => $userData->updated_at,
        ]);
    }

    /**
     * Método de atualização do Token da API (JWT Auth Token)
     * @return \Illuminate\Http\Response
     */
    public function refreshToken()
    {
        $token = auth()->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Token atualizado.',
            'token' => $token,
            'expires_in' => $this->expiresDate()
        ]);
    }

    /**
     * Método de encerramento de sessão da API (JWT Auth Token)
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => true,
            'message' => 'Sessão encerrada.',
        ]);
    }
}
