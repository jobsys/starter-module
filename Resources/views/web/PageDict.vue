<template>
	<NewbieTable ref="list" :url="route('api.manager.starter.dict.items')" :columns="columns()" row-key="id">
		<template #functional>
			<NewbieButton type="primary" :icon="h(PlusOutlined)" @click="onEdit(false)">新增字典</NewbieButton>
		</template>
	</NewbieTable>
	<NewbieModal v-model:visible="state.showEditorModal" title="字典编辑" :width="600">
		<NewbieForm
			ref="edit"
			:fetch-url="state.url"
			:auto-load="!!state.url"
			:submit-url="route('api.manager.starter.dict.edit')"
			:card-wrapper="false"
			:form="getForm()"
			:close="closeEditorModal"
			@success="closeEditorModal(true)"
		/>
	</NewbieModal>

	<NewbieModal type="drawer" v-model:visible="state.showItemDrawer" title="字典项列表" :width="1000">
		<NewbieTable
			ref="itemList"
			:url="route('api.manager.starter.dict.item.items', { dictionary_id: state.currentDictItem.id })"
			:pagination="false"
			:filterable="false"
			:after-fetched="(res) => ({ items: res.result })"
			:columns="itemColumns()"
			row-key="id"
		>
			<template #functional>
				<NewbieButton type="primary" :icon="h(PlusOutlined)" @click="onEditItem(false)">新增字典项
				</NewbieButton>
			</template>
		</NewbieTable>
	</NewbieModal>

	<NewbieModal v-model:visible="state.showItemEditorModal" title="字典项编辑" :width="600">
		<NewbieForm
			ref="itemEdit"
			:submit-url="route('api.manager.starter.dict.item.edit')"
			:card-wrapper="false"
			:data="state.currentItem || null"
			:form="getItemForm()"
			:close="closeItemEditorModal"
			:process-submit-data="onBeforeSubmitItem"
			@success="closeItemEditorModal(true)"
		/>
	</NewbieModal>
</template>

<script setup>
import {useTableActions} from "jobsys-newbie"
import {useFetch, useModalConfirm, useProcessStatusSuccess} from "jobsys-newbie/hooks"
import {message} from "ant-design-vue"
import {h, inject, reactive, ref} from "vue"
import {DeleteOutlined, EditOutlined, ExportOutlined, OrderedListOutlined, PlusOutlined} from "@ant-design/icons-vue"

const list = ref(null)
const edit = ref(null)
const itemList = ref(null)
const itemEdit = ref(null)

const route = inject("route")

const state = reactive({
	showEditorModal: false,
	showItemDrawer: false,
	showItemEditorModal: false,
	currentDictItem: {},
	currentItem: {},
	url: "",
})

const getForm = () => {
	return [
		{
			key: "display_name",
			title: "字典名称",
			required: true,
		},
		{
			key: "name",
			title: "字典标识",
			required: true,
		},
		{
			key: "description",
			title: "字典描述",
		},
		{
			key: "is_active",
			title: "是否启用",
			type: "switch",
			defaultValue: true,
		},
	]
}

const getItemForm = () => {
	return [
		{
			key: "display_name",
			title: "字典项名称",
			required: true,
		},
		{
			key: "value",
			title: "字典项值",
			required: true,
		},
		{
			key: "is_active",
			title: "是否启用",
			type: "switch",
			defaultValue: true,
		},
		{
			key: "sort_order",
			title: "排序",
			type: "number",
			defaultValue: 0,
			help: "数字越大越靠前",
		},
	]
}

const onEdit = (item) => {
	state.url = item ? route("api.manager.starter.dict.item", {id: item.id}) : ""
	state.showEditorModal = true
}

const onView = (item) => {
	state.currentDictItem = item
	state.showItemDrawer = true
}

const onEditItem = (item) => {
	state.currentItem = item
	state.showItemEditorModal = true
}

const closeEditorModal = (isRefresh) => {
	if (isRefresh) {
		list.value.doFetch()
	}
	state.showEditorModal = false
}

const closeItemEditorModal = (isRefresh) => {
	if (isRefresh) {
		itemList.value.doFetch()
	}
	state.showItemEditorModal = false
}

const onDelete = (item) => {
	const modal = useModalConfirm(
		`您确认要删除 ${item.display_name} 吗？`,
		async () => {
			try {
				const res = await useFetch().post(route("api.manager.starter.dict.delete"), {id: item.id})
				modal.destroy()
				useProcessStatusSuccess(res, () => {
					message.success("删除成功")
					list.value.doFetch()
				})
			} catch (e) {
				modal.destroy(e)
			}
		},
		true,
	)
}

const onDeleteItem = (item) => {
	const modal = useModalConfirm(
		`您确认要删除 ${item.display_name} 吗？`,
		async () => {
			try {
				const res = await useFetch().post(route("api.manager.starter.dict.item.delete"), {id: item.id})
				modal.destroy()
				useProcessStatusSuccess(res, () => {
					message.success("删除成功")
					itemList.value.doFetch()
				})
			} catch (e) {
				modal.destroy(e)
			}
		},
		true,
	)
}

const onBeforeSubmitItem = ({formatForm}) => {
	formatForm.dictionary_id = state.currentDictItem.id
	return formatForm
}

const onExport = (record) => {
	window.open(route("export.manager.starter.dict", {id: record.id}))
}
/**
 * @return {Array.<TableColunmConfig>}
 */
const columns = () => {
	return [
		{
			title: "字典名称",
			width: 160,
			dataIndex: "display_name",
			filterable: true,
		},
		{
			title: "字典标识",
			width: 160,
			dataIndex: "name",
			filterable: true,
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
			customRender({record}) {
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
			customRender({record}) {
				return useTableActions([
					{
						name: "字典项",
						props: {
							icon: h(OrderedListOutlined),
							size: "small",
						},
						action() {
							onView(record)
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
}

const itemColumns = () => {
	return [
		{
			title: "字典项名称",
			width: 140,
			dataIndex: "display_name",
		},
		{
			title: "字典项值",
			width: 140,
			dataIndex: "value",
		},
		{
			title: "排序",
			width: 50,
			dataIndex: "sort_order",
		},
		{
			title: "是否启用",
			width: 100,
			key: "is_active",
			customRender({record}) {
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
			width: 120,
			key: "operation",
			align: "center",
			fixed: "right",
			customRender({record}) {
				return useTableActions([
					{
						name: "编辑",
						props: {
							icon: h(EditOutlined),
							size: "small",
						},
						action() {
							onEditItem(record)
						},
					},
					{
						name: "删除",
						props: {
							icon: h(DeleteOutlined),
							size: "small",
						},
						action() {
							onDeleteItem(record)
						},
					},
				])
			},
		},
	]
}
</script>
