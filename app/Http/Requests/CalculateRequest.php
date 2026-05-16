<?php

namespace App\Http\Requests;

use App\Enums\Scenario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price'    => ['required', 'numeric', 'min:1', 'max:999999999'],
            'scenario' => ['required', Rule::enum(Scenario::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'price.required'    => 'Please enter a purchase price.',
            'price.numeric'     => 'Purchase price must be a number.',
            'price.min'         => 'Purchase price must be at least £1.',
            'price.max'         => 'Purchase price must be below £1 billion.',
            'scenario.required' => 'Please select a buyer type.',
            'scenario.enum'     => 'Please select a valid buyer type.',
        ];
    }

    /** Return price as integer pence for the service layer. */
    public function pricePence(): int
    {
        return (int) round($this->validated('price') * 100);
    }
}
