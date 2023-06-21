<script>
import { h as createElement, reactive, toRefs, withModifiers } from "vue"
import {
	Button,
	Card,
	Cascader,
	CheckboxGroup,
	Col,
	DatePicker,
	Divider,
	Form,
	Input,
	InputNumber,
	Modal,
	RadioGroup,
	RangePicker,
	Row,
	Select,
	Skeleton,
	Spin,
	Switch,
	Tag,
	Timeline,
	TimePicker,
	TimeRangePicker,
	Tooltip,
	TreeSelect,
} from "ant-design-vue"
import { cloneDeep, isArray, isFunction, isString, isUndefined } from "lodash-es"
import { formLabel, useFormFail, useProcessStatusSuccess } from "@/js/hooks/web/form"
import { getFullCode, getOptionsValue } from "@/js/utils"
import { useFetch } from "@/js/hooks/web/network"
import { useCreateDateFromFormat, useDateFormat } from "@/js/hooks/web/datetime"
import { useModalConfirm } from "@/js/hooks/web/interact"
import NewbieIcon from "./NewbieIcon.vue"
import NewbieUploader from "./NewbieUploader.vue"
import NewbieAddress from "./NewbieAddress.vue"
import NewbieEditor from "./NewbieEditor.vue"

export const RERENDER = "rerender"
export default {
	name: "NewbieEdit",
	props: {
		title: {
			// 页面标题
			type: String,
			default: "",
		},
		layout: {
			// 页面布局方式，有normal和fixed
			type: String,
			default: "normal",
		},
		data: {
			// 本地数据
			type: [Object, String],
			default: null,
		},
		fetchData: {
			// 获取详情数据
			type: Object,
			default: () => ({}),
		},
		fetchUrl: {
			// 获取详情URL
			type: String,
			default: "",
		},
		submitUrl: {
			// 提交数据URL
			type: String,
			default: "",
		},
		submitButtonText: {
			// 提交按钮文字
			type: String,
			default: "",
		},
		submitConfirmText: {
			// 提交确认文字
			type: String,
			default: "",
		},
		closeButtonText: {
			// 关闭按钮文字
			type: String,
			default: "",
		},
		disabled: {
			type: Boolean,
			default: false,
		},
		submitDisabled: {
			// 隐藏提交按钮
			type: Boolean,
			default: false,
		},
		onSuccess: {
			// 提交成功后的回调
			type: Function,
			default: null,
		},
		onClose: {
			// 关闭页面的按钮
			type: Function,
			default: null,
		},
		form: {
			// 数据
			type: Array,
			default() {
				return [
					{
						key: "", // 数据库关联名称
						title: "", // 显示的名字
						type: "", // 类型,默认是input
						position: "", // 位置，有left(column是two才生效),right(column是two才生效),divider,fixed(layout为fixed才生效) 四种
						placeholder: "", // 组件里的提示
						tips: "", // form item里的提示
						style: "", // 样式
						required: "", // 是否必填
						disabled: "", // 组件不可编辑状态
						onChange: "", // 组件变化时的回调
						defaultProps: "", // 组件的配置
						defaultValue: "", // 默认值，默认是空字符串
					},
				]
			},
		},
		column: {
			// 可选参数 one, two
			type: String,
			default: "one",
		},
		columnWidth: {
			// 在column为two时生效，实为col的分栏。
			type: String,
			default: "12,12",
		},
		autoLoad: {
			// 自动加载数据,在fetchData里找
			type: [Boolean, Array, String],
			default: true,
		},
		fullWidth: {
			// 占满屏
			type: Boolean,
			default: false,
		},
		size: {
			// card的格式
			type: String,
			default: "small",
		},
		cardWrapper: {
			// 用card来包裹
			type: Boolean,
			default: true,
		},
		processReturnData: {
			type: Function,
			default: null,
		},
		processSubmitData: {
			// return false会阻止提交操作
			type: Function,
			default: null,
		},
		formConfig: {
			// form的配置
			type: Object,
			default() {
				return {}
			},
		},
		reverseButton: {
			type: Boolean,
			default: false,
		},
	},
	emits: ["afterSuccess"],
	setup() {
		const state = reactive({
			temporary: {}, // 用于存放一些临时数据
			buttonLoading: {
				loading: false,
			},
			isInit: true,
			isInitForm: false, // 控制初始渲染
			ref: "",
			rules: {},
			submitForm: {},
		})
		return {
			...toRefs(state),
		}
	},
	computed: {
		formData() {
			return this.form
		},
	},
	watch: {
		data(newV) {
			this.initForm(newV || "")
		},
	},
	created() {
		if (!this.ref) {
			this.ref = `edit${new Date().getTime()}`
		}
		this.doInit()
	},
	methods: {
		doInit() {
			if (this.autoLoad && this.fetchUrl) {
				let loadData = this.autoLoad
				let isLoad = true
				// 如果是true，则检查fetchData，要全部有值才能发起请求
				if (loadData === true) {
					Object.keys(this.fetchData).forEach((key) => {
						if (!this.fetchData[key]) {
							isLoad = false
						}
					})
				} else {
					// 不然就按需检查
					if (isString(loadData)) {
						loadData = [loadData]
					}
					loadData.forEach((item) => {
						if (!this.fetchData[item]) {
							isLoad = false
						}
					})
				}

				// 要加载数据
				if (isLoad) {
					this.fetchItem()
					return
				}
			}

			this.isInit = false
			this.initForm(this.data || "")
		},
		getColumnCol(isHalf) {
			const type = this.column === "two" && !isHalf ? "Part" : "Full"
			return {
				labelCol: formLabel[`commonLabel${type}Col`],
				wrapperCol: formLabel[`commonWrapper${type}Col`],
			}
		},
		getForm() {
			return cloneDeep(this.submitForm)
		},
		getSubmitForm() {
			return this.submitForm
		},

		setForm(fields) {
			Object.keys(fields).forEach((key) => {
				this.submitForm[key] = fields[key]
			})
		},
		initForm(result) {
			let value
			let json = {}
			const res = result ? cloneDeep(result) : ""
			this.isInitForm = true
			this.buttonLoading.loading = false
			this.formData.forEach((item) => {
				value = ""
				if (res && !isUndefined(res[item.key])) {
					value = res[item.key]
					delete res[item.key]
				} else if (!isUndefined(item.defaultValue)) {
					value = isFunction(item.defaultValue) ? item.defaultValue(this.submitForm) : item.defaultValue
				}
				if (item.type === "switch") {
					value = value === "true" || value === 1 || value === "1" || value === true
				} else if (item.type === "select") {
					if (item.defaultProps && (item.defaultProps.mode === "multiple" || item.defaultProps.mode === "tags")) {
						value = value || []
					}
				} else if (item.type === "tree-select") {
					if (item.defaultProps && (item.defaultProps.multiple === true || item.defaultProps.treeCheckable === true)) {
						value = value || []
					}
				} else if (item.type === "date" || item.type === "time") {
					if (item.defaultProps && item.defaultProps.type === "range") {
						value = value ? [useCreateDateFromFormat(value[0]), useCreateDateFromFormat(value[1])] : []
					} else {
						value = value ? useCreateDateFromFormat(value) : ""
					}
				} else if (item.type === "checkbox" || item.type === "tag") {
					value = value || []
				} else if (item.type === "address") {
					value = value || []
					if (!isArray(value)) {
						value = getFullCode(value)
					}
				} else if (item.type === "uploader") {
					if (item.defaultProps && item.defaultProps.maxNum && item.defaultProps.maxNum > 1) {
						value = value || []
					} else {
						value = value || { path: "", url: "" }
					}
				} else if (item.type === "editor") {
					setTimeout(
						(val) => {
							if (this.$refs[`richeditor${item.key}`]) {
								this.$refs[`richeditor${item.key}`].setContent(val)
							}
						},
						0,
						value,
					)
				}
				json[item.key] = value
			})
			if (res) {
				json = { ...json, ...res }
			}
			if (this.processReturnData) {
				json = this.processReturnData(json, result)
			}
			this.submitForm = json
		},
		async fetchItem() {
			this.isInit = true
			if (this.fetchUrl) {
				const res = await useFetch().get(this.fetchUrl, { params: this.fetchData })
				this.isInit = false
				useProcessStatusSuccess(res, () => {
					this.$nextTick(() => {
						this.initForm(res.result)
					})
				})
			}
		},
		reset() {
			this.$refs[this.ref].resetFields()
		},
		submit() {
			this.$refs[this.ref]
				.validate()
				.then(async () => {
					let form = cloneDeep(this.submitForm)
					this.formData.forEach((item) => {
						switch (item.type) {
							case "date":
								if (form[item.key]) {
									form[item.key] = useDateFormat(form[item.key], "YYYY-MM-DD")
								}
								break
							case "time":
								if (form[item.key]) {
									form[item.key] = useDateFormat(form[item.key], "HH:mm:ss")
								}
								break
							case "datetime":
								if (form[item.key]) {
									form[item.key] = useDateFormat(form[item.key], "YYY-MM-DD HH:mm:ss")
								}
								break
							case "address":
								if (isArray(form[item.key])) {
									form[item.key] = form[item.key].length ? form[item.key][form[item.key].length - 1] : ""
								}
								break
							default:
								break
						}
					})
					if (this.processSubmitData) {
						form = this.processSubmitData({ processedForm: form, rawForm: this.submitForm })
						if (form === false) {
							return
						}
					}
					const res = await useFetch(this.buttonLoading).post(this.submitUrl, form)
					if (this.onSuccess) {
						this.onSuccess(res)
					} else {
						useProcessStatusSuccess(res, () => {
							Modal.success({
								content: "保存成功",
								onOk: () => {
									this.$emit("afterSuccess", res)
								},
							})
						})
					}
				})
				.catch((info) => {
					useFormFail(info)
				})
		},
	},
	render() {
		function createTips(item) {
			if (item.tips && isFunction(item.tips)) {
				console.log(item.tips())
			}
			return item.tips
				? createElement(
						"div",
						{ class: "tips" },
						{
							default() {
								return isFunction(item.tips) ? item.tips() : item.tips
							},
						},
				  )
				: ""
		}

		function createInput(that, item) {
			if (that.submitForm[item.key] === null || that.submitForm[item.key] === "null" || that.submitForm[item.key] === undefined) {
				that.submitForm[item.key] = ""
			}
			that.submitForm[item.key] = String(that.submitForm[item.key])
			const inputSlot = {}
			if (item.defaultProps) {
				if (item.defaultProps.prefix) {
					inputSlot.prefix = () => {
						return item.defaultProps.prefix
					}
				}
				if (item.defaultProps.suffix) {
					inputSlot.suffix = () => {
						return item.defaultProps.suffix
					}
				}
				if (item.defaultProps.prepend) {
					inputSlot.addonBefore = () => {
						return item.defaultProps.prepend
					}
				}
				if (item.defaultProps.append) {
					inputSlot.addonAfter = () => {
						return isFunction(item.defaultProps.append) ? item.defaultProps.append() : item.defaultProps.append
					}
				}
			}
			let defaultStyle = { width: item.width || "200px" }
			let name = Input
			if (item.type === "textarea" || (item.defaultProps && item.defaultProps.type === "textarea")) {
				name = Input.TextArea
				defaultStyle = { width: "100%", maxWidth: "500px" }
			}

			if (item.type === "password" || (item.defaultProps && item.defaultProps.type === "password")) {
				name = Input.Password
			}

			return createElement(name, {
				value: that.submitForm[item.key],
				disabled: !!item.disabled,
				placeholder: item.placeholder || `请填写${item.title}`,
				style: item.style || defaultStyle,
				class: item.class || "",
				onInput(e) {
					that.submitForm[item.key] = String(e.target.value)
				},
				...(item.defaultProps || {}),
				...inputSlot,
			})
		}

		function createTag(that, item) {
			const key = `commonTag_${item.key}_input`
			if (isUndefined(that.temporary[key])) {
				that.temporary[key] = ""
			}

			const tagElements =
				that.submitForm[item.key] &&
				that.submitForm[item.key].map((option) => {
					return createElement(
						Tag,
						{
							closable: !item.disabled,
						},
						{
							default() {
								return option
							},
						},
					)
				})

			let inputElements = createElement(
				Input,
				{
					value: that.temporary[key],
					disabled: item.disabled,
					icon: "plus",
					placeholder: item.placeholder || "按Enter键确认",
					style: item.style || { width: "200px" },
					onInput(e) {
						that.temporary[key] = String(e.target.value)
					},
					onPressEnter: withModifiers(
						(e) => {
							if (e.keyCode === 13 && that.temporary[key]) {
								that.submitForm[item.key].push(that.temporary[key])
								that.temporary[key] = ""
							}
						},
						["prevent"],
					),
					onBlur() {
						if (that.temporary[key]) {
							that.submitForm[item.key].push(that.temporary[key])
							that.temporary[key] = ""
						}
					},
				},
				{
					prefix() {
						return createElement(NewbieIcon, {
							icon: "PlusOutlined",
						})
					},
				},
			)
			if (item.disabled) {
				inputElements = ""
			}

			return createElement(
				"div",
				{},
				{
					default() {
						return [tagElements, inputElements]
					},
				},
			)
		}

		function createNumber(that, item) {
			that.submitForm[item.key] = Number(that.submitForm[item.key]) || 0
			return createElement(InputNumber, {
				value: that.submitForm[item.key],
				disabled: item.disabled,
				placeholder: item.placeholder || `请填写${item.title}`,
				style: item.style || { width: "100px" },
				onChange: (val) => {
					that.submitForm[item.key] = val
				},
				...(item.defaultProps || {}),
			})
		}

		function createSelect(that, item) {
			let options = getOptionsValue(item.options)
			options = options.map((op) => {
				return isString(op) ? { value: op, label: op } : op
			})
			const onEvent = {
				onChange(val) {
					if (item.change) {
						item.change(val)
					}
					that.submitForm[item.key] = val
				},
				...(item.defaultEvent || {}),
			}

			return createElement(Select, {
				value: that.submitForm[item.key] || undefined,
				options,
				disabled: item.disabled,
				allowClear: true,
				placeholder: item.placeholder || `请选择${item.title}`,
				style: item.style || { width: "200px" },
				class: item.class || "",
				...onEvent,
				...(item.defaultProps || {}),
			})
		}

		function createTreeSelect(that, item) {
			const options = getOptionsValue(item.options)
			const onEvent = {
				onChange(val) {
					if (item.change) {
						item.change(val)
					}
					that.submitForm[item.key] = val
				},
				...(item.defaultEvent || {}),
			}

			return createElement(TreeSelect, {
				value: that.submitForm[item.key] || undefined,
				treeData: options,
				disabled: item.disabled,
				treeDefaultExpandAll: true,
				treeNodeFilterProp: "label",
				showSearch: true,
				allowClear: true,
				placeholder: item.placeholder || `请选择${item.title}`,
				style: item.style || { width: "200px" },
				class: item.class || "",
				...onEvent,
				...(item.defaultProps || {}),
			})
		}

		function createRemote(that, item) {
			const loadKey = `remote_${item.key}_load`
			const optionKey = `remote_${item.key}_options`
			const initKey = `remote_${item.key}_init`
			if (that.temporary[initKey] === undefined) {
				that.temporary[initKey] = true
				let options = []
				if (item.options) {
					options = getOptionsValue(item.options)
					options = options.map((op) => {
						return isString(op) ? { value: op, label: op } : op
					})
				}
				that.temporary[optionKey] = options
			}
			if (that.temporary[optionKey] === undefined) {
				that.temporary[optionKey] = []
			}
			if (that.temporary[loadKey] === undefined) {
				that.temporary[loadKey] = { loading: false }
			}

			const onEvent = {
				onChange(val) {
					if (item.change) {
						item.change(val)
					}
					that.submitForm[item.key] = val
				},
				async onSearch(name) {
					if (!name) {
						return
					}
					const res = await useFetch(that.temporary[loadKey]).get(item.url, { params: { keyword: name } })
					useProcessStatusSuccess(res, () => {
						if (item.onSearch) {
							res.result = item.onSearch(res.result)
						}
						that.temporary[optionKey] = res.result
					})
				},
				...(item.defaultEvent || {}),
			}
			return createElement(
				Select,
				{
					value: that.submitForm[item.key] || undefined,
					options: that.temporary[optionKey],
					disabled: item.disabled,
					showSearch: true,
					allowClear: true,
					filterOption: false,
					dropdownMatchSelectWidth: false,
					placeholder: item.placeholder || `请选择${item.title}`,
					style: item.style || { width: "200px" },
					...onEvent,
					...(item.defaultProps || {}),
				},
				{
					notFoundContent() {
						return that.temporary[loadKey].loading ? createElement(Spin) : ""
					},
				},
			)
		}

		function createAddress(that, item) {
			return createElement(NewbieAddress, {
				modelValue: that.submitForm[item.key],
				disabled: item.disabled,
				placeholder: item.placeholder || `请选择${item.title}`,
				style: item.style || { width: "200px" },
				"onUpdate:modelValue": (val) => {
					that.submitForm[item.key] = val
				},
				...(item.defaultProps || {}),
			})
		}

		function createCascade(that, item) {
			return createElement(Cascader, {
				allowClear: true,
				value: that.submitForm[item.key],
				options: getOptionsValue(item.options),
				disabled: item.disabled,
				changeOnSelect: true,
				showSearch: {
					filter(inputValue, path) {
						return path.some((option) => {
							return option.label && option.label.toLowerCase().indexOf(inputValue.toLowerCase()) > -1
						})
					},
				},
				placeholder: item.placeholder || `请选择${item.title}`,
				style: item.style || { width: "200px" },
				class: item.class || "",
				onChange: (val) => {
					if (item.change) {
						item.change(val)
					}
					that.submitForm[item.key] = val
				},
				...(item.defaultProps || {}),
			})
		}

		function createUploader(that, item) {
			return createElement(NewbieUploader, {
				modelValue: that.submitForm[item.key],
				disabled: item.disabled,
				"onUpdate:modelValue": (val) => {
					that.submitForm[item.key] = val
				},
				...(item.defaultProps || {}),
			})
		}

		function createRichEditor(that, item) {
			return createElement(NewbieEditor, {
				ref: `richeditor${item.key}`,
				key: item.key,
				modelValue: that.submitForm[item.key],
				disabled: item.disabled,
				style: item.style || {},
				"onUpdate:modelValue": (val) => {
					if (val !== that.submitForm[item.key]) {
						that.submitForm[item.key] = val
					}
				},
				...(item.defaultProps || {}),
			})
		}

		function createDate(that, item) {
			let dateName = DatePicker
			if (item.defaultProps && item.defaultProps.type === "range") {
				dateName = RangePicker
			}

			return createElement(dateName, {
				value: that.submitForm[item.key],
				allowClear: true,
				disabled: item.disabled,
				style: item.style || { width: "200px" },
				class: item.class || "",
				onChange: (val) => {
					if (item.change) {
						item.change(val)
					}
					that.submitForm[item.key] = val
				},
				onOpenChange: (val) => {
					if (item.openChange) {
						item.openChange(val)
					}
				},
				...(item.defaultProps || {}),
			})
		}

		function createTime(that, item) {
			let dateName = TimePicker
			if (item.defaultProps) {
				if (item.defaultProps.type === "range") {
					dateName = TimeRangePicker
				}
			}
			return createElement(dateName, {
				value: that.submitForm[item.key],
				allowClear: true,
				disabled: item.disabled,
				style: item.style || {},
				onChange: (val) => {
					if (item.change) {
						item.change(val)
					}
					that.submitForm[item.key] = val
				},
				onOpenChange: (val) => {
					if (item.openChange) {
						item.openChange(val)
					}
				},
				...(item.defaultProps || {}),
			})
		}

		function createSwitch(that, item) {
			const optionElements = {}
			if (item.options && item.options.length) {
				if (item.options[0]) {
					optionElements.checkedChildren = () => {
						return item.options[0]
					}
				}
				if (item.options[1]) {
					optionElements.unCheckedChildren = () => {
						return item.options[1]
					}
				}
			}
			return createElement(
				Switch,
				{
					checked: that.submitForm[item.key],
					disabled: item.disabled,
					style: item.style || {},
					onChange: (val) => {
						that.submitForm[item.key] = val
					},
					...(item.defaultProps || {}),
				},

				optionElements,
			)
		}

		function createRadioGroup(that, item) {
			let options = getOptionsValue(item.options)
			options = options.map((op) => {
				return isString(op) ? { value: op, label: op } : op
			})
			return createElement(RadioGroup, {
				value: that.submitForm[item.key],
				optionType: "button",
				buttonStyle: "solid",
				disabled: item.disabled,
				options,
				style: item.style || {},
				onChange: (e) => {
					const val = e.target.value
					that.submitForm[item.key] = val
					if (item.change) {
						item.change(val)
					}
				},
				...(item.defaultProps || {}),
			})
		}

		function createCheckboxGroup(that, item) {
			let options = getOptionsValue(item.options)
			options = options.map((op) => {
				return isString(op) ? { value: op, label: op } : op
			})
			return createElement(CheckboxGroup, {
				value: that.submitForm[item.key],
				disabled: item.disabled,
				options,
				style: item.style || {},
				onChange: (val) => {
					that.submitForm[item.key] = val
					if (item.change) {
						item.change(val)
					}
				},
				...(item.defaultProps || {}),
			})
		}

		function createText(that, item) {
			const value = item.defaultValue ? item.defaultValue(that.submitForm) : that.submitForm[item.key]
			let returnText = ""
			if (value) {
				if (isArray(value)) {
					returnText = []
					value.forEach((it) => {
						returnText.push(
							createElement(Tag, item.defaultProps || {}, {
								default() {
									return it
								},
							}),
						)
					})
				} else {
					returnText = createElement(Tag, item.defaultProps || {}, {
						default() {
							return value
						},
					})
				}
			}

			return returnText
		}

		function createHtml(that, item) {
			const value = (item.defaultValue && item.defaultValue(that.submitForm)) || that.submitForm[item.key]
			let returnText = ""
			if (value) {
				returnText = createElement("div", {
					style: item.style || {},
					innerHTML: value,
					...(item.defaultProps || {}),
				})
			}

			return returnText
		}

		function createTimeline(that, item) {
			let returnText = ""
			if (item.children) {
				const arr = []
				item.children.forEach((child) => {
					const html = []
					if (!isArray(child)) {
						child = [child]
					}
					child.forEach((ch) => {
						const value = (ch.value && ch.value(that.submitForm)) || that.submitForm[ch.key] || ""
						ch.type = ch.type.toLowerCase()
						if (ch.type === "tag") {
							if (value) {
								html.push(
									createElement(Tag, ch.defaultProps || {}, {
										default() {
											return value
										},
									}),
								)
							}
						} else {
							const content = createElement(
								"div",
								{
									style: {
										maxHeight: "38px",
										lineHeight: "18px",
										marginBottom: "5px",
										overflow: "hidden",
										textOverflow: "ellipsis",
										display: "-webkit-box",
										"-webkit-box-orient": "vertical",
										"-webkit-line-clamp": 2,
									},
									...(item.defaultProps || {}),
								},

								{
									default() {
										return [
											ch.title
												? createElement("b", {
														style: {
															marginRight: "5px",
														},
														innerHTML: ch.title,
												  })
												: "",
											createElement("span", {
												innerHTML: value,
											}),
										]
									},
								},
							)
							if ((ch.title + value).length > 32) {
								const contentTip = createElement(
									Tooltip,
									{
										title: value,
									},
									{
										default() {
											return content
										},
									},
								)
								html.push(contentTip)
							} else {
								html.push(content)
							}
						}
					})
					arr.push(
						createElement(
							Timeline.Item,
							{},
							{
								default() {
									return html
								},
							},
						),
					)
				})
				returnText = createElement(Timeline, item.defaultProps || {}, {
					default() {
						return arr
					},
				})
			}
			return returnText
		}

		function createFormItem(that, item, labelWidth, isHalf) {
			if (item.type === "slot" && that.$slots[item.key]) {
				return that.$slots[item.key]({ submitForm: that.submitForm })
			}
			let returnItem = null
			let rules = ""
			item.type = item.type ? item.type.toLowerCase() : item.type
			if (item.required) {
				rules = {
					required: true,
					message: `请选择${item.title}`,
					trigger: "change", // 特意
				}
			}

			if (that.disabled) {
				item.disabled = that.disabled
			}

			if (item.customRender) {
				returnItem = item.customRender({ submitForm: that.submitForm, item })
				if (item.required) {
					rules = {
						required: true,
						message: item.message || `请填写${item.title}`,
						trigger: item.trigger || "blur",
					}
				}
			}

			if ((!item.customRender && !returnItem) || returnItem === RERENDER) {
				switch (item.type) {
					case "select":
						returnItem = createSelect(that, item)
						break
					case "tree-select":
						returnItem = createTreeSelect(that, item)
						break
					case "remote":
						returnItem = createRemote(that, item)
						break
					case "address":
						returnItem = createAddress(that, item)
						if (item.required) {
							rules.type = "array"
						}
						break
					case "cascader":
						returnItem = createCascade(that, item)
						if (item.required) {
							rules.type = "array"
						}
						break
					case "number":
						returnItem = createNumber(that, item)
						if (item.required) {
							rules = {
								type: "number",
								required: true,
								message: `请填写${item.title}`,
								trigger: "blur",
							}
						}
						break
					case "uploader":
						returnItem = createUploader(that, item)
						if (item.required) {
							if (item.defaultProps && item.defaultProps.maxNum && item.defaultProps.maxNum > 1) {
								rules.type = "array"
							} else {
								rules = {
									type: "object",
									required: true,
									message: `请选择${item.title}`,
									fields: {
										path: { type: "string", required: true, message: `请选择${item.title}` },
									},
								}
							}
						}
						break
					case "editor":
						returnItem = createRichEditor(that, item)
						if (item.required) {
							rules.message = `请填写${item.title}`
						}
						break
					case "date":
						returnItem = createDate(that, item)
						break
					case "time":
						returnItem = createTime(that, item)
						break
					case "radio":
						returnItem = createRadioGroup(that, item)
						break
					case "checkbox":
						returnItem = createCheckboxGroup(that, item)
						if (item.required) {
							rules.type = "array"
						}
						break
					case "switch":
						returnItem = createSwitch(that, item)
						break
					case "tag":
						returnItem = createTag(that, item)
						if (item.required) {
							rules.type = "array"
						}
						break
					case "text":
						returnItem = createText(that, item)
						break
					case "html":
						returnItem = createHtml(that, item)
						break
					case "timeline":
						returnItem = createTimeline(that, item)
						if (!item.title) {
							labelWidth = {
								labelCol: { span: 0 },
								wrapperCol: { span: 24 },
							}
						}
						if (!item.itemStyle) {
							item.itemStyle = { marginBottom: 0 }
						}
						break
					default:
						returnItem = createInput(that, item)
						if (item.required) {
							rules = {
								required: true,
								message: `请填写${item.title}`,
								trigger: "blur",
							}
						}
						break
				}
			}

			let props = {
				label: item.title,
				name: item.key,
			}
			if (item.formExtra) {
				props.extra = item.formExtra
			}
			if (rules) {
				props.rules = rules
			}

			// isHalf证明是双列，则覆盖labelWidth的属性
			if (isHalf) {
				labelWidth = that.getColumnCol(true)
			}
			if (!isUndefined(labelWidth)) {
				props = { ...props, ...labelWidth }
			}
			if (!isUndefined(item.itemStyle)) {
				props = { ...props, ...item.itemStyle }
			}

			return returnItem
				? createElement(Form.Item, props, {
						default() {
							return [returnItem, createTips(item)]
						},
				  })
				: null
		}

		function createSubmitButton(that, labelWidth) {
			let closeButton = ""
			if (isFunction(that.onClose)) {
				closeButton = createElement(
					Button,
					{
						style: {
							marginLeft: that.reverseButton ? "0px" : "10px",
						},
						onClick: () => {
							that.onClose()
						},
					},
					{
						default() {
							return that.closeButtonText || "关闭"
						},
					},
				)
			}
			let submitButton = ""
			const submitText = that.submitButtonText || "保存"
			if (!that.submitDisabled && !that.disabled) {
				submitButton = createElement(
					Button,
					{
						loading: that.buttonLoading.loading,
						htmlType: "submit",
						type: "primary",
						style: {
							marginLeft: that.reverseButton ? "10px" : "0",
						},
						onClick() {
							if (that.submitConfirmText) {
								useModalConfirm(that.submitConfirmText, () => {
									setTimeout(() => {
										that.submit()
									}, 200)
								})
							} else {
								that.submit()
							}
						},
					},
					{
						default() {
							return that.buttonLoading.loading ? `${submitText}中...` : submitText
						},
					},
				)
			}

			const colType = that.column === "two" ? "Part" : "Full"
			let buttonProps = {
				wrapperCol: formLabel[`commonWrapper${colType}Offset`],
				class: "mg-0 divider-line pt-2",
			}
			if (labelWidth !== undefined) {
				buttonProps = { ...buttonProps, ...labelWidth }
			}
			return createElement(Form.Item, buttonProps, {
				default() {
					return that.reverseButton ? [closeButton, submitButton] : [submitButton, closeButton]
				},
			})
		}

		const that = this
		const colItems = []
		const form = this.formData
		const columnType = this.column
		const fixedItems = []
		const layoutType = this.layout
		const labelColZero = { labelCol: { span: 0 }, wrapperCol: { span: 24 } }
		let rowCol = []

		// isInitForm为true才渲染组件
		if (that.isInitForm) {
			// 生成界面排列数据
			form.forEach((item) => {
				if (!isUndefined(item.divider)) {
					colItems.push({
						divider: true,
						dividerTitle: item.divider === true ? "" : item.divider,
					})
				}
				// 双列并且有左右分隔
				if (columnType === "two" && item.position && (item.position.indexOf("left") > -1 || item.position.indexOf("right") > -1)) {
					if (colItems.length === 0 || isUndefined(colItems[colItems.length - 1].left)) {
						colItems.push({
							left: [],
							right: [],
						})
					}
					if (item.position.indexOf("left") > -1) {
						const renderItem = createFormItem(that, item, false, true)
						if (renderItem) {
							colItems[colItems.length - 1].left.push(renderItem)
						}
					} else if (item.position.indexOf("right") > -1) {
						const renderItem = createFormItem(that, item, false, true)
						if (renderItem) {
							colItems[colItems.length - 1].right.push(renderItem)
						}
					}
				} else if (layoutType === "fixed" && item.position && item.position.indexOf("fixed") > -1) {
					// 固定栏
					const renderItem = createFormItem(that, item, labelColZero)
					if (renderItem) {
						fixedItems.push(renderItem)
					}
				} else {
					// 单列，允许双列模式中的单列
					if (colItems.length === 0 || !isArray(colItems[colItems.length - 1])) {
						colItems.push([])
					}

					const renderItem = createFormItem(that, item)
					if (renderItem) {
						colItems[colItems.length - 1].push(renderItem)
					}
				}
			})

			// 生成操作按钮
			if (layoutType === "fixed") {
				fixedItems.push([createSubmitButton(that, labelColZero)])
			} else {
				colItems.push([createElement(Divider), createSubmitButton(that)])
			}
			const rows = []
			if (this.column === "one") {
				// 单列
				colItems.forEach((item) => {
					if (item.divider === true) {
						rows.push(
							createElement(
								Divider,
								{ orientation: "left" },
								{
									default() {
										return item.dividerTitle || ""
									},
								},
							),
						)
						return false
					}
					rows.push(
						createElement(
							Row,
							{},
							{
								default() {
									return createElement(
										Col,
										{
											span: 24,
											xl: that.fullWidth ? 24 : { span: 20, offset: 2 },
											xxl: that.fullWidth ? 24 : { span: 18, offset: 3 },
										},
										{
											default() {
												return item
											},
										},
									)
								},
							},
						),
					)
					return true
				})
			} else if (this.column === "two") {
				// 双列
				const widthArr = this.columnWidth.split(",")
				colItems.forEach((item) => {
					if (item.divider === true) {
						rows.push(
							createElement(
								Divider,
								{ orientation: "left" },
								{
									default() {
										return item.dividerTitle || ""
									},
								},
							),
						)
						return false
					}

					if (isUndefined(item.left)) {
						rows.push(item)
					} else {
						rows.push(
							createElement(
								Row,
								{},
								{
									default() {
										return [
											createElement(
												Col,
												{ span: widthArr[0] || 12 },
												{
													default() {
														return item.left || ""
													},
												},
											),
											createElement(
												Col,
												{ span: widthArr[1] || 12 },
												{
													default() {
														return item.right || ""
													},
												},
											),
										]
									},
								},
							),
						)
					}

					return true
				})
			}

			rowCol = rows
			if (layoutType === "fixed") {
				rowCol = createElement(
					Row,
					{},
					{
						default() {
							return [
								createElement(
									Col,
									{
										span: 18,
									},
									{
										default() {
											return rows
										},
									},
								),
								createElement(
									Col,
									{
										span: 6,
									},
									{
										default() {
											return createElement(
												Card,
												{ style: { marginLeft: "20px" } },
												{
													default() {
														return fixedItems
													},
												},
											)
										},
									},
								),
							]
						},
					},
				)
			}
		}

		const cardSlot = {}
		if (that.$slots.extra) {
			cardSlot.extra = () => {
				return that.$slots.extra
			}
		}
		if (that.$slots.title) {
			cardSlot.title = () => {
				return that.$slots.title
			}
		}

		const childrenWrapper = []
		// that.$slots.prepend && childrenWrapper.push(that.$slots.prepend())

		childrenWrapper.push(
			createElement(
				Skeleton,
				{
					loading: that.isInit,
					active: true,
					paragraph: {
						rows: 10,
					},
				},
				{
					default() {
						const rd = []
						if (that.$slots.prepend) {
							rd.push(that.$slots.prepend())
						}
						rd.push(rowCol)
						return rd
					},
				},
			),
		)

		const formProps = { ...that.getColumnCol(), ...that.formConfig }
		formProps.model = that.submitForm
		formProps.class = "newbie-edit-wrapper"
		formProps.ref = that.ref
		formProps.labelWrap = true
		return createElement(Form, formProps, {
			default() {
				return that.cardWrapper
					? createElement(
							Card,
							{
								size: that.size,
								title: that.title || "",
							},
							{
								default() {
									return childrenWrapper
								},
								...cardSlot,
							},
					  )
					: childrenWrapper
			},
		})
	},
}
</script>

<style lang="less" scoped>
.newbie-edit-wrapper {
	.ant-divider {
		font-weight: bold;
	}

	.newbie-edit-extra-close {
		i {
			font-size: 25px;
			line-height: 1;
		}
	}

	.ant-timeline-item-last {
		padding-bottom: 0;
	}
}

.tips {
	color: #9ca3af;
	margin-top: 4px;
	font-size: 13px;
	white-space: break-spaces;
}
</style>
