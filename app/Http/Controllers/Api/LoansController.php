<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UpdateLoanRequest;
use App\Models\Loan;
use App\Models\LoanedBook;
use App\Services\LoanService;
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
  protected $loanService;

  public function __construct(LoanService $loanService)
  {
    /**
     * Aqui estou instanciando a classe `BookService`.
     * Ela foi criada com a principal finalidade de reduzir processamento 
     * de código dentro do BooksController. Ela assume a responsabilidade 
     * de manipular e formatar os dados da model Book.
     * 
     * Além disso, a classe `BookService` me permite um melhor controle 
     * sobre testes e utilizações dos recursos da model Book.
     */
    $this->loanService = $loanService;
  }

  /**
   * O método `index` vai listar todos os empréstimos.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request)
  {
    // Define a quantidade de itens por página
    $perPage = $request->input('per_page', 10);

    /**
     * Busca todos os dados da tabela `loans`.
     * O parâmetro indica que queremos os resultados com paginação.
     * Isso nos tratá um resultado do tipo Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    $loans = $this->loanService->findAll($perPage);

    // Verifica se a consulta gerou resultados
    if ($loans->isEmpty()) {
      // Caso não tenha retornado, responde com um código de erro `not found`
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    // Formatamos a saída como um array com paginação.
    $data = $this->loanService->getAllPaginate($loans);

    // Responde a requisição com um status `200` indicando o sucesso da operação.
    return response()->json($data, 200);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreLoanRequest $request)
  {
    try {
      /**
       * Aqui utilizamos a classe `LoanService` através do método `create` 
       * que vai criar os dados na tabela `loans` e então devolvê-los 
       * em um array já formatado com a resposta da requisição.
       */
      $loan = $this->loanService->create($request->all());

      // Responde a requisição com um status `201` indicando o sucesso da operação.
      return response()->json($loan, 201);
    } catch (\Throwable $th) {
      // Responde a requisição com um erro genérico em caso de falha.
      // Os detalhes do erro ficarão registrados em log.
      $error = [
        "success" => false,
        "message" => 'Não foi possível registrar o empréstimo'
      ];
      // Responde a requisição com um status `500` indicando o erro da operação.
      return response()->json($error, 500);
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    // Busca os dados da tabela `loans` através do `id` informado.
    $loan = $this->loanService->find($id);

    // Verifica se a consulta gerou resultados
    if (!$loan) {
      // Caso não tenha retornado, responde com um código de erro `not found`
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    // Formatamos a saída como um array.
    $data = $this->loanService->getLoan($loan);

    return response()->json($data, 200);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateLoanRequest $request, string $id)
  {
    /**
     * Utilizamos a classe `LoanService`, através do método `update` 
     * para atualizar os dados do empréstimo.
     */
    $update = $this->loanService->update($request->all(), $id);

    // O resultado esperado deve ser um array ou um booleano `false`.
    if (!$update) {
      // Se o resultado for `false`, respondemos com um status `404` indicando que não encontramos o resultado
      return response()->json([
        'message' => 'Nenhum resultado encontrado.'
      ], 404);
    }

    // Caso contrário, respondemos a requisição com um status `200` indicando o sucesso da operação.
    return response()->json($update, 200);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    try {
      /**
       * Utilizamos a classe `LoanService`, através do método `delete` 
       * para atualizar o os dados do Autor.
       */
      $delete = $this->loanService->delete($id);

      // O resultado esperado deve ser um array ou um booleano `false`.
      if (!$delete) {
        // Se o resultado for `false`, respondemos com um status `404` indicando que não encontramos o resultado
        return response()->json([
          'message' => 'Nenhum resultado encontrado.'
        ], 404);
      }

      // Caso contrário, respondemos a requisição com um status `200` indicando o sucesso da operação.
      return response()->json($delete, 200);
    } catch (\Throwable $th) {
      // Responde a requisição com um erro genérico em caso de falha.
      // Os detalhes do erro ficarão registrados em log.
      $error = [
        "success" => false,
        "message" => 'Não foi possível remover o empréstimo.'
      ];
      // Responde a requisição com um status `500` indicando o erro da operação.
      return response()->json($error, 500);
    }
  }
}
