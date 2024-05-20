<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\AuthorBook;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * O modelo associado à fábrica.
     *
     * @var string
     */
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $authors = Author::selectRaw('YEAR(date_of_birth) as year_of_birth')->get()->toArray();

        if (empty($authors)) {
            return false;
        }

        // Passo 2: Buscar um valor aleatório neste array
        $randomYear = $authors[array_rand($authors)]['year_of_birth'];

        return [
            'title' => $this->faker->sentence(),
            'publication_year' => $randomYear,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Book $book) {
            // Encontra um autor com data de nascimento anterior à data de publicação do livro
            // $author = Author::where('YEAR(date_of_birth)', '<', $book->publication_year)->inRandomOrder()->first();
            $author = Author::where(DB::raw('YEAR(date_of_birth)'), '<', $book->publication_year)->inRandomOrder()->first();

            if ($author) {
                // Cria um registro na tabela author_book
                AuthorBook::create([
                    'author_id' => $author->id,
                    'book_id' => $book->id,
                ]);
            } else {
                // Se não encontrar um autor, exclui o livro recém-criado
                $book->delete();
            }
        });
    }
}
