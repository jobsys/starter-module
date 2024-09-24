<?php

namespace Modules\Starter\Http\Controllers;

use App\Http\Controllers\BaseManagerController;
use Illuminate\Http\Request;
use Modules\Starter\Emnus\State;
use Modules\Starter\Entities\Category;

class CategoryController extends BaseManagerController
{

	public function items(Request $request)
	{
		$module = $request->input('module');
		$group = $request->input('group', false);

		$items = Category::filterable()->where('module', $module)->when($group, function ($query, $group) {
			return $query->where('group', $group);
		})->whereNull('homology_id')->whereNull('parent_id')->get();

		$items = land_get_closure_tree($items);

		return $this->json($items);
	}

	public function item(Request $request, $id)
	{
		$item = Category::find($id);

		return $this->json($item);
	}

	public function homologyItems(Request $request)
	{
		$id = $request->input('id');

		$items = Category::where('homology_id', $id)->get();

		return $this->json($items);
	}

	public function edit(Request $request)
	{

		list($input, $error) = land_form_validate(
			$request->only('id', 'parent_id', 'homology_id', 'lang', 'module', 'group', 'name', 'slug', 'sort_order'),
			[
				'name' => 'bail|required|string',
				'module' => 'bail|required|string',
				'group' => 'bail|required|string',
			],
			[
				'name' => '分类名称',
				'module' => '模块名称',
				'group' => '分组名称',
			]
		);

		if ($error) {
			return $this->message($error);
		}

		$unique = land_is_model_unique($input, Category::class, 'name', true, ['module' => $input['module'], 'group' => $input['group']]);

		if (!$unique) {
			return $this->message('该分类名称已经存在');
		}

		$input['lang'] = $input['lang'] ?? config('app.locale');

		if (isset($input['id']) && $input['id']) {
			$result = Category::where('id', $input['id'])->update($input);
		} else {
			if (!isset($input['slug']) || !$input['slug']) {
				if (!isset($input['homology_id']) || !$input['homology_id']) {
					$slug = collect(pinyin_abbr($input['name']))->join('');
					$index = 1;
					while (!land_is_model_unique(['slug' => $slug], Category::class, 'slug', true, [
						'module' => $input['module'],
						'group' => $input['group'],
					])) {
						$slug = $slug . $index;
						$index += 1;
					}
					$input['slug'] = $slug;
				} else {
					$homology = Category::where('homology_id', $input['homology_id'])->first();
					if ($homology) {
						$input['slug'] = $homology->slug;
					}
				}

			}

			$result = Category::updateOrCreate(['module' => $input['module'], 'group' => $input['group'], 'slug' => $input['slug'], 'lang' => $input['lang']], $input);

		}

		return $this->json(null, $result ? State::SUCCESS : State::FAIL);
	}

	public function delete(Request $request)
	{
		$id = $request->input('id');
		$item = Category::where('id', $id)->first();

		if (!$item) {
			return $this->json(null, State::NOT_ALLOWED);
		}

		if (!$item->homology_id) {
			Category::where('homology_id', $item->id)->delete();
		}


		$result = $item->delete();

		return $this->json(null, $result ? State::SUCCESS : State::FAIL);
	}
}
