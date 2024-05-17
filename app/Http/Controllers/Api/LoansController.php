<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanedBook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class LoansController
 * @package App\Http\Controllers\Api
 */
class LoansController extends Controller
{
  /**
   * O método `index` vai listar todos os empréstimos.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request)
  {
    $perPage = $request->input('per_page', 10);

    $loans = Loan::with('loanedBooks.book')->paginate($perPage);

    if ($loans->isEmpty()) {
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    // Transforma os dados para o formato desejado
    $data = $loans->map(function ($loan) {
      return [
        'id' => $loan->id,
        'user_id' => $loan->user_id,
        'loan_date' => $loan->loan_date,
        'return_date' => $loan->return_date,
        'loaned_books' => $loan->loanedBooks->map(function ($loanedBook) {
          return [
            'id' => $loanedBook->id,
            'expected_return_date' => $loanedBook->expected_return_date,
            'books' => [
              'id' => $loanedBook->book->id,
              'title' => $loanedBook->book->title,
              'publication_year' => $loanedBook->book->publication_year,
            ],
          ];
        }),
      ];
    });

    // Retorna a resposta paginada no formato desejado
    return response()->json([
      'loans' => $data,
      'pagination' => [
        'total' => $loans->total(),
        'per_page' => $loans->perPage(),
        'current_page' => $loans->currentPage(),
        'last_page' => $loans->lastPage(),
        'from' => $loans->firstItem(),
        'to' => $loans->lastItem(),
      ],
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'user_id' => 'required|integer|exists:users,id',
      'loan_date' => 'required|date',
      'expected_return_date' => 'sometimes|date',
      'loaned_books' => 'required|array|min:1',
      'loaned_books.*' => 'required|integer|exists:books,id',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 400);
    }

    // Verificar se algum dos livros já está emprestado
    $loanedBooks = $request->input('loaned_books');
    $currentlyLoanedBooks = LoanedBook::whereIn('book_id', $loanedBooks)
      ->whereHas('loan', function ($query) {
        $query->whereNull('return_date');
      })
      ->with('book')
      ->get();

    if ($currentlyLoanedBooks->isNotEmpty()) {
      $loanedBookDetails = $currentlyLoanedBooks->map(function ($loanedBook) {
        return [
          'id' => $loanedBook->book->id,
          'name' => $loanedBook->book->title,
        ];
      });

      return response()->json([
        'error' => 'Os seguintes livros já estão emprestados e não podem ser emprestados novamente:',
        'books' => $loanedBookDetails,
      ], 400);
    }

    try {
      // Iniciar uma transação
      DB::beginTransaction();

      $loanDate = Carbon::parse($request->input('loan_date'));
      $expectedReturnDate = $request->input('expected_return_date') ? Carbon::parse($request->input('expected_return_date')) : $loanDate->copy()->addDays(10);

      // Criar um novo empréstimo
      $loan = Loan::create([
        'user_id' => $request->input('user_id'),
        'loan_date' => $loanDate->format('Y-m-d H:i:s'),
        'expected_return_date' => $expectedReturnDate->format('Y-m-d H:i:s'),
      ]);

      // Adicionar os livros emprestados ao empréstimo
      foreach ($request->input('loaned_books') as $book) {
        $loan->loanedBooks()->create([
          'book_id' => $book,
        ]);
      }

      // Commit da transação
      DB::commit();

      $data = [
        'user_id' => $loan->user_id,
        'user_name' => $loan->user->name,
        'loan_date' => $loan->loan_date,
        'expected_return_date' => $loan->expected_return_date,
        'id' => $loan->id,
        'books' => $loan->books->map(function ($book) {
          return [
            'id' => $book->id,
            'title' => $book->title,
          ];
        }),
      ];

      return response()->json(['loan' => $data], 201);
    } catch (\Throwable $th) {
      // Reverter a transação em caso de erro
      DB::rollback();

      return response()->json([
        'error' => 'Falha ao criar o empréstimo.',
        'file' => $th->getFile(),
        'line' => $th->getLine(),
        'message' => $th->getMessage(),
      ], 500);
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    $loan = Loan::with('books')->find($id);

    if (!$loan) {
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    $data = [
      'id' => $loan->id,
      'loan_date' => $loan->loan_date,
      'return_date' => $loan->return_date,
      'expected_return_date' => $loan->expected_return_date,
      'user_id' => $loan->user->id,
      'user_name' => $loan->user->name,
      'books' => $loan->books->map(function ($author) {
        return [
          'id' => $author->id,
          'name' => $author->title,
        ];
      })
    ];

    return response()->json(['loan' => $data], 200);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $validator = Validator::make($request->all(), [
      'user_id' => 'sometimes|required|integer|exists:users,id',
      'loan_date' => 'sometimes|required|date',
      'return_date' => 'sometimes|required|date',
      'expected_return_date' => 'sometimes|required|date',
      'loaned_books' => 'sometimes|required|array|min:1',
      'loaned_books.*' => 'required|integer|exists:books,id',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 400);
    }
    $loan = Loan::find($id);

    if (!$loan) {
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    // return response()->json(['loan' => $loan], 200);

    try {

      // Iniciar uma transação
      DB::beginTransaction();

      if ($request->has('loaned_books')) {

        $loan->loanedBooks()->forceDelete();

        // Verificar se algum dos livros já está emprestado
        $loanedBooks = $request->input('loaned_books');
        $currentlyLoanedBooks = LoanedBook::whereIn('book_id', $loanedBooks)
          ->whereHas('loan', function ($query) {
            $query->whereNull('return_date');
          })
          ->with('book')
          ->get();

        if ($currentlyLoanedBooks->isNotEmpty()) {
          $loanedBookDetails = $currentlyLoanedBooks->map(function ($loanedBook) {
            return [
              'id' => $loanedBook->book->id,
              'name' => $loanedBook->book->title,
            ];
          });

          return response()->json([
            'error' => 'Os seguintes livros já estão emprestados e não podem ser emprestados novamente:',
            'books' => $loanedBookDetails,
          ], 400);
        }

        // Adicionar os livros emprestados ao empréstimo
        foreach ($request->input('loaned_books') as $book) {
          $loan->loanedBooks()->create([
            'book_id' => $book,
          ]);
        }
      }

      // Atualizar os dados do empréstimo, se fornecidos no request
      if ($request->has('user_id')) {
        $loan->user_id = $request->input('user_id');
      }

      if ($request->has('loan_date')) {
        $loan->loan_date = Carbon::parse($request->input('loan_date'))->format('Y-m-d H:i:s');
      }

      if ($request->has('return_date')) {
        $loan->return_date = Carbon::parse($request->input('return_date'))->format('Y-m-d H:i:s');
      }

      if ($request->has('expected_return_date')) {
        $loan->expected_return_date = Carbon::parse($request->input('expected_return_date'))->format('Y-m-d H:i:s');
      }

      // Salvar as alterações
      $loan->save();

      // Commit da transação
      DB::commit();

      $data = [
        'id' => $loan->id,
        'loan_date' => $loan->loan_date,
        'return_date' => $loan->return_date,
        'expected_return_date' => $loan->expected_return_date,
        'user_id' => $loan->user->id,
        'user_name' => $loan->user->name,
        'books' => $loan->books->map(function ($author) {
          return [
            'id' => $author->id,
            'name' => $author->title,
          ];
        })
      ];

      return response()->json(['message' => 'Empréstimo atualizado com sucesso.', 'loan' => $data], 200);
    } catch (\Throwable $th) {
      // Reverter a transação em caso de erro
      DB::rollback();

      return response()->json([
        'error' => 'Failed to update the loan.',
        'file' => $th->getFile(),
        'line' => $th->getLine(),
        'message' => $th->getMessage(),
      ], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    $loan = loan::with('books')->find($id);

    if (!$loan) {
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    try {
      // Iniciar uma transação
      DB::beginTransaction();

      // Remover todos os livros emprestados associados a este empréstimo
      $loan->loanedBooks()->delete();

      // Excluir o empréstimo
      $loan->delete();

      // Commit da transação
      DB::commit();

      return response()->json(['message' => 'Empréstimo removido com sucesso.'], 200);
    } catch (\Throwable $th) {
      // Reverter a transação em caso de erro
      DB::rollback();

      return response()->json([
        'error' => 'Failed to delete the loan.',
        'file' => $th->getFile(),
        'line' => $th->getLine(),
        'message' => $th->getMessage(),
      ], 500);
    }
  }
}
