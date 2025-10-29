<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOutletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage_outlet');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('outlets', 'code')
                    ->where(function ($query) {
                        return $query->where('tenant_id', app()->get('current_tenant')->id);
                    })
                    ->ignore($this->route('outlet')->id),
            ],
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'mode' => 'required|string|in:pos,restaurant,minimarket',
            'settings' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'The outlet code is required.',
            'code.unique' => 'This outlet code is already taken.',
            'name.required' => 'The outlet name is required.',
            'mode.required' => 'The outlet mode is required.',
            'mode.in' => 'The outlet mode must be one of: pos, restaurant, minimarket.',
        ];
    }
}
