<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PathaoCourierRequest extends FormRequest
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
            'orderId' =>'required|numeric|min:1',
            'name' =>'required',
            'phone' =>'required|numeric',
            'address' => 'required|string|min:10',
            'city' =>'required|numeric',
            'zone' =>'required|numeric',
            'delivery_type' =>'required|numeric',
            'item_type' =>'required|numeric',
            'quantity' =>'required|numeric',
            'weight' =>'required|numeric',
            'amount_to_collect' =>'required|numeric|min:0',
            'special_instruction' => 'nullable|string|max:500',
            'product_info' => 'nullable|string',
            'item_description' => 'nullable|string|max:500'
        ];
    }

}
