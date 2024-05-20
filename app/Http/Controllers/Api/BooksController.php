<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class BooksController
 * @package App\Http\Controllers\Api
 */
class BooksController extends Controller
{
  protected $bookService;

  public function __construct(BookService $bookService)
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
    $this->bookService = $bookService;
  }

  /**
   * O método `index` vai listar todos os livros.
   * 
   * Livros cujo autor tenha sido excluído ou que não tenham autor não 
   * serão listados, exceto em casos de co-autoria 
   * onde o co-autor ainda esteja ativo.
   * 
   * Essa decisão baseia-se na regra de Livroes 
   * podem não ter livros publicados, mas Livros não se publicam sem autores.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request)
  {
    // Define a quantidade de itens por página
    $perPage = $request->input('per_page', 10);

    /**
     * Busca todos os dados da tabela `books`.
     * O parâmetro indica que queremos os resultados com paginação.
     * Isso nos tratá um resultado do tipo Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    $books = $this->bookService->findAll($perPage);

    // Verifica se a consulta gerou resultados
    if ($books->isEmpty()) {
      // Caso não tenha retornado, responde com um código de erro `not found`
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    // Formatamos a saída como um array com paginação.
    $data = $this->bookService->getAllPaginate($books);

    // Responde a requisição com um status `200` indicando o sucesso da operação.
    return response()->json($data, 200);
  }

  /**
   * O método `store` vai gravar os dados do novo livro.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(StoreBookRequest $request)
  {
    // return response()->json($request, 200);
    try {
      /**
       * Aqui utilizamos a classe `BookService` através do método `create` 
       * que vai criar os dados na tabela `books` e então devolvê-los 
       * em um array já formatado com a resposta da requisição.
       */
      $book = $this->bookService->create($request->all());

      // Responde a requisição com um status `201` indicando o sucesso da operação.
      return response()->json($book, 201);
    } catch (\Throwable $th) {
      // Responde a requisição com um erro genérico em caso de falha.
      // Os detalhes do erro ficarão registrados em log.
      $error = [
        "success" => false,
        "message" => 'Não foi possível registrar o livro',
      ];
      // Responde a requisição com um status `500` indicando o erro da operação.
      return response()->json($error, 500);
    }
  }

  /**
   * O método `show` vai buscar os dados de um livro específico.
   *
   * @param  int  $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(string $id)
  {
    // Busca os dados da tabela `books` através do `id` informado.
    $book = $this->bookService->find($id);
    // $book = Author::with('books')->find($id);

    // Verifica se a consulta gerou resultados
    if (!$book) {
      // Caso não tenha retornado, responde com um código de erro `not found`
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    // Formatamos a saída como um array.
    $data = $this->bookService->getBook($book);

    // Responde a requisição com um status `200` indicando o sucesso da operação.
    return response()->json($data, 200);

    return response()->json(['book' => $data], 200);
  }

  /**
   * O método `update` vai atualizar os dados de um determinado livro.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(UpdateBookRequest $request, string $id)
  {
    try {
      /**
       * Utilizamos a classe `BookService`, através do método `update` 
       * para atualizar o os dados do Livro.
       */
      $update = $this->bookService->update($request->all(), $id);

      // O resultado esperado deve ser um array ou um booleano `false`.
      if (!$update) {
        // Se o resultado for `false`, respondemos com um status `404` indicando que não encontramos o resultado
        return response()->json([
          'message' => 'Nenhum resultado encontrado.'
        ], 404);
      }

      // Caso contrário, respondemos a requisição com um status `200` indicando o sucesso da operação.
      return response()->json($update, 200);
    } catch (\Throwable $th) {
      // Responde a requisição com um erro genérico em caso de falha.
      // Os detalhes do erro ficarão registrados em log.
      $error = [
        "success" => false,
        "message" => 'Não foi possível modificar o livro.'
      ];
      // Responde a requisição com um status `500` indicando o erro da operação.
      return response()->json($error, 500);
    }
  }

  /**
   * O método `destroy` vai excluir os dados de um determinado livro.
   *
   * @param  int  $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function destroy(string $id)
  {
    try {
      /**
       * Utilizamos a classe `BookService`, através do método `delete` 
       * para atualizar o os dados do Autor.
       */
      $delete = $this->bookService->delete($id);

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
        "message" => 'Não foi possível remover o livro.'
      ];
      // Responde a requisição com um status `500` indicando o erro da operação.
      return response()->json($error, 500);
    }
  }
}
