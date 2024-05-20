<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanedBook;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;


/**
 * Class LoanService
 * 
 * Esta classe cria mais uma camada de encapsulamento entre a App\Models\Loan
 * e o App\Http\Controllers\Api\LoansController.
 * 
 * @package App\Services
 */
class LoanService
{
  protected function checkLoanAvailable(int $user): bool
  {
    $loanAvailable = !Loan::where('user_id', $user)
      ->whereNull('return_date')
      ->exists();

    return $loanAvailable;
  }

  protected function checkBookAvailable(int|array $book): bool
  {
    $books = is_array($book) ? $book : [$book];
    // Verificar se todos os livros estão ativos (deleted_at é nulo)
    $activeBooksCount = Book::whereIn('id', $books)
      ->whereNull('deleted_at')
      ->count();

    if ($activeBooksCount !== count($books)) {
      return false; // Nem todos os livros estão ativos
    }

    // Verificar se algum dos livros tem um empréstimo ativo
    $activeLoansCount = LoanedBook::whereIn('book_id', $books)
      ->whereHas('loan', function ($query) {
        $query->whereNull('return_date');
      })
      ->count();

    return $activeLoansCount === 0;
  }

  public function addLoanedBook(int $loanId, int $bookId): void
  {
    $loanedBooks = LoanedBook::where('book_id', $bookId)
      ->where('loan_id', $loanId)
      ->withTrashed()
      ->get();

    if ($loanedBooks->isEmpty()) {
      LoanedBook::create([
        'loan_id' => $loanId,
        'book_id' => $bookId,
      ]);
    } else {
      foreach ($loanedBooks as $loanedBook) {
        if ($loanedBook->trashed()) {
          $loanedBook->restore();
        }
      }
    }
  }

  protected function removeLoanedBook(int $bookId, int $loanId): void
  {
    $loanedBooks = LoanedBook::where('loan_id', $loanId)
      ->where('book_id', $bookId)
      ->get();

    foreach ($loanedBooks as $loanedBook) {
      $loanedBook->delete();
    }
  }

  /**
   * O método `dataCreated` recebe como parâmetro um App\Models\Loan
   * contendo os dados recém criados. Sua finalidade é formatar estes dados
   * e devolvê-los em um Array.
   * @param App\Models\Loan $book
   * @return array
   */
  protected function dataCreated(Loan $loan): array
  {
    $data = [
      'success' => true,
      'message' => 'Empréstimo registrado com sucesso!',
      'loan' => [
        'id' => $loan->id,
        'user_id' => $loan->user_id,
        'user_name' => $loan->user->name,
        'loan_date' => $loan->loan_date,
        'expected_return_date' => $loan->expected_return_date,
        'books' => $loan->loanedBooks->map(function ($loanedBook) {
          if ($loanedBook->book) {
            return [
              'book' => [
                'id' => $loanedBook->book->id,
                'title' => $loanedBook->book->title,
                'publication_year' => $loanedBook->book->publication_year,
                'authors' => $loanedBook->book->authors->map(function ($author) {
                  return [
                    'author' => [
                      'id' => $author->id,
                      'name' => $author->name,
                    ]
                  ];
                })
              ]
            ];
          }
          return [];
        })->toArray()
      ],
    ];

    return $data;
  }

  /**
   * O método `dataUpdated` recebe como parâmetro um App\Models\Loan
   * contendo os dados recém modificados. Sua finalidade é formatar estes dados
   * e devolvê-los em um Array.
   * @param App\Models\Loan $loan
   * @return array
   */
  protected function dataUpdated(Loan $loan): array
  {
    $data = [
      'success' => true,
      'message' => 'Empréstimo modificado com sucesso!',
      'loan' => [
        'id' => $loan->id,
        'user_id' => $loan->user_id,
        'user_name' => $loan->user->name,
        'loan_date' => $loan->loan_date,
        'expected_return_date' => $loan->expected_return_date,
        'books' => $loan->loanedBooks->map(function ($loanedBook) {
          if ($loanedBook->book) {
            return [
              'book' => [
                'id' => $loanedBook->book->id,
                'title' => $loanedBook->book->title,
                'publication_year' => $loanedBook->book->publication_year,
                'authors' => $loanedBook->book->authors->map(function ($author) {
                  return [
                    'author' => [
                      'id' => $author->id,
                      'name' => $author->name,
                    ]
                  ];
                })
              ]
            ];
          }
          return [];
        })->toArray()
      ],
    ];

    return $data;
  }

  /**
   * O método `create` vai receber como parâmetro um Array com os dados 
   * para o novo registro em App\Models\Loans.
   * Seu retorno é um Array com os dados formatados.
   * 
   * @param array $data
   * @return array
   */
  public function create(array $data): array
  {
    try {
      $loanAvailable = $this->checkLoanAvailable($data['user_id']);

      if ($loanAvailable) {
        $bookAvailable = $this->checkBookAvailable($data['books']);
        if ($bookAvailable) {
          DB::beginTransaction();

          $loan = Loan::create($data);

          foreach ($data['books'] as $book) {
            $this->addLoanedBook($loan->id, $book);
          }

          DB::commit();
          return $this->dataCreated($loan);
        }
        return [
          "success" => false,
          "message" => "Um ou mais livros solicitados não estão disponíveis para empréstimo.",
        ];
      }
      return [
        "success" => false,
        "message" => "Este usuário já possui um empréstimo em aberto.",
      ];
    } catch (\Throwable $th) {
      DB::rollBack();
      throw new \Exception($th->getMessage());
    }
  }

  /**
   * O método `update` vai receber como parâmetro um Array com os dados 
   * para atualização de um determinado registro em App\Models\Loan.
   * Seu retorno é false, caso não tenha encontrado registros para atualizar 
   * ou um Array com os dados atualizados, formatados.
   * 
   * @param array $data Dados para atualização
   * @param int   $id   Id do registro que será atualizado
   * @return bool|array
   */
  public function update(array $data, int $id): bool|array
  {
    try {
      DB::beginTransaction();

      // Busca os dados da tabela `loans` através do `id` informado.
      $loan = $this->find($id);

      // Verifica se a consulta gerou resultados.
      if (!$loan) {
        return false;
      }
      /**
       * Transformo o array de data em uma collection para 
       * ter maneiras mais fluidas de tratar seus dados.
       */
      $data = collect($data);

      /**
       * Aqui vamos atualizar apenas os campos `title` e `publication_year`       
       * da tabela `books`, se eles foram enviados na request.
       */
      $loan->update($data->only(['expected_return_date'])->toArray());

      if ($data->has('books')) {
        $books = $data->filter(function ($item, $key) {
          if ($key === 'books') {
            return $item;
          }
        })->get('books');
      }

      if (array_key_exists('add', $books)) {
        $bookAvailable = $this->checkBookAvailable($books['add']);
        if ($bookAvailable) {
          foreach ($books['add'] as $bookId) {
            $this->addLoanedBook($loan->id, $bookId);
          }
        } else {
          return [
            "success" => false,
            "message" => "Um ou mais livros solicitados não estão disponíveis para empréstimo.",
          ];
        }
      }

      if (array_key_exists('remove', $books)) {
        foreach ($books['remove'] as $bookId) {
          /**
           * Eu poderia ter feito a remoção via $book->loanedBooks->detach();,
           * mas ele removeria o dado da tabela e não trabalharia com o softDeletes.
           * Como eu desejo manter este controle na tabela, foi melhor fazê-lo manual.
           */
          $this->removeLoanedBook($bookId, $loan->id);
        }
      }

      DB::commit();
      return $this->dataUpdated($this->find($id));
    } catch (\Throwable $th) {
      DB::rollBack();
      throw new \Exception($th->getMessage());
    }
  }

  /**
   * O método `find` tem uma finalidade bem específica que é retornar 
   * uma collection de App\Models\Loan dos dados pesquisados.
   * 
   * @param int $id   Id do registro que será atualizado
   * @return Loan|null
   */
  public function find(int $id): Loan|null
  {
    return Loan::with('loanedBooks')->find($id);
  }

  /**
   * O método `findAll()` geralmente é chamado de fora da class `LoanService`.
   * Ele busca todos os dados ativos da tabela `loans`.
   * 
   * Sua importância para ser utlizado de maneira externa é que com a mesma consulta
   * podemos buscar todos os dados da tabela, paginados ou não.
   * 
   * Para isso ele recebe o parâmetro `$perPage`, que é um inteiro com a quantidade de itens por página.
   * A ausência deste parâmetro indica que a busca deve ser feita sem paginação.
   * 
   * @param int|null $perPage  Quando `null`, indica que o resultado deve ser obtido sem paginação.
   * @return Collection|LengthAwarePaginator
   */
  public function findAll(int|null $perPage = null): Collection|LengthAwarePaginator
  {
    if (is_null($perPage)) {
      // retornará os dados da tabela `books` sem paginação
      return Loan::with('loanedBooks')->has('books')->get();
    }

    // retornará os dados da tabela `books` com paginação
    return Loan::with('loanedBooks')->has('books')->paginate($perPage);
  }

  /**
   * O método `getAll()` é complementar ao método `findAll()`. Enquanto `findAll` vai
   * retornar uma `Collection` ou `LengthAwarePaginator`, de acordo com o parâmetro enviado,
   * `getAll()` vai receber a Collection retornada de `findAll()` e transformá-la em um array formatado.
   * 
   * @param Collection $loans
   * @return array
   */
  public function getAll(Collection $loans): array
  {
    return [
      'loans' => $loans->map(function ($loan) {
        return $this->getLoan($loan);
      })
    ];
  }

  /**
   * O método `getAllPaginate()` é complementar ao método `findAll()`. Enquanto `findAll` vai
   * retornar uma `Collection` ou `LengthAwarePaginator`, de acordo com o parâmetro enviado,
   * `getAllPaginate()` vai receber a LengthAwarePaginator retornada de `findAll()` e transformá-la em um array formatado.
   * 
   * @param Collection $loans
   * @return array
   */
  public function getAllPaginate(LengthAwarePaginator $loans): array
  {
    return [
      'loans' => $loans->map(function ($loan) {
        return $this->getLoan($loan);
      }),
      'pagination' => [
        'total' => $loans->total(),
        'per_page' => $loans->perPage(),
        'current_page' => $loans->currentPage(),
        'last_page' => $loans->lastPage(),
        'from' => $loans->firstItem(),
        'to' => $loans->lastItem(),
      ]
    ];
  }

  /**
   * O método `delete` recebe um parâmetro do tipo inteiro que 
   * identifica o registro a ser removido.
   * 
   * Retorna um booleano `false` caso não encontre o registro na tabela ou um 
   * array com as informações confirmando a remoção.
   * 
   * @param int   $id   Id do registro que será atualizado
   * @return bool|array
   */
  public function delete(int $id): bool|array
  {
    // Busca os dados da tabela `books` através do `id` informado.
    $loan = $this->find($id);

    // Verifica se a consulta gerou resultados.
    if (!$loan) {
      return false;
    }

    foreach ($loan->loanedBooks as $loanedBook) {;
      $this->removeLoanedBook($loanedBook->book_id, $id);
    }

    // Havendo resultados a modificação é realizada.
    $loan->delete();

    // um array é devolvido com os dados de resposta da requisição.
    return [
      "success" => true,
      "message" => "Empréstimo removido com sucesso!",
    ];
  }

  /**
   * O método `getLoan()` é complementar aos métodos `getAll()`, `getAllPaginate` 
   * e também utilizado fora da class `App\Services\LoanService`. Ele é o único deles
   * que recebe uma collection da model `App\Services\App\Models\Loan` e devolve um array formatos com estes dados.
   * 
   * Os métodos `getAll()`, `getAllPaginate` devolvem uma lista de autores em formato array 
   * e cada resultado nesta lista é produzido por `getLoan()`.
   * 
   * @param App\Services\App\Models\Loan $loan Collection com um único registro de `loans`
   * @return array
   */
  public function getLoan(Loan $loan): array
  {
    return [
      'loan' => [
        'id' => $loan->id,
        'user_id' => $loan->user_id,
        'user_name' => $loan->user->name,
        'loan_date' => $loan->loan_date,
        'expected_return_date' => $loan->expected_return_date,
        'return_date' => $loan->return_date,
        'books' => $loan->loanedBooks->map(function ($loanedBook) {
          if ($loanedBook->book) {
            return [
              'book' => [
                'id' => $loanedBook->book->id,
                'title' => $loanedBook->book->title,
                'publication_year' => $loanedBook->book->publication_year,
                'authors' => $loanedBook->book->authors->map(function ($author) {
                  return [
                    'author' => [
                      'id' => $author->id,
                      'name' => $author->name,
                    ]
                  ];
                })
              ]
            ];
          }
          return [];
        })->toArray(),
      ]
    ];
  }
}
