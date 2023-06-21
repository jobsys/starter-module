<template>
	<div :id="state.id" class="newbie-table-wrapper">
		<a-card size="small" class="newbie-table">
			<template v-if="title || $slots.title || $slots.extra" #title>
				<div class="newbie-table-extra-wrapper">
					<slot v-if="$slots.title" name="title"></slot>
					<template v-else>{{ title }}</template>
				</div>
			</template>

			<template v-if="$slots.extra" #extra>
				<div class="newbie-table-extra-wrapper">
					<slot name="extra"></slot>
				</div>
			</template>

			<div v-if="$slots.prepend" class="newbie-table-prepend-wrapper">
				<slot name="prepend"></slot>
			</div>
			<NewbieSearch
				v-if="filterForm"
				ref="searchRef"
				:columns="comCol"
				:filter-common-filter="filterCommonFilter"
				:filter-common-filter-not-use="filterCommonFilterNotUse"
				:filter-common-filter-hide="filterCommonFilterHide"
				:filter-column-index="filterColumnIndex"
				:auto-query="autoQuery"
				@search="onSearchNewbie"
			/>

			<div class="newbie-table-function-wrapper">
				<div v-if="$slots.functional" class="newbie-table-function-slot">
					<slot name="functional"></slot>
				</div>

				<div class="newbie-table-function-default">
					<a-button v-if="filterColumn" type="link" class="mg-r-5" @click="onOpenCustomColumns">自定义列 </a-button>
					<a-button class="newbie-table-fresh-btn" v-if="showRefresh" @click="doFetch">
						<template #icon>
							<NewbieIcon icon="SyncOutlined" />
						</template>
						刷新表格
					</a-button>
				</div>
				<div class="clearfix"></div>
			</div>

			<a-table
				v-bind="inTableProps"
				:row-selection="
					rowSelection ? { selectedRowKeys: state.tableKeySelection, preserveSelectedRowKeys: true, onChange: onSelectionChange } : null
				"
				:loading="state.tableLoading.loading"
				:data-source="state.items"
				:columns="state.myColumns"
				@resize-column="handleResizeColumn"
				@change="onChangeTable"
			/>

			<table v-if="$slots.append || page" style="width: 100%">
				<tr>
					<td v-if="$slots.append">
						<div class="newbie-table-append-wrapper">
							<slot name="append"></slot>
						</div>
					</td>
					<td v-if="page">
						<div class="newbie-table-page-wrapper">
							<a-pagination
								v-model:current="state.pagination.currentPage"
								v-model:pageSize="state.pagination.pageSize"
								:total="state.pagination.totalSize"
								v-bind="inPageProps"
								@change="onChangePage"
								@show-size-change="onShowSizeChangePage"
							/>
						</div>
					</td>
				</tr>
			</table>
		</a-card>

		<a-modal v-model:visible="state.customColumnVisible" title="自定义列" width="900px" @ok="onCustomSubmit">
			<a-checkbox-group v-model:value="state.columnKeyModal" class="width-100">
				<a-row :gutter="15">
					<a-col v-for="item in state.customColumns" :key="item.n_key" :span="6">
						<a-checkbox class="width-100 mg-b-5" :value="item.n_key">{{ item.title }}</a-checkbox>
					</a-col>
				</a-row>
			</a-checkbox-group>
			<a-divider />
			<a-button @click="onCustomSelectAll">全选</a-button>
			<a-button class="mg-l-5" @click="onCustomClearAll">全不选</a-button>
		</a-modal>
	</div>
</template>

<script>
import { h, ref } from "vue"
import { cloneDeep, isArray, isUndefined } from "lodash-es"
import { Dropdown, Image, Menu, Tag, Tooltip } from "ant-design-vue"

import NewbieIcon from "./NewbieIcon.vue"
import NewbieButton from "./NewbieButton.vue"

/**
 *
 * @param actions
 *  action :
 *  {
 *      type: 'Button',
 *      props: {type: 'primary', icon: 'click'},
 *      action: function(){},
 *      params: {},
 *      style: {},
 *      name: '点击'
 *  }
 */
export function useTableActions(actions) {
	if (!actions) return null
	if (!isArray(actions)) {
		actions = [actions]
	}
	const children = actions.map((action) => {
		let params = cloneDeep(action)
		let { type } = params
		const { name } = params

		if (!type || type === "button") {
			type = NewbieButton
			params.props = params.props || {}
			params.props.type = params.props.type || "text"
		}
		// 专为tag做一些样式处理
		if (type === "a-tag" || type === "tag") {
			type = Tag
			if (params.success) {
				params.color = "#19be6b"
				delete params.success
			}
		} else if (type === "icon") {
			type = NewbieIcon
		}
		delete params.type
		delete params.name
		if (params.props) {
			params = { ...params, ...params.props }
		}
		if (params.action) {
			params.onClick = params.action
		}
		if (params.tooltip) {
			const title = params.tooltip
			delete params.tooltip
			return h(
				Tooltip,
				{ title, transfer: true },
				{
					default: () =>
						h(type, params, {
							default: () => name,
						}),
				},
			)
		}
		if (params.children?.length) {
			const meneItems = params.children
			delete params.children

			// 用 Dropdown
			return h(
				Dropdown,
				{
					placement: "bottom",
				},
				{
					default: () =>
						h(
							NewbieButton,
							{ type: "text", ...(params.props || {}) },
							{ default: () => [name, h(NewbieIcon, { icon: "DownOutlined" })] },
						),
					overlay: () =>
						h(
							Menu,
							{},
							{
								default: () =>
									meneItems.map((item) =>
										h(
											Menu.Item,
											{},
											{
												default: () =>
													h(
														NewbieButton,
														{
															type: "text",
															style: { textAlign: "left" },
															config: { block: true },
															onClick: item.action,
															...(item.props || {}),
														},
														{
															default: () => item.name,
														},
													),
											},
										),
									),
							},
						),
				},
			)
		}
		return h(type, params, {
			default: () => name,
		})
	})
	return h(
		"div",
		{ class: "table-actions-wrapper" },
		{
			default: () => children,
		},
	)
}

export function useTableImage(images, defaultIcon) {
	if (!images) {
		return h(
			"div",
			{
				style: {
					width: "48px",
					height: "48px",
					backgroundColor: "#AAA",
					display: "flex",
					alignItems: "center",
					justifyContent: "center",
					borderRadius: "4px",
				},
			},
			h(NewbieIcon, { icon: defaultIcon || "PictureOutlined", style: { color: "#FFF", fontSize: "24px" } }),
		)
	}
	if (!isArray(images)) {
		images = [images]
	}
	return images.map((img) => {
		const src = isUndefined(img.url) ? img : img.url

		return h(
			"div",
			{
				style: {
					width: "48px",
					height: "48px",
					backgroundColor: "#000",
					display: "flex",
					alignItems: "center",
					justifyContent: "center",
					borderRadius: "4px",
				},
			},
			h(Image, {
				src,
				width: 48,
				height: 48,
			}),
		)
	})
}
</script>

<script setup>
import { reactive, computed, onMounted, nextTick, inject, watchEffect } from "vue"
import { isFunction } from "lodash-es"
import { useFetch } from "@/js/hooks/web/network"
import { useProcessStatusSuccess } from "@/js/hooks/web/form"
import NewbieSearch from "./NewbieSearch.vue"

const props = defineProps({
	title: {
		// 列表的标题
		type: String,
		default: "",
	},
	showExtraArrow: {
		// 是否显示表头箭头
		type: Boolean,
		default: true,
	},
	filterForm: {
		// 是否使用表单搜索
		type: Boolean,
		default: true,
	},
	filterCommonFilter: {
		// 是否使用公共搜索条件
		type: Boolean,
		default: false,
	},
	filterCommonFilterNotUse: {
		// 过滤不需要的公共搜索
		type: Array,
		default() {
			return []
		},
	},
	filterCommonFilterHide: {
		// 隐藏部分公共搜索
		type: Array,
		default() {
			return []
		},
	},
	filterColumnIndex: {
		// 搜索排序
		type: Array,
		default() {
			return []
		},
	},
	filterColumn: {
		// 是否使用自定义列
		type: Boolean,
		default: true,
	},
	tableProps: {
		// 原生表格属性
		type: Object,
		default() {
			return {}
		},
	},
	tableEvents: {
		// 原生表格事件
		type: Object,
		default() {
			return {}
		},
	},
	page: {
		// 是否有翻页
		type: Boolean,
		default: true,
	},
	pageProps: {
		// 原生翻页属性
		type: Object,
		default() {
			return {}
		},
	},
	pageKey: {
		// 发起请求时页码的 Key
		type: String,
		default: "page",
	},
	pageSizeKey: {
		// 发起请求时 PageSize 的 Key
		type: String,
		default: "page_size",
	},
	pageEvents: {
		// 原生页面事件
		type: Object,
		default() {
			return {}
		},
	},
	autoQuery: {
		// 是否在搜索条件变化时自动搜索
		type: Boolean,
		default: false,
	},
	url: {
		// 表格数据请求 URL
		type: String,
		default: "",
	},
	fetched: {
		// 请求后 Res 的处理方法, 如有 url， 则该方法必须传
		type: Function,
		default: null,
	},
	columns: {
		// 表格列定义
		type: [Array, Function],
		default() {
			return []
		},
	},
	rowSelection: {
		// 选择功能的配置
		type: [Boolean, Object],
		default: false,
	},
	rowKey: {
		// 数据值需要指定 key 值，当用rowSelection时一定要指定这个KEY
		type: [String, Function],
		default: "id",
	},
	formData: {
		// 表格数据
		type: Array,
		default() {
			return []
		},
	},
	expandRender: {
		// 展开的行
		type: [String, Function],
		default: "",
	},
	expandedRowKeys: {
		// 展开的行
		type: Array,
		default: () => [],
	},
	extraData: {
		// 请求数据时额外提交的参数
		type: Object,
		default() {
			return {}
		},
	},
	showRefresh: {
		// 是否显示刷新按钮
		type: Boolean,
		default: true,
	},
})

const emits = defineEmits(["fetch"])

const searchRef = ref(null)

const commonTableFetched = inject("NewbieTableCommonFetched")

const state = reactive({
	customColumns: [],
	myColumns: [],
	temporary: {}, // 用于存放一些临时数据
	tableLoading: { loading: false }, // 翻页Loading
	customColumnVisible: false,
	columnKeyModal: [],
	pagination: {
		// 翻页数据
		totalSize: 0,
		currentPage: 1,
		pageSize: 10,
	},
	id: "",
	tableSelection: [], // 给外部用
	tableKeySelection: [], // 内部用
	items: [], // 表格内容
	columnKey: {}, // 自定义列
	fetchQueue: [], // 请示队列，以最后那个为准
})

const inTableProps = ref({
	bordered: true,
	pagination: false,
	size: "middle",
	scroll: {
		y: 570,
		x: 1000,
	},
	rowClassName(_record, index) {
		return index % 2 === 1 ? "newbie-table-striped" : null
	},
	...props.tableProps,
})

const inPageProps = {
	size: "small",
	showQuickJumper: true,
	showSizeChanger: true,
	showTotal: (total) => `共${total}条`,
	pageSizeOptions: ["10", "30", "50", "100"],
	...props.pageProps,
}

const getQueryForm = () => {
	return searchRef.value ? searchRef.value.getQueryForm() : {}
}

const getQueryData = () => {
	let params = getQueryForm()
	params = { ...params, ...props.extraData }
	if (props.page) {
		if (state.pagination.pageSize) {
			params[props.pageSizeKey] = state.pagination.pageSize
		}
		params[props.pageKey] = state.pagination.currentPage
	}
	return params
}

const fetchItems = async () => {
	const params = getQueryData()
	const type = new Date().getTime()
	state.fetchQueue.push(type)
	const res = await useFetch(state.tableLoading).get(props.url, { params })
	useProcessStatusSuccess(res, () => {
		// 根据队列决定是否处理数据
		if (!state.fetchQueue.length) {
			return
		}
		if (state.fetchQueue[state.fetchQueue.length - 1] === type) {
			state.fetchQueue = []
		}
		state.tableKeySelection = []
		state.tableSelection = []
		const fetched = props.fetched || commonTableFetched
		const result = fetched(res)
		if (props.page) {
			state.pagination.totalSize = result.totalSize
		}
		state.items = result.items
	})
}

const onOpenCustomColumns = () => {
	// 复制一份
	state.columnKeyModal = []
	Object.keys(state.columnKey).forEach((key) => {
		if (state.columnKey[key]) {
			state.columnKeyModal.push(key)
		}
	})
	state.customColumnVisible = true
}

const onCustomClearAll = () => {
	state.columnKeyModal = []
}
const onCustomSelectAll = () => {
	onCustomClearAll()

	Object.keys(state.columnKey).forEach((key) => {
		state.columnKeyModal.push(key)
	})
}

const doFetch = () => {
	if (props.url) {
		fetchItems()
	} else {
		emits("fetch")
	}
}

const onCustomSubmit = () => {
	Object.keys(state.columnKey).forEach((key) => {
		state.columnKey[key] = state.columnKeyModal.indexOf(key) > -1
	})
	state.customColumnVisible = false
}

const setQueryData = (key, value) => {
	if (searchRef.value) {
		searchRef.value.setQueryData(key, value)
	}
}

const onChangeTable = (pagination, filters, sorter) => {
	// 远程搜索，给这个KEY加上后缀以便后台辩认
	const value = sorter.order ? sorter.order.replace("end", "") : ""
	setQueryData(`${sorter.columnKey}_query_order`, value)
	state.pagination.currentPage = 1
	doFetch()
}
const onChangePage = () => {
	doFetch()
}
const onShowSizeChangePage = () => {
	doFetch()
}
const onSearchNewbie = () => {
	state.pagination.currentPage = 1
	doFetch()
}

const getKey = (item) => {
	let { key } = item
	if (item.dataIndex) {
		key = item.dataIndex
	}
	return key
}

const getMyColumnByRecursion = (arr) => {
	const result = []
	if (arr) {
		arr.forEach((it) => {
			const item = cloneDeep(it)
			const key = getKey(item)
			if (key && isUndefined(state.columnKey[key])) {
				state.columnKey[key] = true
			}
			if (!item.children) {
				if ((key && state.columnKey[key]) || !key) {
					result.push(item)
				}
			} else {
				const res = getMyColumnByRecursion([].concat(item.children))
				if (res.length) {
					item.children = res
					if ((key && state.columnKey[key]) || !key) {
						result.push(item)
					}
				}
			}
		})
	}
	return result
}

const getCustomColumnByRecursion = (arr) => {
	let result = []
	if (arr) {
		arr.forEach((it) => {
			const item = cloneDeep(it)
			item.n_key = getKey(item)
			if (!item.children) {
				result.push(item)
			} else {
				const res = getCustomColumnByRecursion(item.children)
				delete item.children
				result.push(item)
				result = result.concat(res)
			}
		})
	}

	return result
}

const setPageData = (total, currentPage, pageSize) => {
	if (!isUndefined(total)) {
		state.pagination.totalSize = total
	}
	if (!isUndefined(currentPage)) {
		state.pagination.currentPage = currentPage
	}
	if (!isUndefined(pageSize)) {
		state.pagination.pageSize = pageSize
	}
}
const getPageData = () => {
	return state.pagination
}
const getSelection = () => {
	return [].concat(state.tableSelection)
}

const getData = () => {
	return [].concat(state.items)
}

const getSearchLabel = () => {
	return searchRef.value ? searchRef.value.getSearchLabel() : {}
}

const setTableLoading = (loading) => {
	state.tableLoading.loading = loading
}

const comCol = computed(() => {
	let co = []
	if (isFunction(props.columns)) {
		co = props.columns()
	} else if (props.columns.length) {
		co = props.columns
	} else {
		co = props.tableProps.columns
	}
	return co
})

watchEffect(() => {
	const originColumns = comCol.value.filter((item) => {
		return !item.isOnlyForQuery
	})
	state.customColumns = getCustomColumnByRecursion([].concat(originColumns))
	// 显示列表数组
	state.myColumns = getMyColumnByRecursion([].concat(originColumns))
})

const handleResizeColumn = (w, col) => {
	col.width = w
}

const onSelectionChange = (selectedRowKeys, selectedRows) => {
	state.tableKeySelection = [].concat(selectedRowKeys)
	state.tableSelection = [].concat(selectedRows)
}

onMounted(() => {
	// 因为手动 fetch 需要调用 $ref 的 getQueryForm 等方法
	// 所以需要 mounted 后才执行 fetch
	if (props.url) {
		nextTick(() => {
			fetchItems()
		})
	} else {
		emits("fetch")
	}
	if (props.formData.length) {
		state.items = props.formData
	}
	if (props.expandRender) {
		inTableProps.value.expandedRowRender = props.expandRender
	}
	if (props.expandedRowKeys && props.expandedRowKeys.length) {
		inTableProps.value.expandedRowKeys = props.expandedRowKeys
	}
	if (props.rowKey) {
		inTableProps.value.rowKey = props.rowKey
	}
})

state.id = `newbieTable_${Math.round(Math.random() * 10000000)}`

defineExpose({ getData, setPageData, getPageData, getSelection, getSearchLabel, getQueryData, doFetch, setTableLoading })
</script>

<style lang="less" scoped>
@grey: #f5f5f5;

.newbie-table-wrapper {
	.newbie-query-form {
		margin-bottom: 10px;
		padding-bottom: 10px;
		border-bottom: 1px solid @grey;

		.ivu-form-item {
			margin-bottom: 16px;
		}
	}

	.newbie-table-prepend-wrapper {
		margin-bottom: 10px;
	}

	&.newbie-table-hidden {
		:deep(.newbie-table > .ant-card-body) {
			display: none;
		}

		.newbie-form-extra-arrow {
			transform: rotate(180deg);
		}
	}

	.newbie-hidden {
		display: none !important;
	}

	.newbie-table {
		border: none;

		:deep(.ant-card-head) {
			background: @grey;
		}

		.ivu-card-extra {
			top: 10px;
		}

		.ivu-card-body {
			padding: 10px;
		}

		.ivu-table td,
		.ivu-table th {
			height: 38px;
		}

		:deep(.newbie-table-striped td) {
			background-color: #fafafa;
		}

		.newbie-table-th-wrapper {
			display: flex;
			align-items: center;

			.ivu-poptip {
				margin-left: 10px;

				.ivu-poptip-inner {
					padding: 5px;
				}
			}

			&.active {
				.ivu-poptip-rel {
					.ivu-icon-funnel {
						// color: @blue;
					}
				}
			}
		}

		.ivu-table-cell-ellipsis {
			.ivu-poptip {
				.ivu-poptip-rel {
					max-width: 100%;
					word-break: keep-all;
					white-space: nowrap;
					overflow: hidden;
					text-overflow: ellipsis;
				}
			}
		}

		:deep(.ant-table-cell .ant-image) {
			display: flex;
			align-items: center;
			justify-content: center;
			overflow: hidden;
		}

		:deep(.ant-table-cell .table-actions-wrapper) {
			.ant-btn-text {
				border-right: 1px solid #999;
				border-radius: 0;

				&:last-child {
					border-right: none;
				}
			}
		}
	}

	.newbie-table-page-wrapper {
		text-align: right;
		padding: 10px 0 0;
	}

	.newbie-table-append-wrapper {
		padding: 10px 0 0;
	}

	.newbie-table-function-wrapper {
		padding-bottom: 10px;

		.newbie-table-function-default {
			float: right;
			text-align: right;
		}

		.newbie-table-function-slot {
			float: left;
		}
	}

	.newbie-form-extra-arrow {
		i {
			font-size: 23px;
			line-height: 1;
		}
	}
}
</style>
