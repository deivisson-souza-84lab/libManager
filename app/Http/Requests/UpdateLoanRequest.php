<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLoanRequest extends FormRequest
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
        return [
            'expected_return_date' => 'sometimes|required|date',
            'books.add' => 'sometimes|required|array|min:1',
            'books.remove' => 'sometimes|required|array|min:1',
            'books.add.*' => 'required|integer|exists:books,id',
        ];
    }

    public function attributes()
    {
        return [
            'return_date' => 'data de locação',
            'expected_return_date' => 'data programada para devolução',
        ];
    }

    public function messages()
    {
        return [
            'books.min' => 'É necessário ter pelo menos um livro na lista.',
            'books.*.exists' => 'O livro informado não consta na nossa base de dados.',
            'expected_return_date.date' => 'O campo :attribute deve ser uma data válida no formato `Y-m-d` ou `d-m-Y`.',
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
