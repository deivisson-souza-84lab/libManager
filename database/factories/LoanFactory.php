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
        // Obtém um usuário válido que não está com um empréstimo em aberto
        $user = User::whereDoesntHave('loans', function ($query) {
            $query->whereNull('return_date');
        })
            ->inRandomOrder()
            ->first();

        // Obtém um livro válido que não está emprestado
        $book = Book::whereNull('deleted_at')
            ->whereDoesntHave('loanedBooks', function ($query) {
                $query->whereHas('loan', function ($query) {
                    $query->whereNull('return_date');
                });
            })
            ->inRandomOrder()
            ->first();

        return [
            'user_id' => $user->id,
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
            // Obtém um livro válido que não está emprestado
            $book = Book::whereNull('deleted_at')
                ->whereDoesntHave('loanedBooks', function ($query) {
                    $query->whereHas('loan', function ($query) {
                        $query->whereNull('return_date');
                    });
                })
                ->inRandomOrder()
                ->first();
            LoanedBook::create([
                'loan_id' => $loan->id,
                'book_id' => $book->id,
            ]);
        });
    }
}
