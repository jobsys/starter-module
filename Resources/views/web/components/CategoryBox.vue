<template>
	<div class="category-box">
		<NewbieTable
			ref="tableRef"
			:pagination="false"
			:filterable="false"
			:after-fetched="onAfterTableFetch"
			:url="route('api.manager.starter.category.items', { module, group })"
			:columns="getColumns()"
		>
			<template #functional>
				<NewbieButton type="primary" :icon="h(PlusOutlined)" @click="onEdit(false)">新增分类</NewbieButton>
			</template>
		</NewbieTable>

		<NewbieModal v-model:visible="state.showEditorModal" title="分类编辑">
			<NewbieForm
				ref="formRef"
				:submit-url="route('api.manager.starter.category.edit')"
				:fetch-url="state.url"
				:auto-load="!!state.url"
				:card-wrapper="false"
				:form="getForm()"
				:form-props="{ labelCol: { span: 4 }, wrapperCol: { span: 19 } }"
				:close="closeEditor"
				:before-submit="onBeforeFormSubmit"
				@success="closeEditor(true)"
			/>
		</NewbieModal>

		<NewbieModal v-model:visible="state.showHomologyModal" title="分类多语言管理" type="drawer" :width="1200">
			<NewbieTable
				ref="homologyTableRef"
				:pagination="false"
				:filterable="false"
				:after-fetched="onAfterHomologyTableFetch"
				:url="route('api.manager.starter.category.homology', { id: state.currentHomology?.id })"
				:columns="getHomologyColumns()"
			>
				<template #functional>
					<a-dropdown>
						<template #overlay>
							<a-menu @click="({ key }) => onHomologyEdit({ lang: key })">
								<a-menu-item v-for="lang in foreignLangOptions" :key="lang.value">
									{{ lang.label }}
								</a-menu-item>
							</a-menu>
						</template>
						<a-button type="primary">
							新增语言版本
							<DownOutlined />
						</a-button>
					</a-dropdown>
				</template>
			</NewbieTable>
		</NewbieModal>

		<NewbieModal v-model:visible="state.showHomologyEditorModal" title="分类编辑">
			<NewbieForm
				ref="formHomologyRef"
				:submit-url="route('api.manager.starter.category.edit')"
				:fetch-url="state.url"
				:auto-load="!!state.url"
				:card-wrapper="false"
				:form="getHomologyForm()"
				:form-props="{ labelCol: { span: 4 }, wrapperCol: { span: 19 } }"
				:closable="false"
				:close="closeHomologyEditor"
				:before-submit="onBeforeHomologyFormSubmit"
				@success="closeHomologyEditor(true)"
			/>
		</NewbieModal>
	</div>
</template>
<script setup>
import { h, reactive, ref, inject, computed } from "vue"
import { DeleteOutlined, EditOutlined, GlobalOutlined, PlusOutlined, DownOutlined } from "@ant-design/icons-vue"
import { useTableActions } from "jobsys-newbie"
import useSystemStore from "@manager/stores/system"
import { useFetch, useModalConfirm, useProcessStatusSuccess } from "jobsys-newbie/hooks"
import { message } from "ant-design-vue"
import { useLabelFromOptionsValue } from "jobsys-newbie/hooks"

const systemStore = useSystemStore()

const props = defineProps({
	module: { type: String, default: "" },
	group: { type: String, default: "" },
})

const route = inject("route")

const tableRef = ref()
const homologyTableRef = ref()
const formRef = ref()
const formHomologyRef = ref()

const defaultLang = computed(() => systemStore.lang.defaultLang)
const langOptions = computed(() => systemStore.lang.langOptions)
const foreignLangOptions = computed(() => langOptions.value.filter((item) => item.value !== defaultLang.value))
const state = reactive({
	categoryOptions: [],
	showEditorModal: false,
	showHomologyModal: false,
	showHomologyEditorModal: false,
	currentHomology: null,
	currentLang: undefined,
	url: "",
})

const onAfterTableFetch = (res) => {
	state.categoryOptions = res.result
	return {
		items: res.result,
	}
}

const onAfterHomologyTableFetch = (res) => {
	return {
		items: res.result,
	}
}

const onEdit = ({ item }) => {
	if (item) {
		state.url = route("api.manager.starter.category.item", { id: item.id })
	} else {
		state.url = ""
	}
	state.showEditorModal = true
}

const onHomologyEdit = ({ lang, item }) => {
	if (item) {
		state.url = route("api.manager.starter.category.item", { id: item.id })
	} else {
		state.url = ""
	}

	state.currentLang = lang || undefined
	state.showHomologyEditorModal = true
}

const closeEditor = (refresh) => {
	state.showEditorModal = false
	if (refresh) {
		tableRef.value.doFetch()
	}
}

const closeHomologyEditor = (refresh) => {
	state.showHomologyEditorModal = false
	if (refresh) {
		homologyTableRef.value.doFetch()
	}
}

const onBeforeFormSubmit = ({ formatForm }) => {
	formatForm.module = props.module
	formatForm.group = props.group
	return formatForm
}

const onBeforeHomologyFormSubmit = ({ formatForm }) => {
	formatForm.module = props.module
	formatForm.group = props.group
	formatForm.lang = state.currentLang
	formatForm.homology_id = state.currentHomology?.id
	formatForm.slug = state.currentHomology?.slug
	return formatForm
}

const onDelete = (item) => {
	const modal = useModalConfirm(
		langOptions.value.length > 1 && item.lang === defaultLang.value
			? `该操作将会同时删除其它语言版本，您确认要删除 ${item.name} 吗？`
			: `您确认要删除 ${item.name} 吗？`,
		async () => {
			try {
				const res = await useFetch().post(route("api.manager.starter.category.delete"), { id: item.id })
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

const getHomologyForm = () => {
	return [
		{
			title: "分类名称",
			key: "name",
			required: true,
		},
		{
			title: "语言版本",
			key: "lang",
			type: "select",
			disabled: true,
			options: langOptions.value,
			defaultValue: state.currentLang,
		},
		{
			title: "上级分类",
			key: "parent_id",
			help: "留空为顶级分类",
			disabled: true,
			type: "cascade",
			options: state.categoryOptions,
			defaultProps: {
				fieldNames: { label: "name", value: "id", children: "children" },
			},
			defaultValue: state.currentHomology?.parent_id,
		},
		{
			title: "分类标识",
			key: "slug",
			disabled: true,
			help: "分类标识不能重复，如果不填，系统将会自动生成",
			defaultValue: state.currentHomology?.slug,
		},
	]
}

const getForm = () => [
	{
		title: "分类名称",
		key: "name",
		required: true,
	},
	{
		title: "上级分类",
		key: "parent_id",
		help: "留空为顶级分类",
		type: "tree-select",
		options: state.categoryOptions,
		width: 400,
		defaultProps: {
			treeNodeFilterProp: "name",
			fieldNames: { label: "name", value: "id", children: "children" },
		},
	},
	{
		key: "sort_order",
		title: "排序",
		type: "number",
		defaultProps: {
			min: 0,
		},
		position: "right",
		help: "数字越大越靠前",
	},
	{
		title: "分类标识",
		key: "slug",
		help: "分类标识不能重复，如果不填，系统将会自动生成",
	},
]

const getHomologyColumns = () => {
	return [
		{
			title: "语言版本",
			dataIndex: "lang",
			width: 120,
			customRender({ record }) {
				return h("span", {}, useLabelFromOptionsValue(record.lang, langOptions.value))
			},
		},
		{
			title: "分类名称",
			dataIndex: "name",
			width: 120,
		},
		{
			title: "分类标识",
			dataIndex: "slug",
			width: 120,
		},
		{
			title: "操作",
			width: 160,
			key: "operation",
			align: "center",
			fixed: "right",
			customRender({ record }) {
				const actions = []

				actions.push({
					name: "编辑",
					props: {
						icon: h(EditOutlined),
						size: "small",
					},
					action() {
						onHomologyEdit({ item: record })
					},
				})

				actions.push({
					name: "删除",
					props: {
						icon: h(DeleteOutlined),
						size: "small",
					},
					action() {
						onDelete(record)
					},
				})

				return useTableActions(actions)
			},
		},
	]
}

const getColumns = () => [
	{
		title: "分类名称",
		dataIndex: "name",
		width: 120,
		filterable: true,
	},
	{
		title: "分类标识",
		dataIndex: "slug",
		width: 120,
	},
	{
		title: "排序",
		width: 50,
		dataIndex: "sort_order",
		key: "sort_order",
	},
	{
		title: "操作",
		width: 160,
		key: "operation",
		align: "center",
		fixed: "right",
		customRender({ record }) {
			const actions = []

			actions.push({
				name: "编辑",
				props: {
					icon: h(EditOutlined),
					size: "small",
				},
				action() {
					onEdit({ item: record })
				},
			})

			if (langOptions.value.length > 1 && record.lang === defaultLang.value) {
				actions.push({
					name: "多语言",
					props: {
						icon: h(GlobalOutlined),
						size: "small",
					},
					action() {
						state.currentHomology = record
						state.showHomologyModal = true
					},
				})
			}

			actions.push({
				name: "删除",
				props: {
					icon: h(DeleteOutlined),
					size: "small",
				},
				action() {
					onDelete(record)
				},
			})

			return useTableActions(actions)
		},
	},
]
</script>
