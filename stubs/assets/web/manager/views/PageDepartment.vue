<template>
	<NewbieTable
		ref="list"
		:url="route('api.manager.department.items', { classify: 1 })"
		:columns="columns()"
		:page="false"
		:filter-form="false"
		:fetched="myFetched"
	>
		<template #functional>
			<NewbieButton v-if="$auth('api.manager.department.edit')" type="primary" icon="PlusOutlined" @click="onEdit(false)"
				>新增部门
			</NewbieButton>
		</template>
	</NewbieTable>
	<NewbieModal v-model:visible="state.showDepartmentEditor" title="部门编辑">
		<NewbieEdit
			ref="edit"
			:fetch-url="state.url"
			:auto-load="!!state.url"
			:submit-url="route('api.manager.department.edit')"
			:card-wrapper="false"
			full-width
			:form="getForm()"
			:on-close="closeEditor"
			:submit-disabled="!$auth('api.manager.department.edit')"
			@after-success="closeEditor(true)"
		/>
	</NewbieModal>
</template>

<script setup>
import { NewbieTable, NewbieButton, NewbieEdit, NewbieModal, useTableActions } from "@web/components"
import { useFetch } from "@/js/hooks/web/network"
import { useModalConfirm } from "@/js/hooks/web/interact"
import { useProcessStatusSuccess } from "@/js/hooks/web/form"
import { message } from "ant-design-vue"
import { inject, reactive, ref } from "vue"

const list = ref(null)
const edit = ref(null)

const route = inject("route")

const state = reactive({
	showDepartmentEditor: false,
	options: [],
	url: "",
})

const myFetched = (res) => {
	state.options = res.result
	return {
		items: res.result.map((item) => {
			item.key = item.id
			item.children = item.children
				? item.children.map((child) => {
						child.key = child.id
						return child
				  })
				: null

			return item
		}),
	}
}

const getForm = () => {
	return [
		{
			key: "name",
			title: "部门名称",
			required: true,
		},
		{
			key: "parent_id",
			title: "上级部门",
			type: "tree-select",
			options: state.options,
			defaultProps: {
				treeNodeFilterProp: "name",
				fieldNames: {
					children: "children",
					label: "name",
					value: "id",
				},
			},
			tips: "留空代表顶级部门",
		},
		{
			key: "description",
			title: "部门描述",
			type: "textarea",
		},
		{
			key: "sort_order",
			title: "排序",
			type: "number",
			defaultProps: {
				min: 0,
			},
			position: "right",
			tips: "数字越大越靠前",
		},
		{
			key: "is_active",
			title: "显示状态",
			type: "switch",
			options: ["显示", "隐藏"],
			defaultValue: true,
			position: "right",
		},
	]
}

const onEdit = (item) => {
	state.url = item ? route("api.manager.department.item", { id: item.id }) : ""
	state.showDepartmentEditor = true
}

const closeEditor = (isRefresh) => {
	if (isRefresh) {
		list.value.doFetch()
	}
	state.showDepartmentEditor = false
}

const onDelete = (item) => {
	const modal = useModalConfirm(
		`您确认要删除 ${item.name} 吗？`,
		async () => {
			try {
				const res = await useFetch().post(route("api.manager.department.delete"), { id: item.id })
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

const columns = () => {
	return [
		{
			title: "部门名称",
			width: 200,
			dataIndex: "name",
			key: "name",
		},
		{
			title: "部门描述",
			width: 200,
			dataIndex: "description",
			key: "description",
		},
		{
			title: "排序",
			width: 50,
			dataIndex: "sort_order",
			key: "sort_order",
		},
		{
			title: "显示状态",
			key: "is_active",
			width: 80,
			customRender({ record }) {
				return useTableActions({
					type: "a-tag",
					success: record.is_active,
					name: record.is_active ? "显示" : "隐藏",
				})
			},
		},
		{
			title: "操作",
			width: 160,
			key: "operation",
			align: "center",
			fixed: "right",
			customRender({ record }) {
				return useTableActions([
					{
						name: "编辑",
						props: {
							icon: "EditOutlined",
							size: "small",
							auth: "api.manager.department.edit",
						},
						action() {
							onEdit(record)
						},
					},
					{
						name: "删除",
						props: {
							icon: "DeleteOutlined",
							size: "small",
							auth: "api.manager.department.delete",
						},
						action() {
							onDelete(record)
						},
					},
				])
			},
		},
	]
}
</script>
