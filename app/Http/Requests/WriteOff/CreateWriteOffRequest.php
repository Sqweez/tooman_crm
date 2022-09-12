<?php

namespace App\Http\Requests\WriteOff;

use Illuminate\Foundation\Http\FormRequest;

class CreateWriteOffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'sometimes',
            'revision_id' => 'sometimes',
            'products' => 'required|array',
            'store_id' => 'required',
            'user_id' => 'required',
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'user_id' => auth()->id(),
            'description' => $this->description ?: ''
        ]);
    }
}
