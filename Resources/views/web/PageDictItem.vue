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
				<NewbieButton type="primary" :icon="h(PlusOutlined)" @click="onEditItem(false)"> ����{{ dictName }} </NewbieButton>
			</template>
		</NewbieTable>

		<NewbieModal v-model:visible="state.showItemEditorModal" :title="`${dictName}�༭`" :width="600">
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
	return props.dictionary?.name || "�ֵ���"
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
		// �������飬ֱ�ӷ���
		//state.expandedRowKeys.push(data.id)
		return { ...data, key: data.id }
	}

	return data.map((item) => {
		//state.expandedRowKeys.push(item.id)
		// Ϊÿ���������� key ����
		item.key = item.id

		// ����� children ���ԣ��ݹ���� addKeyToObjects �����ӽڵ�
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
			title: `${dictName.value}����`,
			required: true,
		},
		{
			key: "value",
			title: `${dictName.value}ֵ`,
			help: `���ձ�ʾ��ֵ��${dictName.value}������ͬ`,
		},
		{
			key: "parent_id",
			title: `�ϼ�${dictName.value}`,
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
			help: `���մ�������${dictName.value}`,
		},
		{
			key: "is_active",
			title: "�Ƿ�����",
			type: "switch",
			defaultValue: true,
		},
		{
			key: "sort_order",
			title: "����",
			type: "number",
			defaultValue: 0,
			help: "����Խ��Խ��ǰ",
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
		`��ȷ��Ҫɾ�� ${item.name} ��`,
		async () => {
			try {
				const res = await useFetch().post(route("api.manager.starter.dict.item.delete"), { id: item.id })
				modal.destroy()
				useProcessStatusSuccess(res, () => {
					message.success("ɾ���ɹ�")
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
			title: `${dictName.value}����`,
			width: 140,
			dataIndex: "name",
		},
		{
			title: `${dictName.value}ֵ`,
			width: 140,
			dataIndex: "value",
		},
		{
			title: "����",
			width: 50,
			dataIndex: "sort_order",
		},
		{
			title: "�Ƿ�����",
			width: 100,
			key: "is_active",
			customRender({ record }) {
				return useTableActions({
					type: "tag",
					name: record.is_active ? "����" : "����",
					props: {
						color: record.is_active ? "green" : "red",
					},
				})
			},
		},
		{
			title: "����",
			width: 120,
			key: "operation",
			align: "center",
			fixed: "right",
			customRender({ record }) {
				return useTableActions([
					{
						name: "�༭",
						props: {
							icon: h(EditOutlined),
							size: "small",
						},
						action() {
							onEditItem(record)
						},
					},
					{
						name: "ɾ��",
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