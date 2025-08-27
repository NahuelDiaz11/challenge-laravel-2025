<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_name' => 'required|string|min:1|max:255',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
      public function messages(): array
    {
        return [
            'client_name.required' => 'El nombre del cliente es obligatorio.',
            'client_name.string' => 'El nombre del cliente debe ser un texto.',
            'client_name.min' => 'El nombre del cliente no puede estar vacío.',
            'client_name.max' => 'El nombre del cliente no puede superar los 255 caracteres.',

            'items.required' => 'Debes agregar al menos un item al pedido.',
            'items.array' => 'Los items deben enviarse como un arreglo.',
            'items.min' => 'Debes agregar al menos un item al pedido.',

            'items.*.description.required' => 'La descripción del item es obligatoria.',
            'items.*.description.string' => 'La descripción del item debe ser un texto.',
            'items.*.description.min' => 'La descripción del item no puede estar vacía.',
            'items.*.description.max' => 'La descripción del item no puede superar los 255 caracteres.',

            'items.*.quantity.required' => 'La cantidad del item es obligatoria.',
            'items.*.quantity.integer' => 'La cantidad del item debe ser un número entero.',
            'items.*.quantity.min' => 'La cantidad del item debe ser al menos 1.',

            'items.*.unit_price.required' => 'El precio unitario del item es obligatorio.',
            'items.*.unit_price.numeric' => 'El precio unitario del item debe ser un número.',
            'items.*.unit_price.min' => 'El precio unitario del item no puede ser negativo.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

}
