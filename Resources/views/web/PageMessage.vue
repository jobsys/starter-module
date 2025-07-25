<template>
	<NewbieTable
		ref="tableRef"
		persistence
		row-selection
		:url="route('api.manager.starter.message-batch.items')"
		:columns="tableColumns()"
		class="hover-card"
		:table-props="{ size: 'small' }"
	>
		<template #functional>
			<NewbieButton v-if="$auth('api.manager.starter.message-batch.edit')" type="primary" :icon="h(PlusOutlined)" @click="onEdit()"
				>新增消息批次
			</NewbieButton>
		</template>
	</NewbieTable>

	<MessageSender
		ref="messageSenderRef"
		:channel-options="props.wechatChannelOptions"
		:before-submit="onBeforeSubmit"
		width="1400"
		@success="() => tableRef?.doFetch()"
	>
		<template #receivers="{ submitForm }">
			<div class="mb-6!" v-if="submitForm.receiver_type === 'user'">
				<a-card title="检索设定接收对象" size="small" class="hover-card mb-4!">
					<NewbieSearch
						ref="userSearchRef"
						class="bg-white p-4"
						:filterable-columns="userFilterableColumns"
						@search="onSearchUser"
					></NewbieSearch>
				</a-card>
				<div class="ml-[260px]">
					<span>
						当前条件共有 <span class="text-red-500 font-bold text-lg"> {{ state.totalReceiversCount }} </span> 名员工
					</span>
					<NewbieButton
						v-if="state.totalReceiversCount > 0"
						type="link"
						:icon="h(ArrowRightOutlined)"
						icon-position="right"
						@click="() => (state.showUserTableModal = true)"
						>查看列表
					</NewbieButton>
					<span v-else class="font-italic text-gray-500">请点击搜索进行员工检索</span>
				</div>
			</div>
		</template>
	</MessageSender>

	<NewbieModal v-model:visible="state.showUserTableModal" type="drawer" title="员工列表" width="1300">
		<NewbieTable
			ref="userTableRef"
			:filterable="false"
			:url="route('api.manager.user.items')"
			:columns="userTableColumns()"
			:extra-data="{ ...state.queryForm }"
			class="hover-card"
			:show-refresh="false"
			:table-props="{ size: 'small' }"
		>
		</NewbieTable>
	</NewbieModal>
</template>
<script setup>
import { h, inject, reactive, ref } from "vue"
import { ArrowRightOutlined, DeleteOutlined, EyeOutlined, PlusOutlined, StopOutlined } from "@ant-design/icons-vue"
import { useTableActions } from "jobsys-newbie"
import { useFetch, useModalConfirm, useProcessStatusSuccess } from "jobsys-newbie/hooks"
import { message, Tag } from "ant-design-vue"
import { useTableColumn } from "@/js/hooks/land"
import MessageSender from "@modules/Starter/Resources/views/web/components/MessageSender.vue"
import { router } from "@inertiajs/vue3"

const props = defineProps({
	departments: { type: Array, default: () => [] },
	wechatChannelOptions: { type: Array, default: () => ["database"] },
})

const route = inject("route")
const auth = inject("auth")

const tableRef = ref()
const messageSenderRef = ref()
const userTableRef = ref()
const userSearchRef = ref()

const state = reactive({
	totalReceiversCount: 0,
	queryForm: {},
	showStudentTableModal: false,
	showUserTableModal: false,
	userReceivers: [],
})

const statusOptions = [
	{ label: "待发送", value: "pending", color: "gray" },
	{ label: "发送中", value: "sending", color: "orange" },
	{ label: "已完成", value: "finished", color: "green" },
	{ label: "已取消", value: "cancelled", color: "black" },
	//{ label: "已暂停", value: "paused", color: "yellow" },
	{ label: "发送失败", value: "failed", color: "red" },
]

const onEdit = () => {
	state.totalReceiversCount = 0
	messageSenderRef.value?.open()
}

const onSearchUser = async (queryForm) => {
	state.queryForm = queryForm
	const res = await useFetch().post(route("api.manager.user.items"), { ...queryForm, page: 1, pageSize: 1000 })
	useProcessStatusSuccess(res, () => {
		state.totalReceiversCount = res.result.total
		state.userReceivers = res.result.data.map((item) => item.id)
	})
}

const onBeforeSubmit = ({ formatForm }) => {
	if (!state.totalReceiversCount) {
		message.warning("请先检索并设定接收对象")
		return false
	}
	formatForm.total = state.totalReceiversCount
	if (formatForm.receiver_type === "user") {
		formatForm.receivers = state.userReceivers
	}
	return formatForm
}

const onDelete = (item) => {
	const modal = useModalConfirm(
		`您确认要删除 ${item.title} 吗，已发消息不会被同步删除？`,
		async () => {
			try {
				const res = await useFetch().post(route("api.manager.starter.message-batch.delete", { id: item.id }))
				modal.destroy()
				useProcessStatusSuccess(res, () => {
					message.success("删除成功")
					tableRef.value.doFetch()
				})
			} catch (e) {
				modal.destroy(e)
			}
		},
		true,
	)
}

const onCancel = (item) => {
	const modal = useModalConfirm(
		`您确认要停止 ${item.title} 的发送吗？`,
		async () => {
			try {
				const res = await useFetch().post(route("api.manager.starter.message-batch.cancel", { id: item.id }))
				modal.destroy()
				useProcessStatusSuccess(res, () => {
					message.success("停止成功")
					tableRef.value.doFetch()
				})
			} catch (e) {
				modal.destroy(e)
			}
		},
		true,
	)
}

const userFilterableColumns = [
	{
		title: "用户名",
		key: "name",
	},
	{
		title: "姓名",
		key: "nickname",
		defaultCondition: "include",
	},
	{
		title: "工号",
		key: "work_num",
		defaultCondition: "include",
	},
	{
		title: "部门",
		type: "cascade",
		key: "department_id",
		options: props.departments,
		conditions: ["equal"],
		inputProps: {
			fieldNames: { label: "name", value: "id", children: "children" },
		},
	},
]

const userTableColumns = () => [
	{
		title: "用户名",
		dataIndex: "name",
		width: 100,
	},
	{
		title: "姓名",
		dataIndex: "nickname",
		width: 140,
	},
	{
		title: "工号",
		dataIndex: "work_num",
		width: 100,
	},
	{
		title: "账号状态",
		key: "is_active",
		width: 80,
		align: "center",
		customRender({ record }) {
			return useTableActions({
				type: "tag",
				name: record.is_active ? "正常" : "禁用",
				props: { color: record.is_active ? "green" : "red" },
			})
		},
	},
	{
		title: "微信绑定",
		key: "sns_count",
		width: 80,
		align: "center",
		customRender({ record }) {
			return useTableActions({
				type: "tag",
				name: record.sns_count ? "已绑定" : "未绑定",
				props: { color: record.sns_count ? "green" : "gray" },
			})
		},
	},
	{
		title: "手机号码",
		dataIndex: "phone",
		filterable: true,
		width: 130,
	},
	{
		title: "所属部门",
		key: "departments",
		width: 140,
		customRender({ record }) {
			return h(
				"div",
				{},
				record.departments.length
					? record.departments.map((item) => h(Tag, { color: "cyan" }, { default: () => item.name }))
					: h(Tag, {}, { default: () => "未分配" }),
			)
		},
	},
	{
		title: "职位",
		dataIndex: "position",
		width: 120,
		ellipsis: true,
	},
]

const tableColumns = () => [
	useTableColumn("消息标题", "title", 200, { minWidth: 200 }, { defaultCondition: "include" }),
	useTableColumn(
		"发送状态",
		"status",
		120,
		{
			customRender({ record }) {
				const statusOption = statusOptions.find((item) => item.value === record.status)
				return h(Tag, { color: statusOption?.color ?? "gray" }, () => statusOption?.label)
			},
			align: "center",
		},
		{
			type: "select",
			options: statusOptions,
		},
	),
	useTableColumn("发送渠道", "channels", 200, {
		minWidth: 200,
		customRender({ record }) {
			return record.channels.map((item) => {
				if (item === "work") {
					return h(Tag, { color: "blue" }, () => "企业微信")
				}
				if (item === "official") {
					return h(Tag, { color: "green" }, () => "公众号消息")
				}
				return h(Tag, { color: "orange" }, () => "站内通知")
			})
		},
	}),
	useTableColumn("对象人数", "total", "number"),
	useTableColumn("已发送数", "sent_count", "number"),
	useTableColumn("成功发送数", "success_count", "number"),
	useTableColumn("已读人数", "read_count", "number"),
	useTableColumn("开启状态", "is_active", "switch", { options: ["开启", "未开启"] }),
	useTableColumn("创建时间", "created_at", "datetime"),
	{
		title: "操作",
		width: 280,
		key: "operation",
		align: "center",
		fixed: "right",
		customRender({ record }) {
			const actions = []

			actions.push({
				name: "详情",
				props: {
					icon: h(EyeOutlined),
					size: "small",
				},
				action() {
					router.visit(route("page.manager.starter.message.detail", { id: record.id }))
				},
			})
			/*
						actions.push({
							name: "发送",
							props: {
								icon: h(PlayCircleOutlined),
								size: "small",
							},
							action() {},
						})*/

			if (auth("api.manager.starter.message-batch.pause")) {
				actions.push({
					name: "停止",
					props: {
						icon: h(StopOutlined),
						size: "small",
						danger: true,
					},
					action() {
						onCancel(record)
					},
				})
			}
			if (auth("api.manager.starter.message-batch.delete")) {
				actions.push({
					name: "删除",
					props: {
						icon: h(DeleteOutlined),
						size: "small",
						danger: true,
					},
					action() {
						onDelete(record)
					},
				})
			}

			return useTableActions(actions)
		},
	},
]
</script>
