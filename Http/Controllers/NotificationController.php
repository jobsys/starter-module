<?php

namespace Modules\Starter\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NotificationController extends BaseController
{
    public function items(Request $request)
    {
        $type = $request->input('type', false);
        $page_size = $request->input('page_size', 10);

        if ($type == 'all') {
            $type = false;
        }

        $pagination = auth()->user()->notifications()->when($type, function ($query) use ($type) {
            $type = Str::ucfirst($type);
            $query->where('type', 'like', "%{$type}");
        })->paginate($page_size);

        foreach ($pagination->items() as &$item) {
            $item->{'created_at_datetime'} = $item->created_at->format('Y-m-d H:i');
        }


        return $this->json($pagination);
    }

    public function read($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return $this->json();
    }

    public function readAll(Request $request)
    {
        $type = $request->input('type', false);
        if ($type == 'all') {
            $type = false;
        }

        auth()->user()->unreadNotifications()->when($type, function ($query) use ($type) {
            $type = Str::ucfirst($type);
            $query->where('type', 'like', "%{$type}");
        })->update(['read_at' => now()]);

        return $this->json();
    }

    public function delete($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->delete();
        }
        return $this->json();
    }

    public function deleteAll(Request $request)
    {
        $type = $request->input('type', false);
        if ($type == 'all') {
            $type = false;
        }

        auth()->user()->notifications()->when($type, function ($query) use ($type) {
            $type = Str::ucfirst($type);
            $query->where('type', 'like', "%{$type}");
        })->delete();
        return $this->json();
    }

    public function brief(Request $request)
    {
        /**
         * @var Collection $notifications
         */
        $notifications = auth()->user()->unreadNotifications()->get(['id', 'type', 'read_at']);

        $result = [
            'all' => $notifications->count(),
            'notification' => $notifications->filter(function ($item) {
                return Str::endsWith($item->type, 'Notification');
            })->count(),
            'todo' => $notifications->filter(function ($item) {
                return Str::endsWith($item->type, 'Todo');
            })->count(),
        ];

        return $this->json($result);
    }
}
