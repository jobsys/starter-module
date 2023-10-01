<?php


namespace Modules\Starter\Http\Controllers;

use App\Http\Controllers\BaseManagerController;
use Illuminate\Http\Request;

class SystemController extends BaseManagerController
{
	public function settings(Request $request)
	{

		$settings = [
			'lang' => [
				'langOptions' => collect(config('starter.languages'))->map(function ($item, $key) {
					return ["label" => $item, "value" => $key];
				})->values(),
				'defaultLang' => config('starter.default_lang')
			]
		];
		return $this->json($settings);
	}
}
