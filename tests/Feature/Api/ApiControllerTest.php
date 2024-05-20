<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
  use RefreshDatabase;

  // Teste de criação de usuário
  public function testRegister()
  {
    $response = $this->postJson('/api/register', [
      'name' => 'Test User',
      'email' => 'test@example.com',
      'password' => 'password',
      'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201)
      ->assertJsonStructure([
        'message',
        'data' => [
          'id',
          'name',
          'email',
          'last_update'
        ]
      ]);

    $this->assertDatabaseHas('users', [
      'email' => 'test@example.com',
    ]);
  }

  // Teste de Login de usuário
  public function testLogin()
  {
    $user = User::factory()->create([
      'email' => 'test@example.com',
      'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/login', [
      'email' => 'test@example.com',
      'password' => 'password',
    ]);

    $response->assertStatus(200)
      ->assertJsonStructure([
        'message',
        'token',
        'expires_in',
      ]);
  }

  // Teste de Login inválido
  public function testInvalidLogin()
  {
    $user = User::factory()->create([
      'email' => 'test@example.com',
      'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/login', [
      'email' => 'test@example.com',
      'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
      ->assertJsonStructure([
        'message'
      ]);
  }

  // Teste consulta dados do usuário
  public function testProfile()
  {
    $user = User::factory()->create();
    $token = auth()->login($user);

    $response = $this->actingAs($user, 'api')->getJson('/api/profile');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'message',
        'data' => [
          'id',
          'name',
          'email',
          'last_update'
        ]
      ]);
  }

  // Teste de atuaização do Token
  public function testRefreshToken()
  {
    $user = User::factory()->create();
    $token = auth()->login($user);

    $response = $this->actingAs($user, 'api')->getJson('/api/refresh-token');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'message',
        'token',
        'expires_in',
      ]);
  }

  public function testLogout()
  {
    $user = User::factory()->create();
    $token = auth()->login($user);

    $response = $this->actingAs($user, 'api')->getJson('/api/logout');

    $response->assertStatus(200)
      ->assertJson([
        'message' => 'Sessão encerrada.',
      ]);
  }
}
