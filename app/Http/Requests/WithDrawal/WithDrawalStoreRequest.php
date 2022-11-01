<?php

namespace App\Http\Requests\WithDrawal;

use App\Store;
use Illuminate\Foundation\Http\FormRequest;

class WithDrawalStoreRequest extends FormRequest
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
            'user_id' => 'required',
            'store_id' => 'required',
            'description' => 'sometimes',
            'amount' => 'required|min:1',
            'image' => 'required|file',
            'type_id' => 'required',
            'working_day_id' => 'sometimes'
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'working_day_id' => optional(Store::find($this->store_id)->activeWorkingDay)->id,
        ]);
    }
}
