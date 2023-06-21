import { auth } from "@/js/directives"
import { setDefaultPermissions, auth as authorize } from "@/js/directives/auth"

export default {
	install(app, options) {
		options.forEach((directive) => {
			if (directive.name === "auth") {
				app.directive("auth", auth)
				app.config.globalProperties.$auth = authorize
				app.provide("auth", authorize)
				if (directive.defaultPermissions) {
					setDefaultPermissions(directive.defaultPermissions)
				}
			}
		})
	},
}
