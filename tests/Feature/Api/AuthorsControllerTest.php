<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthorsControllerTest extends TestCase
{
  use RefreshDatabase; // Use isso para garantir que o banco de dados seja redefinido após cada teste

  /**
   * Testa a utilização do método index sem autenticação
   * 
   * @return void
   */
  public function testindexNotAuthorized(): void
  {
    $response = $this->getJson('/api/authors');

    $response->assertStatus(401);
  }

  /**
   * Testa o método index para listar todos os autores.
   *
   * @return void
   */
  public function testIndexNotFound()
  {
    $user = User::factory()->create();
    $token = auth()->login($user);

    $response = $this->getJson('/api/authors');

    $response->assertStatus(404);
  }

  /**
   * Testa o método store para gravar um novo autor.
   *
   * @return void
   */
  public function testStore()
  {
    $user = User::factory()->create();
    $token = auth()->login($user);

    $authorData = [
      'name' => 'John Doe',
      'date_of_birth' => '1980-01-01',
    ];

    $response = $this->actingAs($user, 'api')->postJson('/api/authors', $authorData);

    $response->assertStatus(201)
      ->assertJsonStructure([
        'success',
        'message',
        'author' => [
          'id',
          'name',
          'created_at'
        ]
      ]);
  }

  /**
   * Testa o método index para listar todos os autores.
   *
   * @return void
   */
  public function testIndex()
  {

    $user = User::factory()->create();
    $token = auth()->login($user);


    $author = Author::factory()->create();
    $this->assertModelExists($author);

    $response = $this->actingAs($user, 'api')->getJson('/api/authors');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'authors' => [
          '*' => [
            'author' => [
              'id',
              'name',
              'date_of_birth',
              'last_update',
              'books'
            ],
          ],
        ],
        'pagination' => [
          'total',
          'per_page',
          'current_page',
          'last_page',
          'from',
          'to',
        ],
      ]);
  }

  /**
   * Testa o método show para buscar os dados de um autor específico.
   *
   * @return void
   */
  public function testShow()
  {
    $user = User::factory()->create();
    $token = auth()->login($user);

    $author = Author::factory()->create();

    $response = $this->getJson('/api/authors/' . $author->id);

    $response->assertStatus(200)
      ->assertJsonStructure([
        'author' => [
          'id',
          'name',
          'date_of_birth',
          'last_update',
          'books'
        ]
      ]);
  }

  /**
   * Testa o método show para buscar os dados de um autor específico.
   *
   * @return void
   */
  public function testShowNotFound()
  {
    $user = User::factory()->create();
    $token = auth()->login($user);

    $response = $this->getJson('/api/authors/' . 0);

    $response->assertStatus(404)
      ->assertJsonStructure([
        'message'
      ]);
  }

  // Outros testes para os métodos show, update e destroy podem ser escritos de maneira semelhante

  /**
   * Testa o método destroy para excluir um autor.
   *
   * @return void
   */
  public function testDestroy()
  {
    $user = User::factory()->create();
    $token = auth()->login($user);
    $author = Author::factory()->create();

    $response = $this->deleteJson('/api/authors/' . $author->id);

    $response->assertStatus(200)
      ->assertJsonStructure(['message']);
  }
}
