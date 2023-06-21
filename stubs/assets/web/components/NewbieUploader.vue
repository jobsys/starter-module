<template>
	<Upload
		v-model:file-list="state.fileList"
		v-bind="defaultProps"
		:accept="accept"
		:headers="headers"
		:class="{ 'newbie-uploader': true, 'is-image': isImage }"
		:list-type="type"
		:disabled="disabled"
		:action="action"
		:data="extraData"
		:multiple="multiple"
		:progress="state.progress"
		:before-upload="uploadBeforeHandler"
		:custom-request="uploadAction"
		@preview="previewFile"
		@change="changeFile"
	>
		<template v-if="isImage">
			<div v-if="!isOverflow && !disabled">
				<NewbieIcon style="font-size: 30px" :icon="disabled ? 'PictureOutlined' : 'CloudUploadOutlined'" />
				<div v-if="!disabled">上传</div>
			</div>
		</template>
		<NewbieButton v-if="!isImage && !isOverflow && !disabled" icon="CloudUploadOutlined" :label="uploadText" type="primary" />
	</Upload>
	<Image
		:style="{ display: 'none' }"
		:preview="{
			visible: state.previewVisible,
			onVisibleChange: setPreviewVisible,
		}"
		:src="state.previewUrl"
	/>
</template>

<script setup>
import { computed, onMounted, reactive } from "vue"
import { Form, Image, message, Upload } from "ant-design-vue"
import { isArray } from "lodash-es"
import { STATUS } from "@/js/hooks/common/network"
import Resumable from "resumablejs"
import NewbieButton from "./NewbieButton.vue"
import NewbieIcon from "./NewbieIcon.vue"

const { STATE_CODE_SUCCESS } = STATUS

const props = defineProps({
	modelValue: {
		type: [Object, Array, String],
		default() {
			return {
				path: "",
				url: "",
			}
		},
	},
	headers: {
		type: Object,
		default() {
			return {}
		},
	},
	accept: {
		type: String,
		default: "",
	},
	type: {
		// text, picture 和 picture-card
		type: String,
		default: "picture-card",
	},
	maxSize: {
		// 默认上传不超过20M
		type: Number,
		default: 20,
	},
	maxNum: {
		// 上传最大次数，默认1个
		type: Number,
		default: 1,
	},
	disabled: {
		type: Boolean,
		default: false,
	},
	multiple: {
		// 允许上传多个
		type: Boolean,
		default: false,
	},
	action: {
		// 上传URL
		type: String,
		default: "",
	},
	extraData: {
		// 额外提交的数据
		type: Object,
		default() {
			return {}
		},
	},
	uploadText: {
		type: String,
		default: "上传",
	},
	defaultProps: {
		type: Object,
		default: () => ({}),
	},
})
const emits = defineEmits(["update:modelValue", "success"])
const formItemContext = Form.useInjectFormItemContext()

const state = reactive({
	fileList: [],
	previewVisible: false,
	previewUrl: "",
	progress: {
		strokeColor: {
			"0%": "#108ee9",
			"100%": "#87d068",
		},
		strokeWidth: 3,
		format: (percent) => `${parseFloat(percent.toFixed(2))}%`,
	},
})

const isOverflow = computed(() => {
	return state.fileList.length >= props.maxNum
})

const isImage = computed(() => {
	return props.type === "picture-card"
})

const updateValue = (value) => {
	let val = []
	const newV = value
	if (!isArray(newV)) {
		val = [newV]
	} else {
		val = [].concat(newV)
	}
	state.fileList =
		(val &&
			val
				.filter((item) => item.path)
				.map((item) => {
					return {
						name: item.name || "附件",
						uid: -parseInt(String(Math.random() * 10000), 10),
						path: item.path,
						url: item.url,
					}
				})) ||
		[]
}

onMounted(() => {
	// 控制这个组件的渲染时间
	updateValue(props.modelValue)
})

const setPreviewVisible = (value) => {
	state.previewVisible = value
}

const previewFile = (file) => {
	if (file.thumbUrl) {
		state.previewUrl = file.url
		state.previewVisible = true
	} else {
		window.open(file.url)
	}
}
const submitFile = (list) => {
	let tempList = list
		.filter((item) => item.isDone || !!item.path)
		.map((item) => {
			return {
				path: item.path,
				url: item.url,
				name: item.name,
				id: item.id,
			}
		})
	if (props.maxNum === 1) {
		;[tempList] = tempList
		if (!tempList) {
			tempList = {
				path: "",
				url: "",
			}
		}
	}
	emits("update:modelValue", tempList)
	emits("success", tempList)

	formItemContext.onFieldChange()
}
const changeFile = ({ file, fileList }) => {
	fileList = fileList
		.map((item) => {
			if (item.status === "done" && item.response) {
				const res = item.response
				if (res.result.path) {
					item.path = res.result.path
					item.name = res.result.name
					item.url = res.result.url
					if (res.result.id) {
						item.id = res.result.id
					}
					item.isDone = true
				} else {
					item.isRemove = true
				}
			} else if (item.status === "error") {
				item.isRemove = true
			}
			return item
		})
		.filter((item) => !item.isRemove)

	if (file.status === "done" && file.response) {
		if (file.response.status && file.response.status !== STATE_CODE_SUCCESS) {
			message.error(file.response.result || "上传失败，请稍后再试")
		} else if (file.response.result.path) {
			submitFile(fileList)
		}
	} else if (file.status === "removed") {
		submitFile(fileList)
	} else if (file.status === "error" && file.error) {
		if (file.error.status === 413) {
			message.error("413， 超过服务器上传附件的大小限制")
		} else {
			message.error(file.error.message || "上传失败，请稍后再试")
		}
	} else if (!file.status) {
		let index = -1
		fileList.forEach((item, i) => {
			if (item.uid === file.uid) {
				index = i
			}
		})
		if (index >= 0) {
			fileList.splice(index, 1)
		}
	}

	state.fileList = fileList
}
const uploadBeforeHandler = (file) => {
	if (file.size > props.maxSize * 1024 * 1024) {
		message.error(`最大只能上传${props.maxSize}M 的文件`)
		return false
	}
	if (isOverflow.value) {
		message.error("已超过上传限制个数")
		return false
	}

	return true
}

const clearFileList = () => {
	console.log("fileList cleared!")
	state.fileList = []
}
// 上传行为
const uploadAction = async ({ action, data, file, headers, onError, onProgress, onSuccess }) => {
	const resumable = new Resumable({
		// Use chunk size that is smaller than your maximum limit due a resumable issue
		// https://github.com/23/resumable.js/issues/51
		chunkSize: 3 * 1024 * 1024,
		simultaneousUploads: 10,
		testChunks: false,
		throttleProgressCallbacks: 1,
		// Get the url from data-url tag
		target: action,
		// Append token to the request - required for web routes
		query: data,
		headers,
	})

	resumable.on("fileAdded", () => {
		// trigger when file picked
		resumable.upload() // to actually start uploading.
	})

	resumable.on("fileProgress", (uploadFile) => {
		onProgress({ percent: uploadFile.progress() * 100 })
	})

	resumable.on("fileSuccess", (uploadFile, uploadMessage) => {
		onSuccess(JSON.parse(uploadMessage), uploadFile)
	})

	resumable.on("fileError", (uploadMessage) => {
		onError(uploadMessage)
	})

	resumable.addFile(file)
}

defineExpose({ updateValue, clearFileList })
</script>

<style lang="less" scoped>
.newbie-uploader {
	:deep(.ant-upload-list-file) {
		width: 300px;
	}

	&.is-image {
		min-height: 104px;

		:deep(.ant-upload-list-item, .ant-upload-list-picture-card-container, .ant-upload.ant-upload-select-picture-card > .ant-upload) {
			min-height: 104px;
		}
	}

	:deep(.ant-upload-list-item.ant-upload-list-item-list-type-file) {
		min-height: auto;
	}
}
</style>
