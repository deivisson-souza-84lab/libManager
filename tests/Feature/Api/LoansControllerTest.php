<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Faker\Factory as Faker;
use Tests\TestCase;

class LoansControllerTest extends TestCase
{
  protected $route = '/api/loans';

  protected $faker;

  public function setUp(): void
  {
    parent::setUp();
    $this->faker = Faker::create();
  }
  use RefreshDatabase; // Use isso para garantir que o banco de dados seja redefinido após cada teste

  /**
   * Testa a utilização do método index sem autenticação
   * 
   * @return void
   */
  public function testindexNotAuthorized(): void
  {
    $response = $this->getJson($this->route);

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

    $response = $this->actingAs($user, 'api')->getJson($this->route);

    $response->assertStatus(404);
  }

  /**
   * Testa o método index para listar todos os autores.
   *
   * @return void
   */
  public function testIndex()
  {

    $user = User::factory()->create();

    Author::factory(40)->create();
    Book::factory(40)->create();

    $loan = Loan::factory(20)->create();

    $response = $this->actingAs($user, 'api')->getJson($this->route);

    $response->assertStatus(200)
      ->assertJsonStructure([
        'loans' => [
          '*' => [
            'loan' => [
              'id',
              'user_id',
              'user_name',
              'loan_date',
              'expected_return_date',
              'return_date',
              'books'
            ],
          ],
        ],
      ]);
  }

  /**
   * Testa o método store para gravar um novo autor.
   *
   * @return void
   */
  public function testStore()
  {
    $user = User::factory()->admin()->create();

    Author::factory(20)->create();

    Book::factory(20)->create();
    $book = Book::first();

    $loanData = [
      'user_id' => $user->id,
      'loan_date' => $this->faker->dateTime('now')->format('Y-m-d'),
      'expected_return_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
      'books' => [$book->id]
    ];

    $response = $this->actingAs($user, 'api')->postJson($this->route, $loanData);

    $response->assertStatus(201)
      ->assertJsonStructure([
        'success',
        'message',
        'loan' => [
          'id',
          'user_id',
          'user_name',
          'loan_date',
          'expected_return_date',
          'books'
        ]
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

    Author::factory(80)->create();
    Book::factory(80)->create();
    Loan::factory(40)->create();

    $loan = Loan::first();

    $response = $this->actingAs($user, 'api')->getJson($this->route . '/' . $loan->id);

    $response->assertStatus(200)
      ->assertJsonStructure([
        'loan' => [
          'id',
          'user_id',
          'user_name',
          'loan_date',
          'expected_return_date',
          'return_date',
          'books',
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

    $response = $this->actingAs($user, 'api')->getJson($this->route . 0);

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
    $user = User::factory()->admin()->create();

    Author::factory(80)->create();
    Book::factory(80)->create();
    Loan::factory(40)->create();

    $loan = Loan::first();

    $response = $this->actingAs($user, 'api')->deleteJson($this->route . '/' . $loan->id);

    $response->assertStatus(200)
      ->assertJsonStructure(['message']);
  }
}
