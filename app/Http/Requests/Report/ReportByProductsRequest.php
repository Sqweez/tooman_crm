<?php

namespace App\Http\Requests\Report;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ReportByProductsRequest extends FormRequest
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
            'start' => 'required',
            'finish' => 'required',
            'user_id' => 'required',
            'seller_id' => 'sometimes',
            'store_id' => 'sometimes'
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'start' => Carbon::parse($this->start),
            'finish' => Carbon::parse($this->finish),
            'user_id' => auth()->id(),
        ]);
    }
}
