<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Defini como 10 o número padrão de resultados por página.
        $perPage = $request->query('per_page', 10);

        // Busca a lista de autores, paginada.
        $authors = Author::paginate($perPage);

        // Retornar a resposta paginada como JSON
        return response()->json($authors);
    }

    /**
     * Método de gravação de um novo Autor  
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:authors',
            'date_of_birth' => 'required|date',
        ]);

        $author = Author::create([
            'name' => $request->name,
            'date_of_birth' => $request->date_of_birth,
        ]);

        return response()->json($author, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
