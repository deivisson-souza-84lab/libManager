<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Faker\Factory as Faker;
use Tests\TestCase;

class BooksControllerTest extends TestCase
{
    use RefreshDatabase; // Use isso para garantir que o banco de dados seja redefinido após cada teste

    protected $route = '/api/books';

    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
    }


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

        Author::factory(20)->create();

        $book = Book::factory()->create();
        $this->assertModelExists($book);

        $response = $this->actingAs($user, 'api')->getJson($this->route);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'books' => [
                    '*' => [
                        'book' => [
                            'id',
                            'title',
                            'publication_year',
                            'last_update',
                            'authors'
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
     * Testa o método store para gravar um novo autor.
     *
     * @return void
     */
    public function testStore()
    {
        $user = User::factory()->admin()->create();

        Author::factory(20)->create();
        $author = Author::first();
        $year = Carbon::parse($author->date_of_birth)->format('Y');

        $bookData = [
            'title' => $this->faker->sentence(),
            'publication_year' => (int) $year,
            'authors' => [
                $author->id
            ]
        ];

        $response = $this->actingAs($user, 'api')->postJson($this->route, $bookData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'book' => [
                    'id',
                    'title',
                    'created_at',
                    'publication_year',
                    'authors'
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

        Author::factory(20)->create();

        $book = Book::factory()->create();
        $this->assertModelExists($book);

        $response = $this->actingAs($user, 'api')->getJson($this->route . '/' . $book->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'book' => [
                    'id',
                    'title',
                    'publication_year',
                    'last_update',
                    'authors',
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

        $response = $this->actingAs($user, 'api')->getJson($this->route . '/0');

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

        Author::factory(20)->create();

        $book = Book::factory()->create();
        $this->assertModelExists($book);

        $response = $this->actingAs($user, 'api')->deleteJson($this->route . '/' . $book->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }
}
