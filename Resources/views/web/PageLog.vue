<template>
	<div>
		<NewbieTable ref="tableRef" :url="route('api.manager.starter.log.items')" :columns="tableColumns()"></NewbieTable>
		<NewbieModal v-model:visible="state.showDetailModal" title="日志详情" :width="1000">
			<a-descriptions bordered>
				<a-descriptions-item label="操作者">{{ state.currentItem.causer_name }}</a-descriptions-item>
				<a-descriptions-item label="操作描述">{{ state.currentItem.description }}</a-descriptions-item>
				<a-descriptions-item label="操作对象">
					{{
						state.currentItem.subject_id
							? `${state.currentItem.subject_name} #${state.currentItem.subject_id}`
							: `${state.currentItem.subject_name}`
					}}
				</a-descriptions-item>

				<a-descriptions-item label="IP">{{ state.currentItem.properties?.agent?.ip }}</a-descriptions-item>
				<a-descriptions-item label="浏览器">{{ state.currentItem.properties?.agent?.browser }} </a-descriptions-item>
				<a-descriptions-item label="操作系统">{{ state.currentItem.properties?.agent?.os }} </a-descriptions-item>
				<a-descriptions-item label="访问链接" :span="3">{{ state.currentItem.properties?.agent?.url }} </a-descriptions-item>
			</a-descriptions>
			<div class="mt-4" v-if="state.currentItem?.event === 'updated' && state.propData.length">
				<a-table
					:title="() => '更新属性'"
					:row-key="(record) => record.key"
					:columns="state.propColumns"
					:data-source="state.propData"
					:pagination="false"
				></a-table>
			</div>
		</NewbieModal>
	</div>
</template>

<script setup>
import { useTableActions } from "jobsys-newbie"
import { h, inject, reactive, ref } from "vue"
import { EyeOutlined } from "@ant-design/icons-vue"
import { Tag, Tooltip } from "ant-design-vue"
import { isNull } from "lodash-es"

const tableRef = ref()

const route = inject("route")

const state = reactive({
	showDetailModal: false,
	currentItem: {},
	propColumns: [
		{ title: "属性", dataIndex: "prop" },
		{ title: "原始值", dataIndex: "oldValue" },
		{ title: "新值", dataIndex: "newValue" },
	],
	propData: [],
})

/*const onExport = (record) => {
	window.open(route("export.manager.starter.dict", { id: record.id }))
}*/

const onView = (record) => {
	state.currentItem = record
	state.propData = []
	if (record.event === "updated" && record.properties?.attributes) {
		const data = []
		Object.keys(record.properties.attributes).forEach((key) => {
			data.push({
				key,
				prop: key,
				newValue: isNull(record.properties.attributes[key]) ? "null" : record.properties.attributes[key],
				oldValue: isNull(record.properties.old[key]) ? "null" : record.properties.old[key],
			})
		})
		state.propData = data
	}
	state.showDetailModal = true
}
/**
 *
 * @return {Array.<TableColunmConfig>}
 */
const tableColumns = () => [
	{
		title: "操作者",
		dataIndex: "causer_name",
		width: 100,
		align: "center",
		filterable: {
			key: "causer_name",
		},
	},
	{
		title: "操作描述",
		dataIndex: "description",
		width: 200,
		filterable: true,
	},
	{
		title: "操作对象",
		dataIndex: "subject_name",
		width: 100,
		customRender({ record }) {
			if (!record.subject_id) {
				return record.subject_name
			}
			return h("div", {}, [
				h("span", {}, record.subject_name),
				h(
					Tag,
					{
						color: "orange",
						class: "ml-2",
					},
					() => `#${record.subject_id}`,
				),
			])
		},
	},
	{
		title: "客户端信息",
		key: "client",
		width: 100,
		customRender({ record }) {
			return h(
				Tooltip,
				{ placement: "bottom" },
				{
					default: () => h("span", { class: "text-blue-500 cursor-pointer" }, record.properties?.agent?.ip || "未知IP"),
					title: () =>
						h("div", { class: "whitespace-nowrap" }, [
							h("div", { class: "whitespace-nowrap" }, `浏览器: ${record.properties?.agent?.browser || "未知"}`),
							h("div", { class: "whitespace-nowrap" }, `操作系统: ${record.properties?.agent?.os || "未知"}`),
						]),
				},
			)
		},
	},
	{
		title: "访问链接",
		dataIndex: ["properties", "agent", "url"],
		width: 200,
		ellipsis: true,
	},
	{
		title: "操作时间",
		dataIndex: "created_at_datetime",
		filterable: {
			type: "date",
			key: "created_at",
		},
		width: 160,
	},
	{
		title: "操作",
		width: 100,
		key: "operation",
		align: "center",
		fixed: "right",
		filterable: {
			title: "操作类型",
			key: "event",
			type: "select",
			options: [
				{ label: "查看", value: "view" },
				{ label: "创建", value: "created" },
				{ label: "更新", value: "updated" },
				{ label: "删除", value: "deleted" },
			],
		},
		customRender({ record }) {
			return useTableActions([
				{
					name: "查看",
					props: {
						icon: h(EyeOutlined),
						size: "small",
					},
					action() {
						onView(record)
					},
				},
			])
		},
	},
]
</script>
