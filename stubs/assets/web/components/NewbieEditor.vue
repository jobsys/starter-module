<template>
	<div style="border: 1px solid #f0f0f0">
		<Toolbar :editor="editorRef" :default-config="toolbarConfig" style="border-bottom: 1px solid #f0f0f0" />
		<Editor
			v-model="valueHtml"
			:default-config="editorConfig"
			:style="{ height: height + 'px', overflowY: 'hidden' }"
			@on-created="handleCreated"
			@on-change="handleChange"
			@custom-alert="customAlert"
		/>
	</div>
</template>

<script setup>
import "@wangeditor/editor/dist/css/style.css"
import { nextTick, onBeforeUnmount, onMounted, ref, shallowRef, inject } from "vue"
import { Editor, Toolbar } from "@wangeditor/editor-for-vue"
import { message, Form } from "ant-design-vue"
import { useFetch } from "@/js/hooks/web/network"
import { useProcessStatusSuccess } from "@/js/hooks/web/form"

const props = defineProps({
	modelValue: {
		type: String,
		default: "",
	},
	disabled: {
		type: Boolean,
		default: false,
	},
	config: {
		type: Object,
		default() {
			return {}
		},
	},
	height: {
		type: Number,
		default: 400,
	},
})

const emits = defineEmits(["update:modelValue"])

const editorUploadURL = inject("NewbieEditorUploadURL")

const formItemContext = Form.useInjectFormItemContext()

// 编辑器实例，必须用 shallowRef，重要！
const editorRef = shallowRef()

// 内容 HTML
const valueHtml = ref(props.modelValue)

const toolbarConfig = {
	excludeKeys: ["group-video", "emotion"],
}
const editorConfig = {
	placeholder: "请输入内容...",
	readonly: props.disabled,
	MENU_CONF: {
		uploadImage: {
			async customUpload(file, insertFn) {
				const formData = new FormData()
				formData.append("file", file)

				try {
					const res = await useFetch().post(editorUploadURL, formData)
					useProcessStatusSuccess(res, () => {
						const { url, alt, href } = res.result
						insertFn(url, alt, href)
					})
				} catch (e) {
					console.log(e)
				}
			},
		},
	},
}

// 组件销毁时，也及时销毁编辑器，重要！
onBeforeUnmount(() => {
	const editor = editorRef.value
	if (editor == null) return

	editor.destroy()
})

onMounted(() => {
	nextTick(() => {
		const editor = editorRef.value
		editor.on("fullScreen", () => {
			document.body.classList.add("fullscreen")
		})
		editor.on("unFullScreen", () => {
			document.body.classList.remove("fullscreen")
		})
	})
})

const setContent = (content) => {
	valueHtml.value = content
}
// 编辑器回调函数
const handleCreated = (editor) => {
	editorRef.value = editor // 记录 editor 实例，重要！
}
const handleChange = (editor) => {
	let html = editor.getHtml()
	if (editor.isEmpty()) {
		html = ""
	}
	if (html !== props.modelValue) {
		emits("update:modelValue", html)
		formItemContext.onFieldChange()
	}
}
const customAlert = (info, type) => {
	switch (type) {
		case "success":
			message.success(info)
			break
		case "info":
			message.info(info)
			break
		case "warning":
			message.warning(info)
			break
		case "error":
			message.error(info)
			break
		default:
			message.info(info)
			break
	}
}

defineExpose({ setContent })
</script>
