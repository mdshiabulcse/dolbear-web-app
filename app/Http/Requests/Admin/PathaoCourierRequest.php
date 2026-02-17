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
            'name' =>'required|string|min:3|max:100',
            'phone' =>'required|string|min:11|max:14',
            'address' => 'required|string|min:10|max:220',
            'district' =>'sometimes|string|max:100',
            'thana' =>'sometimes|string|max:100',
            'city' =>'required|numeric|min:1',
            'zone' =>'required|numeric|min:1',
            'delivery_type' =>'required|numeric|in:12,48',
            'item_type' =>'required|numeric|in:1,2',
            'quantity' =>'required|numeric|min:1',
            'weight' =>'required|numeric|min:0.5|max:10',
            'amount_to_collect' =>'required|numeric|min:0',
            'item_description' =>'sometimes|string|max:255',
            'products' =>'sometimes|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.min' => 'Recipient name must be at least 3 characters.',
            'name.max' => 'Recipient name must not exceed 100 characters.',
            'phone.min' => 'Phone number must be at least 11 digits.',
            'phone.max' => 'Phone number must not exceed 14 digits.',
            'address.min' => 'Address must be at least 10 characters.',
            'address.max' => 'Address must not exceed 220 characters.',
            'delivery_type.in' => 'Delivery type must be 12 (On Demand) or 48 (Normal).',
            'item_type.in' => 'Item type must be 1 (Document) or 2 (Parcel).',
            'weight.min' => 'Weight must be at least 0.5 kg.',
            'weight.max' => 'Weight must not exceed 10 kg.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Decode products JSON if present
        $data = [
            'delivery_type' => (int) $this->delivery_type,
            'item_type' => (int) $this->item_type,
            'quantity' => (int) $this->quantity,
            'weight' => (float) $this->weight,
            'amount_to_collect' => (float) $this->amount_to_collect,
        ];

        if ($this->has('products') && is_string($this->products)) {
            $data['products'] = json_decode($this->products, true) ?? [];
        }

        $this->merge($data);
    }

}