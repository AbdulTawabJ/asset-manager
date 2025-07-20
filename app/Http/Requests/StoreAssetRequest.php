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
            'serial_no' => [
                'required',
                'string',
                Rule::unique('assets', 'serial_no')->ignore($assetId),
            ],
            'date_of_purchase' => 'nullable|date',
            'type' => 'required|exists:asset_types,type',
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'location' => 'required|exists:locations,location',
            'owner' => 'nullable|exists:employees,file_no',
            'remarks' => 'nullable|string',
            'requires_it_remark' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'requires_it_remark' => $this->has('requires_it_remark'),
        ]);
    }
}
