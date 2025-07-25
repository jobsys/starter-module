<?php

use App\Models\User;

return [
	'landing_page' => [
		User::class => '', // 'page.mobile.manager.message.detail',
	],
	'receiver_type' => [
		['label' => '用户', 'value' => 'user'],
	],
	'receivers_query' => [
		//由于消息和角色之间是多态关系，因此需要通过morph来查询
		
		'user' => [
			'morph' => User::class,
			'attributes' => ['id', 'nickname', 'work_num', 'name'],
			'query' => fn($batch) => User::whereIn('id', $batch->receivers)->with(['sns'])->get(['id'])
		],
	]
];
