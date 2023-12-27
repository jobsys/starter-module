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

	public function pageDictItem()
	{

		$slug = request()->input('slug');


		$dictionary = Dictionary::where('slug', $slug)->first();

		if (!$dictionary) {
			return Inertia::render('PageDictItem@Starter', [
				'error' => '内容不存在'
			]);
		}
		return Inertia::render('PageDictItem@Starter', [
			'dictionary' => $dictionary,
		]);
	}

	public function edit(Request $request)
	{
		list($input, $error) = land_form_validate(
			$request->only(['id', 'slug', 'name', 'description', 'is_active']),
			['slug' => 'bail|required|string', 'name' => 'bail|required|string'],
			['name' => '显示名称', 'slug' => '字典标识'],
		);

		if ($error) {
			return $this->message($error);
		}

		$unique = land_is_model_unique($input, Dictionary::class, 'slug');

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
			'slug' => function (Builder $builder, $query) {
				return $builder->where('slug', $query['value']);
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

		log_access('查询字典', $item->slug);

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

		log_access('删除字典', $item->slug);

		return $this->json($item);
	}

	public function export(Request $request)
	{
		$id = $request->input('id', false);

		$item = Dictionary::find($id);

		if (!$item) {
			return view('errors.404');
		}

		log_access('字典项导出', $id);

		return (new DictExporter($id))->download("{$item->name}字典项导出.xlsx");
	}


	public function itemEdit(Request $request)
	{
		list($input, $error) = land_form_validate(
			$request->only(['id', 'dictionary_id', 'name', 'parent_id', 'value', 'is_active', 'sort_order']),
			['dictionary_id' => 'bail|required|integer', 'name' => 'bail|required|string'],
			['dictionary_id' => '字典', 'name' => '显示名称'],
		);

		if ($error) {
			return $this->message($error);
		}

		$input['parent_id'] = $input['parent_id'] ?? null;

		// 如果没有 value 则直接将显示名称当作 value
		$input['value'] = $input['value'] ?? $input['name'];

		if (!land_is_model_unique($input, DictionaryItem::class, 'value', true, ['dictionary_id' => $input['dictionary_id'], 'parent_id' => $input['parent_id']])) {
			return $this->message('同名字典值已经存在');
		}

		if (!land_is_model_unique($input, DictionaryItem::class, 'name', true, ['dictionary_id' => $input['dictionary_id'], 'parent_id' => $input['parent_id']])) {
			return $this->message('同名显示名称已经存在');
		}

		$item = DictionaryItem::updateOrCreate(['id' => $input['id'] ?? 0], $input);

		log_access(isset($input['id']) && $input['id'] ? '修改字典项' : '新增字典项', $item->name);

		return $this->json($item);
	}


	public function itemItems(Request $request)
	{

		$dictionary_id = $request->input('dictionary_id', false);

		$items = DictionaryItem::whereNull('parent_id')->where('dictionary_id', $dictionary_id)->get()->map(function(DictionaryItem $item){
			$children = $item->getDescendants()->toTree()->toArray();
			if($children && count($children)){
				$item->{'children'} = $item->getDescendants()->toTree()->toArray();
			}
			return $item;
		})->toArray();

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

		log_access('删除字典项', $item->name);

		return $this->json($item);
	}


}
