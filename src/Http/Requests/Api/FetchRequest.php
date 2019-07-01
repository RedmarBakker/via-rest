<?php

namespace ViaRest\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FetchRequest extends FormRequest implements CrudRequestInterface
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (env('APP_DEBUG') == true) {
            return true;
        }

        return auth()->check();
    }

    /**
     * Pass the failed authorization, we handle this in the AbstractRestController it self.
     * */
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
            'order_identifier' => 'max:255',
            'order_direction'  => 'max:4',
            'limit'            => 'integer',
            'relations'        => 'array'
        ];
    }

}
