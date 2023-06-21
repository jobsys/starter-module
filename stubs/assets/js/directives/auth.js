/**
 * v-auth="'permissions1'"
 * v-auth:all="['clickPermissions',['permission1', 'permission2']]"
 * v-auth:any="['clickPermissions',['permission1', 'permission2']]"
 * v-auth:none="['clickPermissions',['permission1', 'permission2']]"
 * @type {*[]}
 */

let defaultPermissions = []

export const authDirective = {
	beforeMount(el, binding) {
		let { value } = binding
		const { arg } = binding

		if (!value) {
			return
		}

		if (typeof value === "string" || value instanceof String) {
			value = [value]
		}

		let [permissions, myPermissions] = value

		if (!myPermissions) {
			myPermissions = defaultPermissions
		}

		if (typeof permissions === "string" || permissions instanceof String) {
			permissions = [permissions]
		}

		// arg: 'any' 任何一个, 'all' 全部都有，默认, 'none' 都没有

		if (arg === undefined || arg === "all") {
			if (!permissions.every((p) => myPermissions.includes(p))) {
				el.style.display = "none"
			}
		} else if (arg === "any") {
			if (!permissions.some((p) => myPermissions.includes(p))) {
				el.style.display = "none"
			}
		} else if (arg === "none") {
			if (permissions.some((p) => myPermissions.includes(p))) {
				el.style.display = "none"
			}
		}
	},
}

// 该方法可以在项目初始化的时候先设置默认权限
export function setDefaultPermissions(permissions) {
	defaultPermissions = permissions
}

export function auth(value, arg) {
	if (!value) {
		return true
	}

	if (typeof value === "string" || value instanceof String) {
		value = [value]
	}

	let [permissions, myPermissions] = value

	if (!myPermissions) {
		myPermissions = defaultPermissions
	}

	if (typeof permissions === "string" || permissions instanceof String) {
		permissions = [permissions]
	}

	// arg: 'any' 任何一个, 'all' 全部都有，默认, 'none' 都没有

	if (arg === undefined || arg === "all") {
		if (!permissions.every((p) => myPermissions.includes(p))) {
			return false
		}
	} else if (arg === "any") {
		if (!permissions.some((p) => myPermissions.includes(p))) {
			return false
		}
	} else if (arg === "none") {
		if (permissions.some((p) => myPermissions.includes(p))) {
			return false
		}
	}
	return true
}
