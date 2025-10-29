<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOutletRequest extends FormRequest
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
        $user = $this->user();
        $rules = [
            'code' => [
                'required',
                'string',
                'max:255',
            ],
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'mode' => 'required|string|in:pos,restaurant,minimarket',
            'settings' => 'nullable|array',
        ];

        // Handle tenant_id for superadmins
        if ($user->canAccessAllTenants()) {
            $rules['tenant_id'] = 'required|exists:tenants,id';
            $rules['code'][] = Rule::unique('outlets', 'code')->where(function ($query) {
                return $query->where('tenant_id', $this->input('tenant_id'));
            });
        } else {
            // Regular users: tenant_id auto-assigned, code unique within current tenant
            $rules['code'][] = Rule::unique('outlets', 'code')->where(function ($query) {
                return $query->where('tenant_id', app()->get('current_tenant')->id);
            });
        }

        return $rules;
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
            'tenant_id.required' => 'Please select a tenant.',
            'tenant_id.exists' => 'The selected tenant does not exist.',
        ];
    }
}
