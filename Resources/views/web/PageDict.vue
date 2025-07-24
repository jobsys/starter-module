<template>
	<NewbieTable ref="tableRef" :url="route('api.manager.starter.dict.items')" :columns="tableColumns()">
		<template #functional>
			<NewbieButton type="primary" :icon="h(PlusOutlined)" @click="onEdit(false)">新增字典</NewbieButton>
		</template>
	</NewbieTable>
	<NewbieModal v-model:visible="state.showEditorModal" title="字典编辑" :width="600">
		<NewbieForm
			ref="formRef"
			:fetch-url="state.url"
			:auto-load="!!state.url"
			:submit-url="route('api.manager.starter.dict.edit')"
			:card-wrapper="false"
			:form="formColumns()"
			:close="closeEditorModal"
			@success="closeEditorModal(true)"
		/>
	</NewbieModal>
</template>

<script setup>
import { useTableActions } from "jobsys-newbie"
import { useFetch, useModalConfirm, useProcessStatusSuccess } from "jobsys-newbie/hooks"
import { message } from "ant-design-vue"
import { h, inject, reactive, ref } from "vue"
import { DeleteOutlined, EditOutlined, ExportOutlined, OrderedListOutlined, PlusOutlined } from "@ant-design/icons-vue"
import { router } from "@inertiajs/vue3"

const tableRef = ref()
const formRef = ref()

const route = inject("route")

const state = reactive({
	showEditorModal: false,
	showItemEditorModal: false,
	url: "",
})

const formColumns = () => {
	return [
		{
			key: "name",
			title: "字典名称",
			required: true,
		},
		{
			key: "slug",
			title: "字典标识",
			required: true,
		},
		{
			key: "description",
			title: "字典描述",
		},
		{
			key: "is_cascaded",
			title: "是否级联",
			type: "switch",
			defaultValue: false,
		},
		{
			key: "is_active",
			title: "是否启用",
			type: "switch",
			defaultValue: true,
		},
	]
}

const onEdit = (item) => {
	state.url = item ? route("api.manager.starter.dict.item", { id: item.id }) : ""
	state.showEditorModal = true
}

const closeEditorModal = (isRefresh) => {
	if (isRefresh) {
		tableRef.value.doFetch()
	}
	state.showEditorModal = false
}

const onDelete = (item) => {
	const modal = useModalConfirm(
		`您确认要删除 ${item.name} 吗？`,
		async () => {
			try {
				const res = await useFetch().post(route("api.manager.starter.dict.delete"), { id: item.id })
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

const onExport = (record) => {
	window.open(route("export.manager.starter.dict", { id: record.id }))
}
/**
 * @return {Array.<TableColunmConfig>}
 */
const tableColumns = () => [
	{
		title: "字典名称",
		width: 160,
		dataIndex: "name",
		filterable: {
			defaultCondition: "include",
		},
	},
	{
		title: "字典标识",
		width: 160,
		dataIndex: "slug",
		filterable: {
			defaultCondition: "include",
		},
	},
	{
		title: "字典描述",
		width: 160,
		dataIndex: "description",
		ellipsis: true,
	},
	{
		title: "是否启用",
		width: 100,
		key: "is_active",
		customRender({ record }) {
			return useTableActions({
				type: "tag",
				name: record.is_active ? "启用" : "隐藏",
				props: {
					color: record.is_active ? "green" : "red",
				},
			})
		},
	},
	{
		title: "操作",
		width: 220,
		key: "operation",
		align: "center",
		fixed: "right",
		customRender({ record }) {
			return useTableActions([
				{
					name: "字典项",
					props: {
						icon: h(OrderedListOutlined),
						size: "small",
					},
					action() {
						router.visit(route("page.manager.starter.dict.item", { slug: record.slug }))
					},
				},
				{
					name: "导出",
					props: {
						icon: h(ExportOutlined),
						size: "small",
					},
					action() {
						onExport(record)
					},
				},
				{
					name: "更多",
					props: {},
					children: [
						{
							name: "编辑",
							props: {
								icon: h(EditOutlined),
								size: "small",
							},
							action() {
								onEdit(record)
							},
						},
						{
							name: "删除",
							props: {
								icon: h(DeleteOutlined),
								size: "small",
							},
							action() {
								onDelete(record)
							},
						},
					],
				},
			])
		},
	},
]
</script>
