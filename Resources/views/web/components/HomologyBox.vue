<template>
	<div class="homology-box">
		<NewbieModal v-model:visible="state.showHomologyModal" title="多语言管理" type="drawer" :width="1200">
			<NewbieTable
				ref="homologyTableRef"
				:pagination="false"
				:filterable="false"
				:after-fetched="onAfterHomologyTableFetch"
				:url="route(homologiesRoute, { id: state.currentHomology?.id })"
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

		<NewbieModal
			v-model:visible="state.showHomologyEditorModal"
			:width="width"
			title="页面编辑"
			:modal-props="{ bodyStyle: { height: '600px', overflow: 'auto' } }"
		>
			<NewbieForm
				ref="formHomologyRef"
				:submit-url="route(homologyEditRoute)"
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
import { h, reactive, inject, computed, ref } from "vue"
import { DeleteOutlined, DownOutlined, EditOutlined, GlobalOutlined } from "@ant-design/icons-vue"
import { useTableActions } from "jobsys-newbie"
import { useSystemStore } from "@manager/stores"
import { findIndex, isFunction } from "lodash-es"
import { useFetch, useLabelFromOptionsValue, useModalConfirm, useProcessStatusSuccess } from "jobsys-newbie/hooks"
import { message } from "ant-design-vue"

const props = defineProps({
	width: { type: [Number, String], default: 800 },
	homologiesRoute: { type: String, default: "" },
	homologyEditRoute: { type: String, default: "" },
	homologyItemRoute: { type: String, default: "" },
	homologyDeleteRoute: { type: String, default: "" },
	homologyColumns: { type: [Function, Array], default: () => [] },
	homologyFormItems: { type: [Function, Array], default: () => [] },
	actions: { type: [Function, Array], default: () => [] },
})

const route = inject("route")

const systemStore = useSystemStore()

const defaultLang = computed(() => systemStore.lang?.defaultLang)
const langOptions = computed(() => systemStore.lang?.langOptions)
const foreignLangOptions = computed(() => langOptions.value.filter((item) => item.value !== defaultLang.value))

const homologyTableRef = ref()

const state = reactive({
	showEditorModal: false,
	showGroupEditorModal: false,
	currentHomology: null,
	showHomologyEditorModal: false,
	currentLang: undefined,
	url: "",
})

const onAfterHomologyTableFetch = (res) => ({
	items: res.result,
})

const onHomologyEdit = ({ lang, item }) => {
	if (item) {
		state.url = route(props.homologyItemRoute, { id: item.id })
	} else {
		state.url = ""
	}

	state.currentLang = lang || item.lang || undefined
	state.showHomologyEditorModal = true
}

const closeHomologyEditor = (refresh) => {
	state.showHomologyEditorModal = false
	if (refresh) {
		homologyTableRef.value.doFetch()
	}
}

const onBeforeHomologyFormSubmit = ({ formatForm }) => {
	formatForm.lang = state.currentLang
	formatForm.homology_id = state.currentHomology?.id
	formatForm.slug = state.currentHomology?.slug
	return formatForm
}

const onDelete = (item) => {
	const modal = useModalConfirm(
		"是否删除当前记录",
		async () => {
			try {
				const res = await useFetch().post(route(props.homologyDeleteRoute), { id: item.id })
				modal.destroy()
				useProcessStatusSuccess(res, () => {
					message.success("删除成功")
					homologyTableRef.value.doFetch()
				})
			} catch (e) {
				modal.destroy(e)
			}
		},
		true,
	)
}

const getHomologyColumns = () => {
	let columns = isFunction(props.homologyColumns) ? props.homologyColumns(state.currentHomology) : props.homologyColumns

	columns = [
		{
			title: "语言版本",
			dataIndex: "lang",
			width: 120,
			customRender({ record }) {
				return h("span", {}, useLabelFromOptionsValue(record.lang, langOptions.value))
			},
		},
		...columns,
		{
			title: "操作",
			width: 160,
			key: "operation",
			align: "center",
			fixed: "right",
			customRender({ record }) {
				let actions = []

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

				if (props.actions && isFunction(props.actions)) {
					const externalActions = props.actions(record)
					actions = actions.concat(externalActions)
				} else if (props.actions.length) {
					actions = actions.concat(props.actions)
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

	return columns
}

const getHomologyForm = () => {
	const formItems = isFunction(props.homologyFormItems) ? props.homologyFormItems(state.currentHomology) : props.homologyFormItems

	formItems.unshift({
		title: "语言版本",
		key: "lang",
		type: "select",
		disabled: true,
		options: langOptions.value,
		defaultValue: state.currentLang,
	})

	const slugIndex = findIndex(formItems, { key: "slug" })

	if (slugIndex !== -1) {
		formItems[slugIndex].disabled = true
		formItems[slugIndex].defaultValue = state.currentHomology?.slug
	}

	return formItems
}

const useHomologyEntrance = (homology) => {
	state.currentHomology = homology
	state.showHomologyModal = true
}

const useHomologyAction = (actions, record) => {
	if (langOptions.value.length > 1 && record.lang === defaultLang.value) {
		actions.push({
			name: "多语言",
			props: {
				icon: h(GlobalOutlined),
				size: "small",
			},
			action() {
				useHomologyEntrance(record)
			},
		})
	}

	return actions
}

defineExpose({ useHomologyEntrance, useHomologyAction })
</script>
