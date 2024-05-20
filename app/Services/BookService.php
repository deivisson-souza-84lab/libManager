<?php

namespace App\Services;

use App\Models\AuthorBook;
use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class BookService
 * 
 * Esta classe cria mais uma camada de encapsulamento entre a App\Models\Book
 * e o App\Http\Controllers\Api\BooksController.
 * 
 * @package App\Services
 */
class BookService
{
  protected function addAuthor(int $authorId, int $bookId): void
  {
    $authorBooks = AuthorBook::where('book_id', $bookId)
      ->where('author_id', $authorId)
      ->withTrashed()
      ->get();

    if ($authorBooks->isEmpty()) {
      AuthorBook::create([
        'author_id' => $authorId,
        'book_id' => $bookId,
      ]);
    } else {
      foreach ($authorBooks as $authorBook) {
        if ($authorBook->trashed()) {
          $authorBook->restore();
        }
      }
    }
  }

  protected function removeAuthor(int $authorId, int $bookId): void
  {
    $authorBooks = AuthorBook::where('author_id', $authorId)
      ->where('book_id', $bookId)
      ->get();

    foreach ($authorBooks as $authorBook) {
      $authorBook->delete();
    }
  }
  /**
   * O método `dataCreated` recebe como parâmetro um App\Models\Book
   * contendo os dados recém criados. Sua finalidade é formatar estes dados
   * e devolvê-los em um Array.
   * @param App\Models\Book $book
   * @return array
   */
  protected function dataCreated(Book $book): array
  {
    $data = [
      'success' => true,
      'message' => 'Livro registrado com sucesso!',
      'book' => [
        'id' => $book->id,
        'title' => $book->title,
        'publication_year' => $book->publication_year,
        'created_at' => $book->created_at->format('Y-m-d H:i:s'),
        'authors' => $book->authors->map(function ($author) {
          return [
            'id' => $author->id,
            'name' => $author->name,
          ];
        })
      ],
    ];

    return $data;
  }

  /**
   * O método `dataUpdated` recebe como parâmetro um App\Models\Book
   * contendo os dados recém modificados. Sua finalidade é formatar estes dados
   * e devolvê-los em um Array.
   * @param App\Models\Book $book
   * @return array
   */
  protected function dataUpdated(Book $book): array
  {
    $data = [
      'success' => true,
      'message' => 'Livro modificado com sucesso!',
      'book' => [
        'id' => $book->id,
        'title' => $book->title,
        'publication_year' => $book->publication_year,
        'updated_at' => $book->updated_at->format('Y-m-d H:i:s'),
        'authors' => $book->authors->map(function ($author) {
          return [
            'id' => $author->id,
            'name' => $author->name,
          ];
        })
      ],
    ];

    return $data;
  }

  /**
   * O método `create` vai receber como parâmetro um Array com os dados 
   * para o novo registro em App\Models\Book.
   * Seu retorno é um Array com os dados formatados.
   * 
   * @param array $data
   * @return array
   */
  public function create(array $data): array
  {
    try {
      DB::beginTransaction();

      $book = Book::create($data);

      foreach ($data['authors'] as $author) {
        $book->authors()->attach($author);
      }

      DB::commit();
      return $this->dataCreated($book);
    } catch (\Throwable $th) {
      DB::rollBack();
      throw new \Exception($th->getMessage());
    }
  }

  /**
   * O método `update` vai receber como parâmetro um Array com os dados 
   * para atualização de um determinado registro em App\Models\Book.
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

      // Busca os dados da tabela `books` através do `id` informado.
      $book = $this->find($id);

      // Verifica se a consulta gerou resultados.
      if (!$book) {
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
      $book->update($data->only(['title', 'publication_year'])->toArray());

      /**
       * Eu poderia apenas fazer a inclusão de autor via $book->
       */
      if ($data->has('authors')) {
        $authors = $data->filter(function ($item, $key) {
          if ($key === 'authors') {
            return $item;
          }
        })->get('authors');

        if (array_key_exists('add', $authors)) {
          foreach ($authors['add'] as $author) {
            /**
             * Eu poderia fazer a inclusão de autor via $book->authors->attach($author);
             * Mas este recurso não dará o controle que eu desejo nesta parte da API.
             * Por isso decidi fazer esta etapa o mais manual possível.
             */
            $this->addAuthor($author, $book->id);
          }
        }
        if (array_key_exists('remove', $authors)) {
          foreach ($authors['remove'] as $author) {
            /**
             * Eu também poderia ter feito a remoção via $book->authors->detach();,
             * mas ele removeria o dado da tabela e não trabalharia com o softDeletes.
             * Como eu desejo manter este controle na tabela, foi melhor fazê-lo manual.
             */
            $this->removeAuthor($author, $book->id);
          }
        };
      }

      DB::commit();
      return $this->dataUpdated($this->find($id));
    } catch (\Throwable $th) {
      DB::rollBack();
      throw new \Exception($th->getMessage());
    }
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
    $book = $this->find($id);

    // Verifica se a consulta gerou resultados.
    if (!$book) {
      return false;
    }

    foreach ($book->authors as $author) {;
      $this->removeAuthor($author->id, $id);
    }

    // Havendo resultados a modificação é realizada.
    $book->delete();

    // um array é devolvido com os dados de resposta da requisição.
    return [
      "success" => true,
      "message" => "Livro removido com sucesso!",
    ];
  }

  /**
   * O método `find` tem uma finalidade bem específica que é retornar 
   * uma collection de App\Models\Book dos dados pesquisados.
   * 
   * @param int $id   Id do registro que será atualizado
   * @return Book|null
   */
  public function find(int $id): Book|null
  {
    return Book::with('authors')->find($id);
  }

  /**
   * O método `findAll()` geralmente é chamado de fora da class `BookService`.
   * Ele busca todos os dados ativos da tabela `books`.
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
      return Book::with('authors')->has('authors')->get();
    }

    // retornará os dados da tabela `books` com paginação
    return Book::with('authors')->has('authors')->paginate($perPage);
  }

  /**
   * O método `getAll()` é complementar ao método `findAll()`. Enquanto `findAll` vai
   * retornar uma `Collection` ou `LengthAwarePaginator`, de acordo com o parâmetro enviado,
   * `getAll()` vai receber a Collection retornada de `findAll()` e transformá-la em um array formatado.
   * 
   * @param Collection $books
   * @return array
   */
  public function getAll(Collection $books): array
  {
    return [
      'books' => $books->map(function ($book) {
        return $this->getBook($book);
      })
    ];
  }

  /**
   * O método `getAllPaginate()` é complementar ao método `findAll()`. Enquanto `findAll` vai
   * retornar uma `Collection` ou `LengthAwarePaginator`, de acordo com o parâmetro enviado,
   * `getAllPaginate()` vai receber a LengthAwarePaginator retornada de `findAll()` e transformá-la em um array formatado.
   * 
   * @param Collection $books
   * @return array
   */
  public function getAllPaginate(LengthAwarePaginator $books): array
  {
    return [
      'books' => $books->map(function ($book) {
        return $this->getBook($book);
      }),
      'pagination' => [
        'total' => $books->total(),
        'per_page' => $books->perPage(),
        'current_page' => $books->currentPage(),
        'last_page' => $books->lastPage(),
        'from' => $books->firstItem(),
        'to' => $books->lastItem(),
      ]
    ];
  }

  /**
   * O método `getBook()` é complementar aos métodos `getAll()`, `getAllPaginate` 
   * e também utilizado fora da class `App\Services\BookService`. Ele é o único deles
   * que recebe uma collection da model `App\Services\App\Models\Book` e devolve um array formatos com estes dados.
   * 
   * Os métodos `getAll()`, `getAllPaginate` devolvem uma lista de autores em formato array 
   * e cada resultado nesta lista é produzido por `getBook()`.
   * 
   * @param App\Services\App\Models\Book $book Collection com um único registro de `books`
   * @return array
   */
  public function getBook(Book $book): array
  {
    return [
      'book' => [
        'id' => $book->id,
        'title' => $book->title,
        'publication_year' => $book->publication_year,
        'last_update' => $book->updated_at->format('Y-m-d H:i:s'),
        'authors' => $book->authors->map(function ($author) {
          return [
            'id' => $author->id,
            'name' => $author->name
          ];
        }),
      ]
    ];
  }
}
