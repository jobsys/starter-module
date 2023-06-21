<template>
	<div class="newbie-tags">
		<template v-for="(tag, index) in state.tags" :key="tag">
			<a-tooltip v-if="tag.length > 20" :title="tag">
				<a-tag :closable="index !== 0" @close="onClose(tag)">
					{{ `${tag.slice(0, 20)}...` }}
				</a-tag>
			</a-tooltip>
			<a-tag v-else :closable="closable" @close="onClose(tag)">
				{{ tag }}
			</a-tag>
		</template>
		<a-input
			v-if="state.inputVisible"
			ref="inputRef"
			v-model:value="state.inputValue"
			type="text"
			size="small"
			:style="{ width: '78px' }"
			@blur="onConfirm"
			@keyup.enter="onConfirm"
		/>
		<a-tag v-else style="background: #fff; border-style: dashed" @click="showInput"> <PlusOutlined></PlusOutlined> 新标签 </a-tag>
	</div>
</template>

<script setup>
import { nextTick, reactive, ref } from "vue"
import { PlusOutlined } from "@ant-design/icons-vue"

const props = defineProps({
	modelValue: {
		type: Array,
		default() {
			return []
		},
	},
	disabled: {
		type: Boolean,
		default: false,
	},

	closable: {
		type: Boolean,
		default: true,
	},
})

const emits = defineEmits(["update:modelValue"])

const inputRef = ref(null)

const state = reactive({
	tags: props.modelValue,
	inputVisible: false,
	inputValue: "",
})

const onClose = (removedTag) => {
	state.tags = state.tags.filter((tag) => tag !== removedTag)
	emits("update:modelValue", state.tags)
}
const showInput = () => {
	state.inputVisible = true
	nextTick(() => {
		inputRef.value.focus()
	})
}
const onConfirm = () => {
	const { inputValue } = state
	let { tags } = state
	if (inputValue && tags.indexOf(inputValue) === -1) {
		tags = [...tags, inputValue]
	}
	Object.assign(state, {
		tags,
		inputVisible: false,
		inputValue: "",
	})

	emits("update:modelValue", state.tags)
}
</script>
