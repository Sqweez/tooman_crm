<?php

namespace App\Http\Requests\WorkingDay;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkingDayRequest extends FormRequest
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
            'opening_cash_in_hand' => 'integer'
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
}
