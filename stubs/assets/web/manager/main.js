import { createApp, h } from "vue"
import { createInertiaApp } from "@inertiajs/vue3"
import { ZiggyVue } from "ziggy-js/dist/vue"
import "ant-design-vue/dist/antd.less"
import "simplebar-vue/dist/simplebar.min.css"
import "./styles/style.less"
import { ConfigProvider } from "ant-design-vue"
import zhCN from "ant-design-vue/es/locale/zh_CN"
import dayjs from "dayjs"
import "dayjs/locale/zh-cn"
import useUserStore from "@manager/stores/user"
import { createPinia } from "pinia"
import { directives, http } from "@/js/plugins"
import Layout from "./shared/CommonLayout.vue"

dayjs.locale("zh-cn")
const pinia = createPinia()

function resolvePage(pageUri) {
	const [page, module] = pageUri.split("@")

	let pages
	let pagePath
	if (!module) {
		pages = import.meta.glob("./views/**/*.vue", { eager: true })
		pagePath = `./views/${page}.vue`
	} else {
		pages = import.meta.glob("/Modules/*/Resources/views/web/**/*.vue", { eager: true })
		pagePath = `/Modules/${module}/Resources/views/web/${page}.vue`
	}

	const resolvedPage = pages[pagePath]
	if (typeof resolvedPage === "undefined") {
		throw new Error(`Page not found: ${pagePath}`)
	}

	resolvedPage.default.layout = page.startsWith("Nude") ? undefined : Layout

	return resolvedPage
}

createInertiaApp({
	progress: {
		color: "#9921e8",
	},
	title: (title) => `${title}`,
	resolve: (name) => resolvePage(name),
	setup({ el, App, props, plugin }) {
		const app = createApp({
			render: () => {
				return h(
					ConfigProvider,
					{
						locale: zhCN,
					},
					() => [h(App, props)],
				)
			},
		})
			.use(plugin)
			.use(http)
			.use(pinia)

		// 先初始化用户信息再挂载App
		const userStore = useUserStore()
		userStore.init(window.starterInit || {})

		app.use(directives, [{ name: "auth", defaultPermissions: userStore.permissions }])

		app.config.globalProperties.$http.get("api/ziggy").then((routes) => {
			app.use(ZiggyVue, routes)

			app.mount(el)
		})
	},
})
