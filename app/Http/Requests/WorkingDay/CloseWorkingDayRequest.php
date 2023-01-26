<?php

namespace App\Http\Requests\WorkingDay;

use Illuminate\Foundation\Http\FormRequest;

class CloseWorkingDayRequest extends FormRequest
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
            'working_day_id' => 'required',
            'user_id' => 'required',
            'closing_cash_in_hand' => 'required|integer|min:0',
            'kaspi_transfers_cash' => 'required|integer|min:0',
            'kaspi_terminal_cash' => 'required|integer|min:0',
            'jysan_transfers_cash' => 'required|integer|min:0',
            'cashless_payment' => 'required|integer|min:0',
            'hard_cash' => 'required|integer|min:0',
            'closed_at' => 'required',
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'user_id' => auth()->id(),
            'closed_at' => now()
        ]);
    }
}
