import { isFunction, isString } from "lodash-es"
import { message } from "ant-design-vue"
import { STATUS } from "../common/network"

const { STATE_CODE_FAIL, STATE_CODE_INFO_NOT_COMPLETE, STATE_CODE_NOT_FOUND, STATE_CODE_NOT_ALLOWED, STATE_CODE_SUCCESS } = STATUS

export function useHiddenForm(options) {
	const { url, data, csrfToken } = options
	let { method } = options

	method = method || "post"

	const form = document.createElement("form")
	form.action = url
	form.method = method
	form.target = "_blank"
	form.style.display = "none"

	Object.keys(data).forEach((key) => {
		const input = document.createElement("input")
		input.type = "hidden"
		input.name = key
		input.value = data[key]
		form.appendChild(input)
	})

	if (!csrfToken) {
		const input = document.createElement("input")
		input.type = "hidden"
		input.name = "_token"
		input.value = document.querySelector('meta[name="csrf-token"]').getAttribute("content")
		form.appendChild(input)
	}

	document.body.appendChild(form)

	return form
}

export function useProcessStatus(res, ops) {
	const { status } = res
	const msg = res.result
	const predefined = {}
	predefined.default = "请求失败, 请检查数据并重试"
	predefined[STATE_CODE_FAIL] = "系统错误，请稍候再试"
	predefined[STATE_CODE_NOT_FOUND] = "请求的内容不存在"
	predefined[STATE_CODE_INFO_NOT_COMPLETE] = "信息不完整"
	predefined[STATE_CODE_NOT_ALLOWED] = "没有权限"

	// 有几个常用的自定义名称
	const special = {
		[STATE_CODE_SUCCESS]: "success",
	}

	const op = ops[status] || ops[special[status]] || predefined[status] || predefined.def

	if (isString(op)) {
		if (status === STATE_CODE_SUCCESS) {
			message.success(op)
		} else {
			message.error(msg || op)
		}
	} else if (isFunction(op)) {
		op()
	}
}

export function useProcessStatusSuccess(res, success) {
	const that = this
	useProcessStatus(res, {
		success() {
			if (success) {
				success.call(that)
			}
		},
	})
}

export function useFormFail(e) {
	if (e && e.errorFields) {
		e.errorFields.forEach((item) => {
			message.error(item.errors.join(" "))
		})
	} else if (!(e && e.response)) {
		message.error("请检查填写项")
	}
}

export const formLabel = {
	commonLabelCol: { span: 8, xxl: 6 },
	commonWrapperCol: { span: 12, xxl: 14 },
	commonWrapperOffset: { xs: { offset: 8, span: 12 }, xxl: { offset: 6, span: 14 } },
	commonLabelFullCol: { span: 8, xxl: 6 },
	commonWrapperFullCol: { span: 16, xxl: 18 },
	commonWrapperFullOffset: { xs: { offset: 8, span: 16 }, xxl: { offset: 6, span: 18 } },
	commonLabelPartCol: { span: 4, xxl: 3 },
	commonWrapperPartCol: { span: 20, xxl: 21 },
	commonWrapperPartOffset: { xs: { offset: 4, span: 20 }, xxl: { offset: 3, span: 21 } },
}
