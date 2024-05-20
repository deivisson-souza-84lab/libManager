<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class StoreAuthorRequest extends FormRequest
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
      'name' => 'required|string|max:255|unique:authors',
      'date_of_birth' => 'required|date',
    ];
  }

  protected function prepareForValidation()
  {
    if ($this->has('name')) {
      $this->merge([
        'name' => trim($this->name),
      ]);
    }
  }

  public function messages()
  {
    return [
      'date_of_birth.date' => 'O campo :attribute deve ser uma data válida no formato `Y-m-d` ou `d-m-Y`.',
    ];
  }

  public function attributes()
  {
    return [
      'name' => 'nome',
      'date_of_birth' => 'data de nascimento',
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

  protected function passedValidation()
  {
    $this->merge([
      'date_of_birth' => Carbon::parse($this->input('date_of_birth'))->format('Y-m-d'),
    ]);
  }
}
