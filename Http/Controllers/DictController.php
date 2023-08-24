<?php

namespace Modules\Starter\Http\Controllers;

use App\Exporters\DictExporter;
use App\Http\Controllers\BaseManagerController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Starter\Entities\Dictionary;
use Modules\Starter\Entities\DictionaryItem;

class DictController extends BaseManagerController
{
    public function pageDict()
    {
        return Inertia::render('PageDict@Starter');
    }

    public function edit(Request $request)
    {
        list($input, $error) = land_form_validate(
            $request->only(['id', 'name', 'display_name', 'description', 'is_active']),
            ['name' => 'bail|required|string', 'display_name' => 'bail|required|string'],
            ['display_name' => '显示名称', 'name' => '字典标识'],
        );

        if ($error) {
            return $this->message($error);
        }

        $unique = land_is_model_unique($input, Dictionary::class, 'name');

        if (!$unique) {
            return $this->message('同名字典标识已经存在');
        }

        $item = Dictionary::updateOrCreate(['id' => $input['id'] ?? 0], $input);

        log_access(isset($input['id']) && $input['id'] ? '修改字典' : '新增字典', $item->name);

        return $this->json($item);
    }

    public function items(Request $request)
    {
        $pagination = Dictionary::filterable([
            'name' => function(Builder $builder, $query){
                return $builder->where('name', $query['value']);
            }
        ])->orderBy('id', 'desc')->paginate();

        log_access('查询字典列表');

        return $this->json($pagination);
    }

    public function item(Request $request, $id)
    {

        $item = Dictionary::find($id);

        if (!$item) {
            return $this->message('字典不存在');
        }

        log_access('查询字典', $item->name);

        return $this->json($item);
    }

    public function delete(Request $request)
    {
        $id = $request->input('id', false);

        $item = Dictionary::find($id);

        if (!$item) {
            return $this->message('字典不存在');
        }

        DictionaryItem::where('dictionary_id', $id)->delete();

        $item->delete();

        log_access('删除字典', $item->name);

        return $this->json($item);
    }

    public function export(Request $request)
    {
        $id = $request->input('id', false);

        $item = Dictionary::find($id);

        if(!$item){
            return view('errors.404');
        }

        log_access('字典项导出', $id);

        return (new DictExporter($id))->download("{$item->display_name}字典项导出.xlsx");
    }


    public function itemEdit(Request $request)
    {
        list($input, $error) = land_form_validate(
            $request->only(['id', 'dictionary_id', 'display_name', 'value', 'is_active', 'sort_order']),
            ['dictionary_id' => 'bail|required|integer', 'display_name' => 'bail|required|string', 'value' => 'bail|required|string'],
            ['dictionary_id' => '字典', 'display_name' => '显示名称', 'value' => '字典值'],
        );

        if ($error) {
            return $this->message($error);
        }

        if (!land_is_model_unique($input, DictionaryItem::class, 'value', true, ['dictionary_id' => $input['dictionary_id']])) {
            return $this->message('同名字典值已经存在');
        }

        if (!land_is_model_unique($input, DictionaryItem::class, 'display_name', true, ['dictionary_id' => $input['dictionary_id']])) {
            return $this->message('同名显示名称已经存在');
        }

        $item = DictionaryItem::updateOrCreate(['id' => $input['id'] ?? 0], $input);

        log_access(isset($input['id']) && $input['id'] ? '修改字典项' : '新增字典项', $item->display_name);

        return $this->json($item);
    }


    public function itemItems(Request $request)
    {

        $dictionary_id = $request->input('dictionary_id', false);

        $items = DictionaryItem::where('dictionary_id', $dictionary_id)->orderBy('sort_order', 'desc')->get();

        log_access('查询字典值列表');

        return $this->json($items);
    }

    public function itemDelete(Request $request)
    {
        $id = $request->input('id', false);

        $item = DictionaryItem::find($id);

        if (!$item) {
            return $this->message('字典值不存在');
        }

        $item->delete();

        log_access('删除字典项', $item->display_name);

        return $this->json($item);
    }


}
