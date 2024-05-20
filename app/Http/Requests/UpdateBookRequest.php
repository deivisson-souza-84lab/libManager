<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBookRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return auth()->check();
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $bookId = $this->route('book');

    return [
      'title' => 'sometimes|required|string|unique:books',
      'publication_year' => 'sometimes|required|digits:4',
      'authors.add' => 'sometimes|required|array',
      'authors.remove' => 'sometimes|required|array',
      'authors.add.*' => 'sometimes|required|integer|exists:authors,id'
    ];
  }

  protected function prepareForValidation()
  {
    if ($this->has('title')) {
      $this->merge([
        'title' => trim($this->title),
      ]);
    }
  }

  public function messages()
  {
    return [
      'publication_year.digits' => 'O campo :attribute deve ser um ano no formato YYYY.',
      'authors.min' => 'É obrigatório que o livro tenha pelo menos um autor.',
      'authors.*.exists' => 'Parece que este autor não está cadastrado. Confirme os dados e cadastre-o se for necessário.',
    ];
  }

  public function attributes()
  {
    return [
      'title' => 'título',
      'authors' => 'autor',
      'publication_year' => 'ano de publicação',
    ];
  }

  protected function failedValidation(Validator $validator)
  {
    throw new HttpResponseException(response()->json([
      'success' => false,
      'message' => 'Erro de validação',
      'errors' => $validator->errors()
    ], 422));
  }
}
