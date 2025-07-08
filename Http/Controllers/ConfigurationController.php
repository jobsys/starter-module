<?php

namespace Modules\Starter\Http\Controllers;

use App\Http\Controllers\BaseManagerController;
use Illuminate\Http\Request;

class ConfigurationController extends BaseManagerController
{
	public function items(Request $request)
	{
		$module = $request->input('module', false);
		$group = $request->input('group', false);
		$name = $request->input('name', false);
		$lang = $request->input('lang', false);

		$items = configuration_get($module, $group, $name, $lang);
		return $this->json($items);
	}

	public function edit(Request $request)
	{

		list($input, $error) = land_form_validate(
			$request->only(['configurations']),
			[
				'configurations' => 'bail|required|array'
			],
			[
				'configurations' => '配置项',
			],
		);

		if ($error) {
			return $this->message($error);
		}

		foreach ($input['configurations'] as $configuration) {
			configuration_save($configuration);
		}

		log_access("编辑{$input['configurations'][0]['module']}配置项");

		return $this->json();

	}
}
