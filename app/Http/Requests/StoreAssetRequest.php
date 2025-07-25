<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
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
        $assetId = $this->route('asset')?->id;

        return [
            'asset_tag' => [
                'required',
                'string',
                Rule::unique('assets', 'asset_tag')->ignore($assetId),
            ],
            'serial' => 'nullable|string|max:255',
            'date_of_purchase' => 'nullable|date',
            'date_of_issue' => 'nullable|date',
            'type' => 'required|exists:asset_types,type',
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'location' => 'required|exists:locations,location',
            'owner' => 'nullable|exists:employees,file_no',
            'remarks' => 'nullable|string',
            'requires_it_remark' => 'nullable|boolean',
            'status' => ['required', Rule::in(['None', 'Working', 'Damaged'])],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'requires_it_remark' => $this->has('requires_it_remark'),
        ]);

    }
}
