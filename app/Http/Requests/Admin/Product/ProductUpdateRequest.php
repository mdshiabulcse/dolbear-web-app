<?php

namespace App\Http\Requests\Admin\Product;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;

class ProductUpdateRequest extends FormRequest
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
    public function rules(Request $request)
    {
        $id = \Request()->id;

        return [
            'name'                      => 'required|max:190',
            'code'                      => 'required|max:190',
            'slug'                      => 'nullable|nullable|max:190|unique:products,slug,'.$id,
            'free_shipping'             => 'required|boolean',
            'category'                  => 'required',
            'brand'                  => 'required',
            'price'                     => 'numeric|required',
//            'sku'                       => 'required_without:has_variant',
//            'current_stock'             => 'numeric|required_without:has_variant',
            'unit'                      => 'required',
            'variant_sku.*' => [
                'required_if:has_variant,1',
            ],
            'video_url'                 => 'required_with:video_provider',
            'minimum_order_quantity'    => 'numeric|min:1',
            'low_stock_to_notify'       => 'numeric|min:0',
            'shipping_fee'              => 'required_if:shipping_type,flat_rate',
//            'estimated_shipping_days'   => 'numeric',
            'special_discount_period'   => 'required_with:special_discount_type',
            'special_discount'          => 'required_with:special_discount_type',

            'campaign_discount'         => 'required_with:campaign',
            'campaign_discount_type'    => 'required_with:campaign',
            'colors'    => 'required_if:has_variant,1|array|min:0',

            'question'=> 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'colors.required_if' => 'The colors field is required when product has variant.',
        ];
    }

//     protected function failedValidation(Validator $validator)
//     {
//         dd($validator->errors());
//     }
}
