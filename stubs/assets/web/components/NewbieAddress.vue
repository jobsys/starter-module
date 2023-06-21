<template>
	<Cascader
		v-model:value="state.value"
		allow-clear
		:placeholder="placeholder"
		:disabled="disabled"
		:options="state.items"
		:display-render="displayRender"
		change-on-select
		show-search
		@change="change"
	/>
</template>
<script setup>
import { reactive } from "vue"
import { Cascader } from "ant-design-vue"
import data from "./regionData.json"

const props = defineProps({
	modelValue: {
		type: [String, Number, Array],
		default() {
			return []
		},
	},
	level: {
		// 生成哪个级别的数据
		type: Number,
		default: 3,
	},
	disabled: {
		type: Boolean,
		default: false,
	},
	placeholder: {
		type: String,
		default: "",
	},
	url: {
		// 获取数据的链接
		type: String,
		default: "",
	},
	queryKey: {
		// 获取数据的KEY
		type: String,
		default: "code",
	},
	items: {
		type: Array,
		default() {
			return []
		},
	},
})

const state = reactive({
	items: props.items,
	value: props.modelValue,
})

const emits = defineEmits(["update:modelValue"])

if (state.items.length === 0) {
	state.items = data
}

const displayRender = ({ selectedOptions }) => {
	const item = selectedOptions.length ? selectedOptions[selectedOptions.length - 1] : ""
	return (item && item.alias) || ""
}
const change = (value) => {
	emits("update:modelValue", value)
}
</script>
