<?php

namespace App\Services;

use App\Models\Author;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class AuthorService
 * @package App\Services
 */
class AuthorService
{
  /**
   * O método `dataCreated` recebe como parâmetro um App\Models\Author
   * contendo os dados recém criados. Sua finalidade é formatar estes dados
   * e devolvê-los em um Array.
   * @param App\Models\Author $author
   * @return array
   */
  protected function dataCreated(Author $author): array
  {
    $data = [
      'success' => true,
      'message' => 'Autor registrado com sucesso!',
      'author' => [
        'id' => $author->id,
        'name' => $author->name,
        'date_of_birth' => $author->date_of_birth,
        'created_at' => $author->created_at->format('Y-m-d H:i:s')
      ],
    ];

    return $data;
  }

  /**
   * O método `dataUpdated` recebe como parâmetro um App\Models\Author
   * contendo os dados recém modificados. Sua finalidade é formatar estes dados
   * e devolvê-los em um Array.
   * @param App\Services\App\Models\Author $author
   * @return array
   */
  protected function dataUpdated(Author $author): array
  {
    $data = [
      'success' => true,
      'message' => 'Autor modificado com sucesso!',
      'author' => [
        'id' => $author->id,
        'name' => $author->name,
        'date_of_birth' => $author->date_of_birth,
        'updated_at' => $author->updated_at->format('Y-m-d H:i:s')
      ],
    ];

    return $data;
  }

  /**
   * O método `create` vai receber como parâmetro um Array com os dados 
   * para o novo registro em App\Models\Author.
   * Seu retorno é um Array com os dados formatados.
   * 
   * @param array $data
   * @return array
   */
  public function create(array $data): array
  {
    try {
      DB::beginTransaction();
      $author = Author::create($data);
      DB::commit();
      return $this->dataCreated($author);
    } catch (\Throwable $th) {
      DB::rollBack();
    }
  }

  /**
   * O método `update` vai receber como parâmetro um Array com os dados 
   * para atualização de um determinado registro em App\Models\Author.
   * Seu retorno é false, caso não tenha encontrado registros para atualizar 
   * ou um Array com os dados atualizados, formatados.
   * 
   * @param array $data Dados para atualização
   * @param int   $id   Id do registro que será atualizado
   * @return bool|array
   */
  public function update(array $data, int $id): bool|array
  {
    // Busca os dados da tabela `authors` através do `id` informado.
    $author = $this->find($id);

    // Verifica se a consulta gerou resultados.
    if (!$author) {
      return false;
    }

    // Havendo resultados a modificação é realizada.
    $author->update($data);

    // um array é devolvido com os dados de resposta da requisição.
    return $this->dataUpdated($author);
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
    // Busca os dados da tabela `authors` através do `id` informado.
    $author = $this->find($id);

    // Verifica se a consulta gerou resultados.
    if (!$author) {
      return false;
    }

    // Havendo resultados a modificação é realizada.
    $author->delete();

    // um array é devolvido com os dados de resposta da requisição.
    return [
      "success" => true,
      "message" => "Autor removido com sucesso!",
    ];
  }

  /**
   * O método `find` tem uma finalidade bem específica que é retornar 
   * uma collection de App\Models\Author dos dados pesquisados.
   * 
   * @param int $id   Id do registro que será atualizado
   * @return Author|null
   */
  public function find(int $id): Author|null
  {
    $author = Author::with('books')->find($id);
    return $author;
  }

  /**
   * O método `findAll()` geralmente é chamado de fora da class `AuthorService`.
   * Ele busca todos os dados ativos da tabela `authors`.
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
      // retornará os dados da tabela `authors` sem paginação
      return Author::with('books')->get();
    }

    // retornará os dados da tabela `authors` com paginação
    return Author::with('books')->paginate($perPage);
  }

  /**
   * O método `getAll()` é complementar ao método `findAll()`. Enquanto `findAll` vai
   * retornar uma `Collection` ou `LengthAwarePaginator`, de acordo com o parâmetro enviado,
   * `getAll()` vai receber a Collection retornada de `findAll()` e transformá-la em um array formatado.
   * 
   * @param Collection $authors
   * @return array
   */
  public function getAll(Collection $authors): array
  {
    return [
      'authors' => $authors->map(function ($author) {
        return $this->getAuthor($author);
      })
    ];
  }

  /**
   * O método `getAllPaginate()` é complementar ao método `findAll()`. Enquanto `findAll` vai
   * retornar uma `Collection` ou `LengthAwarePaginator`, de acordo com o parâmetro enviado,
   * `getAllPaginate()` vai receber a LengthAwarePaginator retornada de `findAll()` e transformá-la em um array formatado.
   * 
   * @param Collection $authors
   * @return array
   */
  public function getAllPaginate(LengthAwarePaginator $authors): array
  {
    return [
      'authors' => $authors->map(function ($author) {
        return $this->getAuthor($author);
      }),
      'pagination' => [
        'total' => $authors->total(),
        'per_page' => $authors->perPage(),
        'current_page' => $authors->currentPage(),
        'last_page' => $authors->lastPage(),
        'from' => $authors->firstItem(),
        'to' => $authors->lastItem(),
      ]
    ];
  }

  /**
   * O método `getAuthor()` é complementar aos métodos `getAll()`, `getAllPaginate`.
   * Este método recebe uma instância de `App\Services\App\Models\Author` e retorna um array
   * formatado com os dados do autor.
   * 
   * Os métodos `getAll()` e `getAllPaginate()` devolvem uma lista de autores em formato de array,
   * e cada resultado nesta lista é produzido pelo método `getAuthor()`.
   * 
   * @param App\Services\App\Models\Author $author Instância d `AuthorService`
   * @return array
   */
  public function getAuthor(Author $author): array
  {
    return [
      'author' => [
        'id' => $author->id,
        'name' => $author->name,
        'date_of_birth' => $author->date_of_birth,
        'last_update' => $author->updated_at->format('Y-m-d H:i:s'),
        'books' => $author->books->map(function ($book) {
          return [
            'id' => $book->id,
            'title' => $book->title,
            'publication_year' => $book->publication_year
          ];
        }),
      ]
    ];
  }
}
