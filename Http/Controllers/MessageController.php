<?php


namespace Modules\Starter\Http\Controllers;

use App\Http\Controllers\BaseManagerController;
use App\Models\Department;
use Inertia\Inertia;
use Modules\Starter\Entities\Message;
use Modules\Starter\Entities\MessageBatch;
use Modules\Starter\Services\MessageService;

class MessageController extends BaseManagerController
{
	public function pageMessage()
	{
		$wechat_channel = config('conf.use_wechat_channel', '');
		$wechat_channel_options = explode(',', $wechat_channel);
		$departments = land_get_closure_tree(Department::whereNull('parent_id')->orderBy('sort_order', 'DESC')->get());


		return Inertia::render('PageMessage@Starter', [
			'wechatChannelOptions' => $wechat_channel_options,
			'departments' => $departments
		]);
	}

	public function pageMessageDetail($id, MessageService $service)
	{

		$item = MessageBatch::find($id);

		$channel_options = $service->getChannelOptions();

		return Inertia::render('PageMessageDetail@Starter', [
			'pageTitle' => '消息发送详情',
			'item' => $item,
			'channelOptions' => $channel_options
		]);
	}


	public function batchItems()
	{
		$pagination = MessageBatch::filterable()->latest()->paginate();
		return $this->json($pagination);
	}

	public function batchItem($id)
	{
		$item = MessageBatch::find($id);
		if (!$item) {
			return $this->message('消息批次不存在');
		}

		log_access('查看消息批次详情', $item);

		return $this->json($item);
	}

	public function batchEdit(MessageService $service)
	{
		[$input, $error] = land_form_validate(
			request()->only(['id', 'title', 'content', 'channels', 'receiver_type', 'total', 'receivers', 'send_type', 'send_params', 'remark', 'is_active']),
			[
				'title' => 'bail|required|string',
				'content' => 'bail|required|string',
				'channels' => 'bail|required|array',
				'receiver_type' => 'bail|required|string',
				'receivers' => 'bail|required|array',
				'send_type' => 'bail|required|string',
			],
			[
				'title' => '标题',
				'content' => '内容',
				'channels' => '发送渠道',
				'receiver_type' => '接收对象',
				'receivers' => '接收者',
				'send_type' => '发送类型'
			]
		);

		if ($error) {
			return $this->message($error);
		}

		if (empty($input['id'])) {
			$input['creator_id'] = auth()->id();
			$input['status'] = MessageBatch::STATUS_PENDING;
			$item = MessageBatch::create($input);
		} else {
			$item = MessageBatch::find($input['id']);

			if ($item->status !== MessageBatch::STATUS_PENDING || $item->status === MessageBatch::STATUS_FINISHED) {
				return $this->message('消息批次已发送，无法修改');
			}

			$item->update($input);
		}

		[, $error] = $service->createMessageBatchTask($item);
		if ($error) {
			return $this->message($error);
		}

		return $this->json();
	}

	public function batchDelete()
	{
		$id = request('id');

		$item = MessageBatch::find($id);

		if (!$item) {
			return $this->message('消息批次不存在');
		}

		$item->delete();

		return $this->json();
	}

	public function batchCancel()
	{
		$id = request('id');

		$item = MessageBatch::find($id);
		if (!$item) {
			return $this->message('消息批次不存在');
		}

		if ($item->status === MessageBatch::STATUS_FINISHED) {
			return $this->message('消息批次已结束，无法停止');
		}

		if ($item->status === MessageBatch::STATUS_FAILED) {
			return $this->message('消息批次发送失败，无须停止');
		}

		if ($item->status === MessageBatch::STATUS_FINISHED) {
			return $this->message('消息批次已发送完毕，无法停止');
		}

		$item->status = MessageBatch::STATUS_CANCELLED;
		$item->save();

		return $this->json();
	}

	public function messageItems()
	{
		$batch_id = request('batch_id');
		$channel = request('channel');

		$batch = MessageBatch::find($batch_id);

		$config = config("starter.message.receivers_query.{$batch->receiver_type}");

		$pagination = Message::filterable([], ['morph' => ['receiver' => [$config['morph']]]])
			->with(['receiver:' . implode(',', $config['attributes'])])
			->where('message_batch_id', $batch_id)
			->when($channel, fn($query) => $query->where('channel', $channel))
			->paginate();

		return $this->json($pagination);
	}

	public function messageConfig(MessageService $service)
	{
		$config = [
			'channels' => $service->getChannelOptions(),
			'receiver_types' => config('starter.message.receiver_type'),
		];

		return $this->json($config);
	}

}
