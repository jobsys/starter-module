<script>
import { h as createElement, reactive, toRefs } from "vue"
import { Button, Checkbox, Col, DatePicker, Divider, Form, Input, Modal, MonthPicker, RangePicker, Row, Select, Tag, Tooltip } from "ant-design-vue"
import { isArray, isString } from "lodash-es"
import { getOptionsValue } from "@/js/utils"
import { useDateFormat, useDateUnix } from "@/js/hooks/web/datetime"
import NewbieIcon from "./NewbieIcon.vue"

export default {
	name: "NewbieSearch",
	components: {
		NewbieIcon,
	},
	props: {
		columns: {
			// 表格列定义
			type: [Array, Function],
			default() {
				return []
			},
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
		autoQuery: {
			// 是否在搜索条件变化时自动搜索
			type: Boolean,
			default: false,
		},
	},
	emits: ["search"],
	setup() {
		const state = reactive({
			temporary: {}, // 用于存放一些临时数据
			queryForm: {}, // 查询内容
			columnKey: {}, // 自定义列
			searchKey: {}, // 自定义搜索框
			showSearchLabel: [], // 显示具体搜索了什么内容
		})

		return {
			...toRefs(state),
		}
	},
	computed: {
		originSearchForm() {
			// 合并后的搜索数组
			return this.columns
				.filter((item) => {
					return item.isOnlyForQuery || item.nFilterType
				})
				.map((item) => {
					item.n_key = this.getKey(item)
					return item
				})
		},
		mySearchColumns() {
			// 显示的搜索数组
			const that = this
			const co = this.originSearchForm.filter((col) => {
				const key = that.getKey(col)
				if (key && typeof that.searchKey[key] === "undefined") {
					this.searchKey[key] = !col.nFilterHidden
				}

				return that.searchKey[key]
			})
			if (this.filterColumnIndex && this.filterColumnIndex.length) {
				const ind = this.filterColumnIndex
				co.sort((prev, next) => {
					const previewIndex = ind.indexOf(that.getKey(prev))
					const nextIndex = ind.indexOf(that.getKey(next))
					if ((previewIndex === nextIndex) === -1) {
						// 不在排序内，保存原位
						return 0
					}
					if (previewIndex === -1) {
						// 前者不在，则后者靠前
						return 1
					}
					if (nextIndex === -1) {
						// 后者不在，则前者靠前
						return -1
					}
					// 两者都在，正常比较
					return previewIndex - nextIndex
				})
			}
			return co
		},
	},
	mounted() {
		const searchKey = localStorage.getItem(this.getTableSearchName())
		if (searchKey) {
			this.searchKey = JSON.parse(searchKey)
		}
	},
	methods: {
		getTableSearchName() {
			return `newbieTableSearch-${location.pathname.split("/").pop()}`
		},
		getSearchLabel() {
			return this.showSearchLabel
		},
		getKey(item) {
			return item.nFilterKey || item.key || item.dataIndex
		},
		getQueryForm(isNotCreateLabel) {
			const params = { ...this.queryForm }
			const that = this
			Object.keys(params).forEach((key) => {
				// 搜索框不显示或数值为空时，过滤掉
				if (
					(typeof that.searchKey[key] !== "undefined" && !that.searchKey[key]) ||
					params[key] === "" ||
					params[key] === null ||
					params[key] === undefined ||
					(isArray(params[key]) && params[key].length === 0)
				) {
					delete params[key]
				}
			})

			if (!isNotCreateLabel) {
				this.createSearchLabel()
			}
			return params
		},
		setQueryData(key, value) {
			this.queryForm[key] = value
		},
		createSearchLabel() {
			const that = this
			const params = this.getQueryForm(true)
			if (params && that.mySearchColumns) {
				const showQueryLabel = []
				Object.keys(params).forEach((key) => {
					that.mySearchColumns.forEach((item) => {
						const itemKey = that.getKey(item)
						if (itemKey === key) {
							const title = item.nFilterTitle || item.title
							let value = params[key]
							if (item.nFilterType === "select") {
								const options = getOptionsValue(item.nFilterOptions)
								if (isArray(value)) {
									const valueArr = []
									options.forEach((it) => {
										if (value.indexOf(it.value) > -1) {
											valueArr.push(it.label)
										}
									})
									value = valueArr.join(",")
								} else {
									options.forEach((it) => {
										if (it.value === value) {
											value = it.label
										}
									})
								}
							} else if (item.nFilterType === "date") {
								const newV = []
								// 因为组件默认是YYYY-MM-DD
								const formatType = (item.nFilterProps && item.nFilterProps.format) || "YYYY-MM-DD"
								if (isArray(value)) {
									if (value[0]) {
										newV.push(useDateFormat(value[0], formatType))
									}
									if (value[1]) {
										newV.push(useDateFormat(value[1], formatType))
									}
									value = newV.join(" 至 ")
								} else {
									value = useDateFormat(value, formatType)
								}
							} else if (item.nFilterType === "address") {
								// value = NewbieAddress.getLocalName(value);
							}

							showQueryLabel.push({
								title,
								label: value,
							})
						}
					})
				})
				this.showSearchLabel = showQueryLabel
			}
		},
		doSearch() {
			this.createSearchLabel()
			this.$emit("search")
		},
	},
	render() {
		function makeDate(that, title, key, props) {
			const normalKey = `chosenDate${key}_normal`

			let dateName = DatePicker
			if (props) {
				if (props.type === "month") {
					dateName = MonthPicker
				} else if (props.type === "range") {
					dateName = RangePicker
				}
			}

			return createElement(dateName, {
				style: { width: "100%" },
				value: that.temporary[normalKey],
				allowClear: true,
				placeholder: title,
				onChange(val) {
					let newV = ""
					if (isArray(val)) {
						if (val[0] && val[1]) {
							newV = [useDateUnix(val[0]), useDateUnix(val[1])]
						}
					} else if (val) {
						newV = useDateUnix(val)
					}
					that.queryForm[key] = newV
					that.temporary[normalKey] = val
				},
				...(props || {}),
			})
		}

		function makeInput(that, title, key, props, value) {
			if (that.queryForm[key] === undefined && typeof value !== "undefined") {
				that.queryForm[key] = value
			}
			return createElement(Input, {
				value: that.queryForm[key],
				allowClear: true,
				placeholder: title,
				onInput(e) {
					that.queryForm[key] = e.target.value
				},
				onPressEnter(e) {
					if (that.autoQuery && e.keyCode === 13) {
						that.doSearch()
					}
				},
				...(props || {}),
			})
		}

		function makeTextarea(that, title, key, props, value) {
			if (that.queryForm[key] === undefined && typeof value !== "undefined") {
				that.queryForm[key] = value
			}
			return createElement(Input.TextArea, {
				value: that.queryForm[key],
				allowClear: true,
				placeholder: title,
				onInput: (e) => {
					that.queryForm[key] = e.target.value
				},
				...(props || {}),
			})
		}

		function makeSelect(that, title, key, options, props, style, callback) {
			let selectValue = null
			let isMultiple = false
			if (props && (props.mode === "multiple" || props.mode === "tags")) {
				isMultiple = true
				selectValue = []
			}
			const optionElements =
				options &&
				options.map((option) => {
					if (isString(option)) {
						option = {
							value: option,
							label: option,
						}
					}
					if (option.selected) {
						if (isMultiple) {
							selectValue.push(option.value)
						} else {
							selectValue = option.value
						}
					}
					return option
				})

			if (!that.temporary[`${key}_select_init`] && ((!isArray(selectValue) && selectValue) || (isArray(selectValue) && selectValue.length))) {
				that.queryForm[key] = selectValue
			}
			that.temporary[`${key}_select_init`] = true

			const selectProps = {
				style: { width: "100%", height: "32px", ...(style || {}) },
				value: that.queryForm[key],
				allowClear: true,
				placeholder: title,
				options: optionElements,
				onChange(val) {
					that.queryForm[key] = val
					if (that.autoQuery) {
						that.doSearch()
					}
					if (callback) {
						callback(val)
					}
				},
			}
			return createElement(Select, Object.assign(selectProps, props || {}))
		}

		function makeAddress() {}

		// 搜索操作栏
		function doSearchAction(that, formItems) {
			const acContent = [
				createElement(
					Button,
					{
						style: { marginRight: "5px" },
						type: "dashed",
						onClick: () => {
							Object.keys(that.queryForm).forEach((key) => {
								if (key.indexOf("_query_order") === -1) {
									// 忽略排序的字段
									that.queryForm[key] = undefined
								}
							})
							Object.keys(that.temporary).forEach((key) => {
								that.temporary[key] = undefined
							})
						},
					},
					{
						default() {
							return "清除"
						},
					},
				),
				createElement(
					Button,
					{
						type: "primary",
						style: { marginRight: "5px" },
						onClick: () => {
							that.doSearch()
						},
					},
					{
						default() {
							return "查询"
						},
					},
				),
				createElement(
					Tooltip,
					{
						title: "点击设置搜索选项",
					},
					{
						default() {
							return [
								createElement(
									Button,
									{
										type: "link",
										onClick() {
											// 复制一份
											that.temporary.searchKeyModal = []
											Object.keys(that.searchKey).forEach((key) => {
												if (that.searchKey[key]) {
													that.temporary.searchKeyModal.push(key)
												}
											})
											const modal = Modal.confirm({
												width: "900px",
												title: "自定义设置搜索选项",
												content() {
													const el = []

													that.originSearchForm.forEach((sea) => {
														const key = that.getKey(sea)
														const title = sea.nFilterTitle || sea.title
														if (key) {
															el.push(
																createElement(
																	Col,
																	{
																		span: 6,
																	},
																	{
																		default() {
																			return [
																				createElement(
																					Checkbox,
																					{
																						style: {
																							width: "100%",
																							marginBottom: "5px",
																						},
																						value: key,
																					},
																					{
																						default() {
																							return title
																						},
																					},
																				),
																			]
																		},
																	},
																),
															)
														}
													})

													return createElement("div", {}, [
														createElement(
															Checkbox.Group,
															{
																value: that.temporary.searchKeyModal,
																style: { width: "100%" },
																onChange(val) {
																	that.temporary.searchKeyModal = val
																},
															},
															{
																default() {
																	return [
																		createElement(
																			Row,
																			{
																				gutter: 15,
																			},
																			{
																				default() {
																					return el
																				},
																			},
																		),
																	]
																},
															},
														),
														createElement(Divider),
														createElement(
															Button,
															{
																onClick: () => {
																	that.temporary.searchKeyModal = []
																	Object.keys(that.searchKey).forEach((key) => {
																		that.temporary.searchKeyModal.push(key)
																	})
																},
															},
															{
																default() {
																	return "全选"
																},
															},
														),
														createElement(
															Button,
															{
																class: "mg-l-5",
																onClick: () => {
																	that.temporary.searchKeyModal = []
																},
															},
															{
																default() {
																	return "全不选"
																},
															},
														),
														createElement(
															Button,
															{
																class: "mg-l-5",
																onClick: () => {
																	that.temporary.searchKeyModal = []
																	that.originSearchForm.forEach((sea) => {
																		const key = that.getKey(sea)
																		if (key && sea.nFilterHidden !== true) {
																			that.temporary.searchKeyModal.push(key)
																		}
																	})
																	Object.keys(that.searchKey).forEach((key) => {
																		that.searchKey[key] = that.temporary.searchKeyModal.indexOf(key) > -1
																	})
																	localStorage.setItem(that.getTableSearchName(), "")
																	modal.destroy()
																},
															},
															{
																default() {
																	return "初始化"
																},
															},
														),
													])
												},
												onOk: () => {
													Object.keys(that.searchKey).forEach((key) => {
														that.searchKey[key] = that.temporary.searchKeyModal.indexOf(key) > -1
													})
													localStorage.setItem(that.getTableSearchName(), JSON.stringify(that.searchKey))
												},
											})
										},
									},
									{
										default() {
											return [
												createElement(NewbieIcon, {
													icon: "SettingOutlined",
													style: { fontSize: "18px" },
												}),
											]
										},
									},
								),
							]
						},
					},
				),
			]
			if (formItems) {
				formItems.push(
					createElement(
						Col,
						{
							span: 6,
							xxl: 4,
							style: { marginBottom: "5px", display: "flex", alignItems: "flex-start" },
						},
						{
							default() {
								return acContent
							},
						},
					),
				)
			}
			return [
				createElement(
					Row,
					{ gutter: 15 },
					{
						default() {
							return formItems
						},
					},
				),
			]
		}

		const that = this
		let formItems = []

		// 公共搜索条件
		let commonBatchTextarea = ""
		this.mySearchColumns.forEach((sea) => {
			const key = that.getKey(sea)
			const title = sea.nFilterTitle || sea.title
			let hasItem = false
			let makeDouble = false

			switch (sea.nFilterType.toLowerCase()) {
				case "input":
					hasItem = [makeInput(that, title, key, sea.nFilterProps, sea.nFilterValue)]
					break
				case "select":
					hasItem = [
						makeSelect(that, title, key, getOptionsValue(sea.nFilterOptions), sea.nFilterProps, sea.nFilterStyle, sea.nFilterCallback),
					]
					break
				case "address":
					hasItem = [makeAddress(that, title, key, sea.nFilterProps)]
					break
				case "date":
					hasItem = [makeDate(that, title, key, sea.nFilterProps)]
					if (sea.nFilterProps && sea.nFilterProps.double) {
						makeDouble = true
					}
					break
				case "batch":
					commonBatchTextarea = makeTextarea(that, title, key, sea.nFilterProps, sea.nFilterValue)
					break
				default:
					break
			}
			if (hasItem) {
				formItems.push(
					createElement(
						Col,
						{
							span: makeDouble ? 12 : 6,
							xxl: makeDouble ? 8 : 4,
							class: "mb-2",
						},
						{
							default() {
								return hasItem
							},
						},
					),
				)
			}
		})
		formItems = doSearchAction(that, formItems)

		let formItemsOne = ""
		if (commonBatchTextarea) {
			formItemsOne = [
				createElement(
					Row,
					{},
					{
						default() {
							return [
								createElement(
									Col,
									{
										style: {
											width: "200px",
											marginRight: "15px",
										},
									},
									{
										default() {
											return commonBatchTextarea
										},
									},
								),
								createElement(
									Col,
									{
										style: {
											width: "calc(100% - 215px)",
										},
									},
									{
										default() {
											return formItems
										},
									},
								),
							]
						},
					},
				),
			]
		} else {
			formItemsOne = formItems
		}

		// 显示具体的搜索字段
		if (this.showSearchLabel.length) {
			const searchLabel = this.showSearchLabel.map((item) => {
				return createElement(
					Tag,
					{},
					{
						default() {
							return `${item.title}: ${item.label}`
						},
					},
				)
			})
			searchLabel.unshift(createElement("span", "搜索条件 "))
			formItemsOne.push(
				createElement(
					"div",
					{ class: "mg-t-5", style: "overflow: auto;" },
					{
						default() {
							return searchLabel
						},
					},
				),
			)
		}

		return createElement(
			Form,
			{
				class: "newbie-query-form",
				inline: true,
				model: this.queryForm,
				onSubmit: (e) => {
					e.preventDefault()
				},
			},
			{
				default() {
					return formItemsOne
				},
			},
		)
	},
}
</script>
