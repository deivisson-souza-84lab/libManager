<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiControllerRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
  public function register(StoreUserRequest $request)
  {
    try {
      // Gravação de usuários por meio da model User
      $user = User::create($request->all());

      // Retorno da requisição
      return response()->json([
        'message' => 'usuário registrado com sucesso',
        'data' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'last_update' => $user->updated_at,
        ]
      ], 201);
    } catch (ValidationException $e) {
      return response()->json([
        'message' => 'Erro de validação',
        'errors' => $e->errors()
      ], 422);
    } catch (\Throwable $th) {
      return response()->json([
        'message' => 'Erro ao registrar usuário',
        'error' => $th->getMessage()
      ], 500);
    }
  }

  /**
   * Método de login de ususários na API
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function login(Request $request)
  {
    try {
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
          'message' => 'Login inválido',
        ], 401);
      }

      return response()->json([
        'message' => 'Usuário logado.',
        'token' => $token,
        'expires_in' => $this->expiresDate()
      ], 200);
    } catch (ValidationException $e) {

      return response()->json([
        'message' => 'Erro de validação',
        'errors' => $e->errors()
      ], 422);
    } catch (\Throwable $th) {

      return response()->json([
        'message' => 'Erro no login',
        'error' => $th->getMessage()
      ], 500);
    }
  }

  /**
   * Método de visualização do perfil de usuário (JWT Auth Token)
   * @return \Illuminate\Http\Response
   */
  public function profile()
  {
    $user = Auth()->user();

    return response()->json([
      'message' => 'Dados do perfil.',
      'data' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'last_update' => $user->updated_at
      ]
    ], 200);
  }

  /**
   * Método de atualização do Token da API (JWT Auth Token)
   * @return \Illuminate\Http\Response
   */
  public function refreshToken()
  {
    try {
      $token = auth()->refresh();

      return response()->json([
        'message' => 'Token atualizado.',
        'token' => $token,
        'expires_in' => $this->expiresDate()
      ], 200);
    } catch (\Throwable $th) {
      return response()->json([
        'message' => 'Erro ao atualizar token',
        'error' => $th->getMessage()
      ], 500);
    }
  }

  /**
   * Método de encerramento de sessão da API (JWT Auth Token)
   * @return \Illuminate\Http\Response
   */
  public function logout()
  {
    try {
      auth()->logout();

      return response()->json([
        'message' => 'Sessão encerrada.',
      ], 200);
    } catch (\Throwable $th) {

      return response()->json([
        'message' => 'Erro ao encerrar sessão',
        'error' => $th->getMessage()
      ], 500);
    }
  }
}
