<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Models\Author;
use App\Services\AuthorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class AuthorsController
 * @package App\Http\Controllers\Api
 */
class AuthorsController extends Controller
{
  protected $authorService;

  public function __construct(AuthorService $authorService)
  {
    /**
     * Aqui estou instanciando a classe `AuthorService`.
     * Ela foi criada com a principal finalidade de reduzir processamento 
     * de código dentro do AuthorController. Ela assume a responsabilidade 
     * de manipular e formatar os dados da model Author.
     * 
     * Além disso, a classe `AuthorService` me permite um melhor controle 
     * sobre testes e utilizações dos recursos da model Author.
     */
    $this->authorService = $authorService;
  }

  /**
   * O método `index` vai listar todos os autores.
   * 
   * Autores com livros associados trarão também um array 
   * com `id`, `title` e `publication_year` da tabela `books`.
   * 
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    // Define a quantidade de itens por página
    $perPage = $request->input('per_page', 10);

    /**
     * Busca todos os dados da tabela `authors`.
     * O parâmetro indica que queremos os resultados com paginação.
     * Isso nos tratá um resultado do tipo Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    $authors = $this->authorService->findAll($perPage);

    // Verifica se a consulta gerou resultados
    if ($authors->isEmpty()) {
      // Caso não tenha retornado, responde com um código de erro `not found`
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    // Formatamos a saída como um array com paginação.
    $data = $this->authorService->getAllPaginate($authors);

    // Responde a requisição com um status `200` indicando o sucesso da operação.
    return response()->json($data, 200);
  }

  /**
   * O método `store` vai gravar os dados do novo autor.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(StoreAuthorRequest $request): JsonResponse
  {
    try {
      /**
       * Aqui utilizamos a classe `AuthorService` através do método `create` 
       * que vai criar os dados na tabela `authors` e então devolvê-los 
       * em um array já formatado com a resposta da requisição.
       */
      $author = $this->authorService->create($request->all());

      // Responde a requisição com um status `201` indicando o sucesso da operação.
      return response->json($author, 201);
    } catch (\Throwable $th) {
      // Responde a requisição com um erro genérico em caso de falha.
      // Os detalhes do erro ficarão registrados em log.
      $error = [
        "success" => false,
        "message" => 'Não foi possível registrar o autor',
      ];
      // Responde a requisição com um status `500` indicando o erro da operação.
      return response()->json($error, 500);
    }
  }

  /**
   * O método `show` vai buscar os dados de um autor específico.
   *
   * @param  int  $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(int $id): JsonResponse
  {
    // Busca os dados da tabela `authors` através do `id` informado.
    $author = $this->authorService->find($id);
    // $author = Author::with('books')->find($id);

    // Verifica se a consulta gerou resultados
    if (!$author) {
      // Caso não tenha retornado, responde com um código de erro `not found`
      return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
    }

    // Formatamos a saída como um array.
    $data = $this->authorService->getAuthor($author);

    // Responde a requisição com um status `200` indicando o sucesso da operação.
    return response()->json($data, 200);
  }

  /**
   * O método `update` vai atualizar os dados de um determinado autor.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(UpdateAuthorRequest $request, int $id): JsonResponse
  {
    try {
      /**
       * Utilizamos a classe `AuthorService`, através do método `update` 
       * para atualizar o os dados do Autor.
       */
      $update = $this->authorService->update($request->all(), $id);

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
        "message" => 'Não foi possível modificar o autor',
      ];
      // Responde a requisição com um status `500` indicando o erro da operação.
      return response()->json($error, 500);
    }
  }

  /**
   * O método `destroy` vai excluir os dados de um determinado autor.
   *
   * @param  int  $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function destroy(int $id): JsonResponse
  {
    try {
      /**
       * Utilizamos a classe `AuthorService`, através do método `delete` 
       * para atualizar o os dados do Autor.
       */
      $delete = $this->authorService->delete($id);

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
        "message" => 'Não foi possível remover o autor'
      ];
      // Responde a requisição com um status `500` indicando o erro da operação.
      return response()->json($error, 500);
    }
  }
}
