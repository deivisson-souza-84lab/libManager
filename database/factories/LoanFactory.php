<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanedBook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    /**
     * O modelo associado à fábrica.
     *
     * @var string
     */
    protected $model = Loan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Verifica se existem registros nas tabelas relacionadas
        if (User::count() === 0 || Book::count() === 0) {
            return null;
        }

        // Obtém um user_id válido
        $userId = User::inRandomOrder()->first()->id;

        return [
            'user_id' => $userId,
            'loan_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'return_date' => $this->faker->optional()->dateTime(),
            'expected_return_date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ];
    }

    /**
     * Configurações adicionais após a criação do empréstimo.
     *
     * @return \Closure
     */
    public function configure()
    {
        return $this->afterCreating(function (Loan $loan) {
            // Associa livros emprestados ao empréstimo
            $bookIds = Book::inRandomOrder()->limit(rand(1, 5))->pluck('id');
            foreach ($bookIds as $bookId) {
                LoanedBook::create([
                    'loan_id' => $loan->id,
                    'book_id' => $bookId,
                ]);
            }
        });
    }
}
