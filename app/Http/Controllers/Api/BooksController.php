<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
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
    /**
     * O método `index` vai listar todos os livros.
     * Livros cujo autor tenha sido excluído não serão listados, exceto em casos de co-autoria 
     * onde o co-autor ainda esteja ativo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Define quantos itens por página
        $perPage = $request->input('per_page', 10);

        // $books = Book::with('authors')->get();
        $books = Book::with('authors')->has('authors')->paginate($perPage);

        if ($books->isEmpty()) {
            return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
        }

        $data = $books->map(function ($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'publication_year' => $book->publication_year,
                'authors' => $book->authors->map(function ($author) {
                    return [
                        'id' => $author->id,
                        'name' => $author->name,
                    ];
                }),
            ];
        });

        return response()->json([
            'books' => $data,
            'pagination' => [
                'total' => $books->total(),
                'per_page' => $books->perPage(),
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'from' => $books->firstItem(),
                'to' => $books->lastItem(),
            ],
        ]);
    }

    /**
     * O método `store` vai gravar os dados do novo livro.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|unique:books',
            'publication_year' => 'required|digits:4',
            'author.*' => [
                'required',
                'integer',
                Rule::exists('authors', 'id'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            DB::beginTransaction();

            throw new Exception("Error Processing Request", 1);

            $book = Book::create([
                'title' => $request->input('title'),
                'publication_year' => $request->input('publication_year'),
            ]);

            foreach ($request->input('author') as $authorId) {
                $book->authors()->attach($authorId);
            }

            DB::commit();

            return response()->json(['book' => $book], 201);
        } catch (\Throwable $th) {
            DB::rollback();

            if (app('env') != 'production') {
                return response()->json([
                    'error' => 'Falha ao registrar o livro.',
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'message' => $th->getMessage(),
                ], 500);
            }

            return response()->json(['error' => 'Falha ao registrar o livro.'], 500);
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
        $book = book::with('authors')->find($id);

        if (!$book) {
            return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
        }

        $data = [
            'id' => $book->id,
            'title' => $book->title,
            'publication_year' => $book->publication_year,
            'authors' => $book->authors->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->name,
                ];
            })
        ];

        return response()->json(['book' => $data], 200);
    }

    /**
     * O método `update` vai atualizar os dados de um determinado livro.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $book = book::with('authors')->find($id);

        if (!$book) {
            return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|unique:books,title,' . $id,
            'publication_year' => 'sometimes|required|digits:4|integer|min:0|max:9999',
            'author.*' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('authors', 'id'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            DB::beginTransaction();

            $book->update($request->only(['title', 'publication_year']));

            if ($request->has('author')) {
                $book->authors()->detach();
                foreach ($request->input('author') as $authorId) {
                    $book->authors()->attach($authorId);
                }
            }

            DB::commit();

            $data = [
                'id' => $book->id,
                'title' => $book->title,
                'publication_year' => $book->publication_year,
                'authors' => $book->authors->map(function ($author) {
                    return [
                        'id' => $author->id,
                        'name' => $author->name,
                    ];
                })
            ];

            return response()->json(['book' => $data], 200);
        } catch (\Throwable $th) {
            DB::rollback();

            if (app('env') != 'production') {
                return response()->json([
                    'error' => 'Falha ao atualizar o livro.',
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'message' => $th->getMessage(),
                ], 500);
            }

            return response()->json(['error' => 'Falha ao atualizar o livro.'], 500);
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
        $book = book::with('authors')->find($id);

        if (!$book) {
            return response()->json(['message' => 'Nenhum resultado encontrado.'], 404);
        }

        $book->delete();

        return response()->json(['message' => 'Livro excluído com sucesso.'], 200);
    }
}
