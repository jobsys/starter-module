import { isFunction } from "lodash-es"

export function getOptionsValue(options) {
	let result = []
	if (options && isFunction(options)) {
		result = options()
	} else if (options && options.length) {
		result = options
	}
	return result
}

export function getFullCode(code, level) {
	code += ""
	if (code && code.length === 6) {
		if (!level) {
			if (/0000$/.test(code)) {
				level = 1
			} else if (/00$/.test(code)) {
				level = 2
			} else {
				level = 3
			}
		}
		if (level === 1) {
			return [`${code.substr(0, 2)}0000`]
		}
		if (level === 2) {
			return [`${code.substr(0, 2)}0000`, `${code.substr(0, 4)}00`]
		}
		if (level === 3) {
			return [`${code.substr(0, 2)}0000`, `${code.substr(0, 4)}00`, code]
		}
	}
	return []
}
