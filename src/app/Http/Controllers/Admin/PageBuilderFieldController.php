<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * Class PageBuilderFieldController
 * @package Clevyr\PageBuilder\app\Http\Controllers\Admin
 */
class PageBuilderFieldController extends CrudController
{
    /**
     * @throws Exception
     */
    public function setup()
    {
        $this->crud->setModel(config('backpack.pagebuilder.page_model_class', 'Clevyr\PageBuilder\app\Models\Page'));
    }

    public function getField(Request $request, Response $response)
    {
        try {
            $data = [
                'uniq_id' => Str::uuid(),
                'fields' => [],
            ];

            $section = $request->get('section');

            foreach ($section['fields'] as $key => $field) {
                $test = view('crud::fields.' . $field['type'],
                    [
                        'field' => $field,
                        'crud' => $this->crud,
                    ])->render();

                dd($test);

                $data['fields'][$key] = View::make('crud::fields.' . $field['type'],
                    [
                        'field' => $field,
                        'crud' => $this->crud,
                    ])->render();
            }

            return response()->json($data);
        } catch (Exception $e) {
            if (config('app.env') === 'local') {
                dd($e);
            }

            return response([
                'success' => false,
            ], 500);
        }
    }
}
