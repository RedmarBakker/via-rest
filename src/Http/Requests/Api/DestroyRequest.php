<?php

namespace ViaRest\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest implements CrudRequestInterface
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
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
            //
        ];
    }
}
