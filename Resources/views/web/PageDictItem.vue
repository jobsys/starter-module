<template>
	<div>
		<NewbieTable
			ref="tableRef"
			:url="route('api.manager.starter.dict.item.items', { dictionary_id: dictionary.id })"
			:pagination="false"
			:filterable="false"
			:after-fetched="onAfterFetched"
			:columns="itemColumns()"
		>
			<template #functional>
				<NewbieButton type="primary" :icon="h(PlusOutlined)" @click="onEditItem(false)"> 新增{{ dictName }} </NewbieButton>
			</template>
		</NewbieTable>

		<NewbieModal v-model:visible="state.showItemEditorModal" :title="`${dictName}编辑`" :width="600">
			<NewbieForm
				ref="editRef"
				:submit-url="route('api.manager.starter.dict.item.edit')"
				:card-wrapper="false"
				:data="state.currentItem || null"
				:form="getItemForm()"
				:close="closeItemEditorModal"
				:before-submit="onBeforeSubmitItem"
				@success="closeItemEditorModal(true)"
			/>
		</NewbieModal>
	</div>
</template>

<script setup>
import { useTableActions } from "jobsys-newbie"
import { useFetch, useModalConfirm, useProcessStatusSuccess } from "jobsys-newbie/hooks"
import { message } from "ant-design-vue"
import { computed, h, inject, reactive, ref } from "vue"
import { DeleteOutlined, EditOutlined, PlusOutlined } from "@ant-design/icons-vue"

const props = defineProps({
	error: { type: String, default: "" },
	dictionary: { type: Object, default: () => ({}) },
})

const tableRef = ref(null)
const editRef = ref(null)

const route = inject("route")

const dictName = computed(() => {
	return props.dictionary?.name || "字典项"
})

const state = reactive({
	expandedRowKeys: [],
	showItemEditorModal: false,
	currentItem: {},
	options: [],
	url: "",
})

const addKeyToObjects = (data) => {
	if (!Array.isArray(data)) {
		// 不是数组，直接返回
		//state.expandedRowKeys.push(data.id)
		return { ...data, key: data.id }
	}

	return data.map((item) => {
		//state.expandedRowKeys.push(item.id)
		// 为每个对象添加 key 属性
		item.key = item.id

		// 如果有 children 属性，递归调用 addKeyToObjects 处理子节点
		if (item.children && item.children.length > 0) {
			item.children = addKeyToObjects(item.children)
		}

		return item
	})
}

const onAfterFetched = (res) => {
	state.options = res.result
	return {
		items: addKeyToObjects(res.result),
	}
}

const getItemForm = () => {
	return [
		{
			key: "name",
			title: `${dictName.value}名称`,
			required: true,
		},
		{
			key: "value",
			title: `${dictName.value}值`,
			help: `留空表示该值与${dictName.value}名称相同`,
		},
		{
			key: "parent_id",
			title: `上级${dictName.value}`,
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
			help: `留空代表顶级${dictName.value}`,
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

const onEditItem = (item) => {
	state.currentItem = item
	state.showItemEditorModal = true
}

const closeItemEditorModal = (isRefresh) => {
	if (isRefresh) {
		tableRef.value.doFetch()
	}
	state.showItemEditorModal = false
}

const onDeleteItem = (item) => {
	const modal = useModalConfirm(
		`您确认要删除 ${item.name} 吗？`,
		async () => {
			try {
				const res = await useFetch().post(route("api.manager.starter.dict.item.delete"), { id: item.id })
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

const onBeforeSubmitItem = ({ formatForm }) => {
	formatForm.dictionary_id = props.dictionary.id
	return formatForm
}

const itemColumns = () => {
	return [
		{
			title: `${dictName.value}名称`,
			width: 140,
			dataIndex: "name",
		},
		{
			title: `${dictName.value}值`,
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
			width: 120,
			key: "operation",
			align: "center",
			fixed: "right",
			customRender({ record }) {
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
