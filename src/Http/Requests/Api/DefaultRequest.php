<?php

namespace ViaRest\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DefaultRequest extends FormRequest implements CrudRequestInterface
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (config('APP_DEBUG') == true) {
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
        return [];
    }

}
