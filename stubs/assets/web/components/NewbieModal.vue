<template>
	<div class="newbie-modal">
		<Modal
			:footer="null"
			:wrap-style="{ overflow: 'hidden' }"
			:wrap-class-name="'newbie-modal' + (isFullModal ? ' full-modal' : '')"
			:width="isFullModal ? '100%' : width"
			v-model:visible="isVisible"
			v-if="type === 'modal'"
			v-bind="config"
			:closable="false"
			destroy-on-close
			:mask-closable="false"
			@cancel="onCloseModal"
		>
			<template #title>
				<div class="newbie-modal-header">
					<span class="newbie-modal-title" ref="modalTitleRef">{{ title }}</span>
					<div class="newbie-modal-actions">
						<a v-if="!isFullModal" @click="() => (isFullModal = true)">
							<ExpandOutlined :style="{ fontSize: '16px' }" />
						</a>
						<a v-else @click="() => (isFullModal = false)">
							<CompressOutlined :style="{ fontSize: '16px' }" />
						</a>

						<a @click="onCloseModal">
							<CloseOutlined :style="{ fontSize: '20px', marginTop: '2px' }" />
						</a>
					</div>
				</div>
			</template>
			<template #modalRender="{ originVNode }">
				<div :style="transformStyle">
					<component :is="originVNode" />
				</div>
			</template>
			<div :class="[isFullModal ? 'max-h-full' : 'max-h-[600px]', ' overflow-y-auto']">
				<slot></slot>
			</div>
		</Modal>
		<Drawer v-else :title="title" v-model:visible="isVisible" destroy-on-close :width="width" v-bind="config" @close="onCloseModal">
			<slot></slot>
		</Drawer>
	</div>
</template>

<script setup>
import { computed, ref, watch, watchEffect } from "vue"
import { useDraggable } from "@vueuse/core"
import { Drawer, Modal } from "ant-design-vue"
import { CloseOutlined, CompressOutlined, ExpandOutlined } from "@ant-design/icons-vue"

const props = defineProps({
	type: {
		type: String,
		default: "modal",
	},
	visible: {
		type: Boolean,
		default: false,
	},
	width: {
		type: Number,
		default: 800,
	},
	title: {
		type: String,
		default: "",
	},
	config: {
		type: Object,
		default: () => {
			return {}
		},
	},
})
const emits = defineEmits(["update:visible", "close"])

const modalTitleRef = ref(null)
const isFullModal = ref(false)
const isVisible = ref(props.visible)

watch(
	() => props.visible,
	(visible) => {
		isVisible.value = visible
	},
)

const onCloseModal = () => {
	isFullModal.value = false
	isVisible.value = false
	emits("update:visible", false)
	emits("close")
}

// 以下代码用于拖动弹窗，来自官方文档， https://www.antdv.com/components/modal-cn#components-modal-demo-modal-render
const { x, y, isDragging } = useDraggable(modalTitleRef)

const startX = ref(0)
const startY = ref(0)
const startedDrag = ref(false)
const transformX = ref(0)
const transformY = ref(0)
const preTransformX = ref(0)
const preTransformY = ref(0)
const dragRect = ref({
	left: 0,
	right: 0,
	top: 0,
	bottom: 0,
})
watch([x, y], () => {
	if (!startedDrag.value) {
		startX.value = x.value
		startY.value = y.value
		const bodyRect = document.body.getBoundingClientRect()
		const titleRect = modalTitleRef.value.getBoundingClientRect()
		dragRect.value.right = bodyRect.width - titleRect.width
		dragRect.value.bottom = bodyRect.height - titleRect.height
		preTransformX.value = transformX.value
		preTransformY.value = transformY.value
	}
	startedDrag.value = true
})
watch(isDragging, () => {
	if (!isDragging) {
		startedDrag.value = false
	}
})
watchEffect(() => {
	if (startedDrag.value) {
		transformX.value = preTransformX.value + Math.min(Math.max(dragRect.value.left, x.value), dragRect.value.right) - startX.value
		transformY.value = preTransformY.value + Math.min(Math.max(dragRect.value.top, y.value), dragRect.value.bottom) - startY.value
	}
})
const transformStyle = computed(() => {
	return {
		transform: `translate(${transformX.value}px, ${transformY.value}px)`,
	}
})
</script>

<style lang="less">
.newbie-modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;

	.newbie-modal-title {
		cursor: move;
		flex-grow: 1;
	}

	.newbie-modal-actions {
		display: flex;
		align-items: center;

		a {
			margin-left: 10px;
			cursor: pointer;
		}
	}
}

.newbie-modal {
	.ant-modal-body {
		padding: 6px 24px 32px;
	}
}

.full-modal {
	.ant-modal {
		max-width: 100%;
		top: 0;
		padding-bottom: 0;
		margin: 0;
	}

	.ant-modal-content {
		display: flex;
		flex-direction: column;
		height: calc(100vh);
	}

	.ant-modal-body {
		flex: 1;
	}
}
</style>
