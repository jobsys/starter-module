<template>
	<component :is="state.dom"></component>
</template>

<script setup>
import { reactive, h } from "vue"
import * as Icons from "@ant-design/icons-vue"

const IconFont = Icons.createFromIconfontCN({
	scriptUrl: "//at.alicdn.com/t/font_2037135_4379zha28h.js", // 在 iconfont.cn 上生成
})

const props = defineProps({
	icon: {
		type: String,
		default: "",
	},
	config: {
		type: Object,
		default() {
			return {}
		},
	},
})
const state = reactive({
	dom: null,
})

if (props.icon) {
	if (Icons[props.icon]) {
		state.dom = h(Icons[props.icon], props.config, null)
	} else {
		state.dom = h(IconFont, { type: props.icon, ...props.config }, null)
	}
}
</script>
