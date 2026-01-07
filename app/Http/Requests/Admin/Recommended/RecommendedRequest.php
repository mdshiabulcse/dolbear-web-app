<?php

namespace App\Http\Requests\Admin\Recommended;

use Illuminate\Foundation\Http\FormRequest;

class RecommendedRequest extends FormRequest
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
            'name' => 'required',
            'banner' => 'nullable',
            'description' => 'nullable',
            'status' => 'nullable',
        ];
    }
}