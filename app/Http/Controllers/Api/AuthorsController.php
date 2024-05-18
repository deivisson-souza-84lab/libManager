<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class AuthorsController
 * @package App\Http\Controllers\Api
 */
class AuthorsController extends Controller
{
    /**
     * O método `index` vai listar todos os autores.
     * 
     * Autores com livros associados trarão também um array 
     * com `id`, `title` e `publication_year` da tabela `books`.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Define quantos itens por página
        $perPage = $request->input('per_page', 10);

        $authors = Author::with('books')->paginate($perPage);

        if ($authors->isEmpty()) {
            return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
        }

        $data = $authors->map(function ($author) {
            return [
                'id' => $author->id,
                'name' => $author->name,
                'date_of_birth' => $author->date_of_birth,
                'books' => $author->books->map(function ($book) {
                    return [
                        'id' => $book->id,
                        'name' => $book->title,
                    ];
                }),
            ];
        });

        return response()->json([
            'authors' => $data,
            'pagination' => [
                'total' => $authors->total(),
                'per_page' => $authors->perPage(),
                'current_page' => $authors->currentPage(),
                'last_page' => $authors->lastPage(),
                'from' => $authors->firstItem(),
                'to' => $authors->lastItem(),
            ],
        ]);
    }

    /**
     * O método `store` vai gravar os dados do novo autor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:authors',
            'date_of_birth' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $author = Author::create($request->all());
        return response()->json(['author' => $author], 201);
    }

    /**
     * O método `show` vai buscar os dados de um autor específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $author = Author::with('books')->find($id);

        if (!$author) {
            return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
        }

        $data = [
            'id' => $author->id,
            'name' => $author->name,
            'date_of_birth' => $author->date_of_birth,
            'last_update' => $author->last_update,
            'books' => $author->books
        ];

        return response()->json(['author' => $data], 200);
    }

    /**
     * O método `update` vai atualizar os dados de um determinado autor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $author = Author::with('books')->find($id);

        if (!$author) {
            return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:authors',
            'date_of_birth' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $author->update($request->all());

        return response()->json(['author' => $author], 200);
    }

    /**
     * O método `destroy` vai excluir os dados de um determinado autor.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        $author = Author::with('books')->find($id);

        if (!$author) {
            return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
        }

        $author->delete();

        return response()->json(['message' => 'Autor removido com sucesso.'], 200);
    }
}
