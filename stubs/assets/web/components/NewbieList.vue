<template>
	<div ref="container" class="newbie-list" :style="{ height: `${height}px` }">
		<a-list ref="list" v-bind="combinedListProps" :data-source="pagination.items || []">
			<template v-if="slots.renderItem" #renderItem="{ item, index }">
				<slot name="renderItem" :item="item" :index="index" />
			</template>
			<slot></slot>

			<template #loadMore>
				<div class="loading-container" v-if="pagination.loading">
					<a-spin />
				</div>
				<div class="finished-text" v-if="pagination.finished">{{ finishedText }}</div>
			</template>
		</a-list>
	</div>
</template>
<script setup>
import { computed, ref, useSlots, watch } from "vue"
import { usePage } from "@/js/hooks/web/network"
import { useScroll } from "@vueuse/core"

const slots = useSlots()

const container = ref(null)
const list = ref(null)

const props = defineProps({
	listProps: {
		// a-list 的 props
		type: Object,
		default() {
			return {}
		},
	},
	url: {
		type: String,
		default: "",
	},
	extraData: {
		type: Object,
		default() {
			return {}
		},
	},
	height: {
		type: Number,
		default: 300,
	},
	offset: {
		// 滚动条与底部距离小于 offset 时触发 load 事件
		type: Number,
		default: 50,
	},
	finishedText: {
		type: String,
		default: "数据加载完毕",
	},
	autoLoad: {
		type: Boolean,
		default: true,
	},
	useStore: {
		// 如果使用 store, 请确认 store 中定义了 pagination 和 initPagination 方法
		type: Object,
		default: null,
	},
})

const combinedListProps = computed(() => {
	return {
		...props.listProps,
	}
})

let pagination = null
if (props.useStore) {
	props.useStore.initPagination({
		uri: props.url,
		params: props.extraData,
	})
	pagination = computed(() => props.useStore.pagination)
} else {
	pagination = ref({
		uri: props.url,
		params: props.extraData,
	})
}

const loadMore = async (refresh) => {
	await usePage(pagination.value, refresh)
}

const items = () => {
	return pagination.value.items || []
}

const { y } = useScroll(container)

watch(y, async (value) => {
	if (value + props.height + props.offset >= container.value.scrollHeight && !pagination.value.loading && !pagination.value.finished) {
		await loadMore()
	}
})

if (props.autoLoad) {
	loadMore()
}

defineExpose({
	loadMore,
	items,
	pagination,
})
</script>

<style lang="less" scoped>
.newbie-list {
	background: #fff;
	padding: 10px;
	border-radius: 4px;
	overflow-y: scroll;
}

.loading-container {
	text-align: center;
	padding: 20px 0 10px;
}

.finished-text {
	text-align: center;
	font-size: 13px;
	padding: 10px 0;
	color: #999;
}
</style>
