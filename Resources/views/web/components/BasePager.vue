<template>
	<a-page-header class="mb-4" :class="[props.ghost ? 'ghost' : 'hover-card']" :ghost="props.ghost" :sub-title="props.subTitle">
		<template v-if="props.pageTitle || props.titleIcon" #title>
			<component v-if="props.titleIcon" :is="props.titleIcon"></component>
			{{ props.pageTitle }}
		</template>
		<template v-if="titleTag" #tags>
			<a-tag :color="props.titleTagColor">{{ props.titleTag }}</a-tag>
		</template>
		<template v-if="back" #extra>
			<a-button type="primary" ghost :icon="h(ArrowLeftOutlined)" @click="onBack"> 返回</a-button>
		</template>
		<div
			v-if="props.descriptions?.length || props.statistics?.length || slots.descriptions || slots.statistics"
			class="flex justify-between items-center flex-wrap"
		>
			<template v-if="slots.descriptions">
				<slot name="descriptions"></slot>
			</template>
			<template v-else-if="props.descriptions?.length">
				<a-descriptions
					size="small"
					bordered
					:style="{ width: genPixel(props.descriptionWidth) }"
					:column="props.descriptionColumn"
					:label-style="{ textAlign: 'right' }"
					:content-style="{ textAlign: 'left' }"
				>
					<template v-for="(item, index) in props.descriptions" :key="index">
						<a-descriptions-item :label="`${item.label} :`" :span="item.span || 1">
							<template v-if="isFunction(item.value)">
								<component :is="item.value()" />
							</template>
							<template v-else>
								{{ item.value }}
							</template>
						</a-descriptions-item>
					</template>
				</a-descriptions>
			</template>

			<template v-if="slots.statistics">
				<slot name="statistics"></slot>
			</template>
			<template v-else-if="props.statistics?.length">
				<div
					class="flex items-end justify-end text-center gap-y-[10px] pr-8"
					:style="{ width: genPixel(props.statisticsWidth), columnGap: genPixel(props.statisticsGap) }"
				>
					<template v-for="(item, index) in props.statistics" :key="index">
						<div v-if="item.component === 'divider'" class="w-[1px] h-[80px] bg-gray-200"></div>
						<component v-else :is="generateChart(item)"></component>
					</template>
				</div>
			</template>
		</div>
	</a-page-header>
	<a-result v-if="props.error" status="warning" title="操作提醒" :sub-title="props.error"></a-result>
	<div v-else class="mt-4">
		<slot></slot>
	</div>
</template>

<script setup>
import { h } from "vue"
import { isFunction, isNull, isUndefined, merge } from "lodash-es"
import { ArrowLeftOutlined } from "@ant-design/icons-vue"
import { useGoBack } from "@/js/hooks/land"
import { VueUiKpi, VueUiSparkgauge } from "vue-data-ui"
import { Tooltip } from "ant-design-vue"

const props = defineProps({
	error: { type: String, default: "" },
	ghost: { type: Boolean, default: false },
	pageTitle: { type: String, default: "" },
	titleIcon: { type: Object, default: () => null },
	subTitle: { type: String, default: "" },
	titleTag: { type: String, default: "" },
	titleTagColor: { type: String, default: "blue" },
	back: { type: [String, Function], default: "" },
	descriptions: {
		type: Array,
		default: () => [],
	},
	descriptionColumn: {
		type: Number,
		default: 2,
	},
	descriptionWidth: {
		type: [Number, String],
		default: 700,
	},
	statistics: {
		type: Array,
		default: () => [],
	},
	statisticsWidth: {
		type: [Number, String],
		default: 580,
	},
	statisticsGap: {
		type: Number,
		default: 40,
	},
})

const slots = defineSlots()

const genPixel = (num) => {
	if (isNull(num) || isUndefined(num)) {
		return "auto"
	}
	if (!isNaN(num)) {
		return `${num}px`
	}

	return num
}

const generateChart = (item) => {
	let chart = null
	if (item.component === "VueUiSparkgauge") {
		chart = h(VueUiSparkgauge, {
			style: { width: "80px", flexShrink: 0 },
			dataset: getChartDataset(item),
			config: getChartConfig(item),
		})
	}
	if (item.component === "VueUiKpi") {
		chart = h(VueUiKpi, {
			dataset: getChartDataset(item),
			config: getChartConfig(item),
		})
	}

	if (item.tooltip) {
		if (isFunction(item.tooltip)) {
			return item.tooltip({ chart })
		}
		return h(Tooltip, { title: item.tooltip }, () => chart)
	}
	return chart
}

const getChartDataset = (item) => {
	if (isFunction(item.dataset)) {
		return item.dataset()
	}
	return isUndefined(item.dataset) ? {} : item.dataset
}
const getChartConfig = (item) => {
	if (item.component === "VueUiSparkgauge") {
		if (item.title) {
			item.dataset.title = item.title
		}
		return merge(
			{
				style: {
					title: {
						bold: false,
						fontSize: 14,
						position: "bottom",
					},
					dataLabel: {
						fontSize: 30,
					},
					colors: {
						min: "#8ddb6c",
						max: "#389e0d",
					},
				},
			},
			item.config || {},
		)
	}
	if (item.component === "VueUiKpi") {
		return merge(
			{
				valueFontSize: 36,
				titleBold: false,
				titleFontSize: 14,
				layoutClass: "flex justify-end min-w-[100px] flex-col-reverse",
			},
			item.title ? { title: item.title } : {},
			item.config || {},
		)
	}

	return item.config || {}
}

const onBack = () => {
	if (isFunction(props.back)) {
		props.back()
	} else {
		useGoBack(props.back)
	}
}
</script>

<style lang="less">
.ant-page-header {
	padding: 10px 12px !important;
	background: #fff;

	&.ghost {
		padding: 0 !important;
		background: transparent !important;
	}

	.ant-descriptions {
		&.ant-descriptions-bordered {
			.ant-descriptions-view {
				border: none !important;
			}

			tr {
				border-bottom: none !important;
			}

			td,
			th {
				border-inline-end: none !important;
			}

			.ant-descriptions-item-label {
				background: #fff;
				white-space: nowrap;
				vertical-align: baseline;
				font-weight: 500;
			}

			.ant-descriptions-item-content {
				padding: 8px 0 !important;
				vertical-align: baseline;
				min-width: 100px;
			}
		}
	}
}
</style>
