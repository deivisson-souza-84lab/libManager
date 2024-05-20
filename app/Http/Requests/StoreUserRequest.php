<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Se o campo 'role' não estiver presente, permita a requisição
        if (!$this->has('role')) {
            return true;
        }

        // Se o campo 'role' estiver presente, verifique se o usuário está autenticado e é um administrador
        if (Auth::check() && Auth::user()->isAdmin()) {
            return true;
        }

        // Lança uma exceção personalizada se a autorização falhar
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|confirmed'
        ];

        // Adicione uma regra para 'role' apenas se o campo estiver presente
        if ($this->has('role')) {
            $rules['role'] = 'in:user,admin';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'role.in' => 'O valor do campo :attribute não é compatível com o esperado.',
            'role.required' => 'Você não possui privilégios para esta requisição.',
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

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Requisição não autorizada.',
        ], 403));
    }

    protected function passedValidation()
    {
        $this->merge([
            'password' => bcrypt($this->input('password')),
        ]);
    }
}
