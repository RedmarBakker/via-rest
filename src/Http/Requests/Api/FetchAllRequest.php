<?php

namespace ViaRest\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FetchAllRequest extends FormRequest implements CrudRequestInterface
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

    public function failedAuthorization()
    {
        return;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

        ];
    }
}
