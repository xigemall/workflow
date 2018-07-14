<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
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
        $fileTypes = ['jpg','png','jpeg','bmp','gif','svg'];
        return [
            'upFile'=>[
                'required',
                'file',
                'image:'.implode(',',$fileTypes),
            ]
        ];
    }

    public function attributes(){
        return [
            'upFile'=>'文件',
        ];
    }
}
