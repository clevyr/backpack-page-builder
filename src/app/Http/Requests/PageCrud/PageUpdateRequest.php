<?php

namespace Clevyr\PageBuilder\app\Http\Requests\PageCrud;

use Illuminate\Foundation\Http\FormRequest;

class PageUpdateRequest extends FormRequest
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
            'title' => 'required|max:200',
            'slug' => 'required|max:200',
            'page_view_id' => 'required',
            'sections' => 'sometimes|required'
        ];
    }

    public function messages()
    {
        return [
            'sections.required' => 'Please add sections to the layout.',
        ];
    }
}

