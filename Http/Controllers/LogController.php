<?php

namespace Modules\Starter\Http\Controllers;


use App\Http\Controllers\BaseManagerController;
use App\Models\User;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class LogController extends BaseManagerController
{

	public function pageLog()
	{
		return Inertia::render('PageLog@Starter');
	}


	public function items()
	{

		$builder = Activity::with(['causer:id,name']);
		$filters = request('newbieQuery');
		if ($filters) {
			if (is_string($filters)) {
				$filters = json_decode($filters, true);
			}
			foreach ($filters as $prop => $query) {

				if ($prop === 'event') {
					if ($query['value'] === 'view') {
						$builder = $builder->whereNull('event');
					} else {
						$builder = $builder->where('event', $query['value']);
					}
				} else if ($prop === 'causer_name') {
					$builder = $builder->whereHas('causer', function ($q) use ($query) {
						return $q->where('name', 'like', '%' . $query['value'] . '%');
					});
				} else {
					$builder = land_filterable($prop, $builder, $query);
				}
			}
		}

		if (auth()->user()->isSuperAdmin()) {
			$pagination = $builder
				->where(
					fn($query) => $query->where(fn($sub_query) => $sub_query->where('causer_type', '<>', 'user')
						->orWhere(fn($q) => $q->where('causer_type', User::class)->where('causer_id', '<>', config('conf.super_admin_id')))
					)
				)->latest()->paginate(request('page_size', 10));
		} else {
			$pagination = $builder->where('causer_type', User::class)->where('causer_id', auth()->id())->latest()->paginate(request('page_size', 10));
		}

		$pagination->transform(function (Activity $item) {
			$item->causer_name = $item->causer?->name ?? ' - ';
			$item->created_at_datetime = $item->created_at?->format('Y-m-d H:i:s');
			if ($item->subject_type) {
				$item->subject_name = $item->subject_type::getModelName();
			}
			return $item;
		});

		return $this->json($pagination);
	}
}
