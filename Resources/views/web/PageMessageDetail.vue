<template>
	<BasePager
		:error="props.error"
		:page-title="props.pageTitle"
		:title-icon="useLucideIcon(MessageSquareShare)"
		:title-tag="find(statusOptions, { value: item.status })?.label"
		:title-tag-color="find(statusOptions, { value: item.status })?.color"
		:back="route('page.manager.starter.message')"
		:descriptions="descriptions"
		:description-column="2"
		:description-width="500"
		:statistics="statisticsItems"
		:statistics-width="700"
	>
		<a-card size="small" class="hover-card mb-4!" :title="props.item.title">
			<div>{{ props.item.content }}</div>
		</a-card>

		<NewbieTable
			ref="tableRef"
			row-selection
			:url="route('api.manager.starter.message.items', { batch_id: props.item.id })"
			:columns="tableColumns()"
			persistence
			method="post"
			class="hover-card"
			:table-props="{ size: 'small' }"
		>
		</NewbieTable>
	</BasePager>
</template>
<script setup>
import { h, inject } from "vue"
import { useLucideIcon } from "@manager/compositions/util"
import { MessageSquareShare } from "lucide-vue-next"
import BasePager from "@modules/Starter/Resources/views/web/components/BasePager.vue"
import { find } from "lodash-es"
import { Tag } from "ant-design-vue"
import { useTableColumn } from "@/js/hooks/land"
import { useDefaultStudentQuery } from "@manager/compositions/service"

const props = defineProps({
	error: { type: String, default: "" },
	pageTitle: { type: String, default: "" },
	item: { type: Object, default: () => ({}) },
	channelOptions: { type: Array, default: () => [] },
})
const route = inject("route")

const statusOptions = [
	{ label: "待发送", value: "pending", color: "gray" },
	{ label: "发送中", value: "sending", color: "orange" },
	{ label: "已完成", value: "finished", color: "green" },
	{ label: "已取消", value: "cancelled", color: "black" },
	{ label: "发送失败", value: "failed", color: "red" },
]

const messageStatusOptions = [
	{ label: "待发送", value: "pending", color: "gray" },
	{ label: "发送成功", value: "success", color: "green" },
	{ label: "发送失败", value: "failed", color: "red" },
]

const descriptions = [
	{
		label: "发送时间",
		value: () => {
			if (props.item.send_type === "schedule") {
				return h("span", {}, ["延迟发送: ", props.item.send_params?.send_at])
			}

			if (props.item.send_type === "cron") {
				return h("span", {}, ["循环定时发送: ", props.item.send_params?.cron])
			}

			return h("span", {}, "立即发送")
		},
	},
	{
		label: "发送渠道",
		value: () =>
			h(
				"div",
				{},
				props.item.channels.map((item) => {
					if (item === "work") {
						return h(Tag, { color: "blue" }, () => "企业微信")
					}
					if (item === "official") {
						return h(Tag, { color: "green" }, () => "公众号消息")
					}
					return h(Tag, { color: "orange" }, () => "站内通知")
				}),
			),
	},
	{ label: "创建时间", value: props.item?.created_at },
	{
		label: "开启状态",
		value: () => (props.item.is_active ? h(Tag, { color: "green" }, () => "已开启") : h(Tag, { color: "gray" }, () => "未开启")),
	},
]

const statisticsItems = [
	{
		title: "对象人数",
		component: "VueUiKpi",
		dataset: props.item.total,
	},
	{
		component: "VueUiSparkgauge",
		title: "已发送数",
		dataset: {
			value: props.item.sent_count,
			max: props.item.total,
			min: 0,
		},
	},
	{
		component: "VueUiSparkgauge",
		title: "成功发送数",
		dataset: {
			value: props.item.success_count,
			max: props.item.total,
			min: 0,
		},
	},
	{
		component: "VueUiSparkgauge",
		title: "已读人数",
		dataset: {
			value: props.item.read_count,
			max: props.item.total,
			min: 0,
		},
	},
]

const tableColumns = () => {
	let columns = [
		useTableColumn(
			"发送状态",
			"status",
			120,
			{
				customRender({ record }) {
					const messageStatusOption = messageStatusOptions.find((item) => item.value === record.status)
					return h(Tag, { color: messageStatusOption?.color ?? "gray" }, () => messageStatusOption?.label)
				},
				align: "center",
			},
			{
				type: "select",
				options: messageStatusOptions,
			},
		),
		useTableColumn(
			"消息渠道",
			"channel",
			100,
			{
				customRender({ record }) {
					const channelOption = find(props.channelOptions, { value: record.channel })
					if (!channelOption) {
						return h(Tag, {}, () => "未知渠道")
					}
					return h(Tag, { color: channelOption.color }, () => channelOption.label)
				},
			},
			{
				type: "select",
				options: props.channelOptions,
			},
		),
	]

	if (props.item.receiver_type.indexOf("student") > -1) {
		columns = columns.concat([
			...useDefaultStudentQuery("receiver"),
			useTableColumn("学号", ["receiver", "student_number"], 120),
			useTableColumn("姓名", ["receiver", "name"], 100),
			useTableColumn("校区", ["receiver", "campus_name"], 140),
			useTableColumn("学院", ["receiver", "college_name"], 160),
			useTableColumn("专业", ["receiver", "major_name"], 160),
			useTableColumn("班级", ["receiver", "stu_class_name"], 160),
			useTableColumn("学历", ["receiver", "major_level"], 100),
		])
	} else if (props.item.receiver_type.indexOf("user") > -1) {
		columns = columns.concat([useTableColumn("姓名", ["receiver", "name"], 100), useTableColumn("工号", ["receiver", "work_num"], 100)])
	}

	columns = columns.concat([useTableColumn("是否已读", "read_at", "switch"), useTableColumn("发送时间", "created_at", "datetime")])

	return columns
}
</script>
