<?php

namespace App\Http\Requests\Economia;

use Illuminate\Foundation\Http\FormRequest;

class DecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'provisional' => $this->boolean('provisional')
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => [
                'required',
                'string',
                'max:200'
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:3000'
            ],

            'tmar' => [
                'required',
                'numeric',
                'gt:0',
                'max:100',
                'decimal:0,4'
            ],

            'tasa_isr' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                'decimal:0,2'
            ],

            'provisional' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo' => 'decisión',
            'tmar' => 'TMAR',
            'tasa_isr' => 'tasa de ISR'
        ];
    }
}