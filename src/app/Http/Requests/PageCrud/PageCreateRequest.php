<?php

namespace Clevyr\PageBuilder\app\Http\Requests\PageCrud;

use Illuminate\Foundation\Http\FormRequest;

class PageCreateRequest extends FormRequest
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
            'name' => 'required|max:200',
            'title' => 'required|max:200',
            'slug' => 'required|max:200',
            'layout' => 'required'
        ];
    }
}

