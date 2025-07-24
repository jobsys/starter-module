<template>
	<NewbieModal v-model:visible="state.showEditorModal" type="drawer" width="1400" :title="props.title">
		<div v-if="props.receivers?.length">
			<div class="mb-6!">
				<a-card title="已选择的接收对象" size="small" class="hover-card mb-4!">
					<a-tag color="green" v-for="receiver in receivers" :key="receiver.value">{{ receiver.label }} </a-tag>
				</a-card>
			</div>
		</div>
		<slot name="receivers" :submit-form="submitForm"></slot>
		<NewbieForm
			ref="formRef"
			:fetch-url="state.url"
			:auto-load="!!state.url"
			:submit-url="route('api.manager.starter.message-batch.edit')"
			:submit-disabled="!$auth('api.manager.starter.message-batch.edit')"
			:card-wrapper="false"
			:form="formColumns()"
			:before-submit="onBeforeSubmit"
			:close="() => (state.showEditorModal = false)"
			@success="onSubmitSuccess"
		>
			<template #cron>
				<a-form-item label="循环周期定制">
					<div class="bg-gray-100 p-4 rounded">
						<a-row class="mb-2!">
							<a-col :span="4" class="text-center">分钟</a-col>
							<a-col :span="4" class="text-center">小时</a-col>
							<a-col :span="4" class="text-center">日期</a-col>
							<a-col :span="4" class="text-center">月份</a-col>
							<a-col :span="4" class="text-center">星期</a-col>
						</a-row>
						<a-row>
							<a-col :span="4" class="text-center">
								<a-select style="width: 100px" v-model:value="state.cronForm.minute">
									<a-select-option value="0">0</a-select-option>
									<a-select-option value="30">30</a-select-option>
								</a-select>
							</a-col>
							<a-col :span="4" class="text-center">
								<a-select style="width: 100px" v-model:value="state.cronForm.hour">
									<a-select-option value="-">每小时</a-select-option>
									<a-select-option v-for="i in 24" :key="i" :value="String(i - 1)">{{ i - 1 }} </a-select-option>
								</a-select>
							</a-col>
							<a-col :span="4" class="text-center">
								<a-select style="width: 100px" v-model:value="state.cronForm.day">
									<a-select-option value="-">每天</a-select-option>
									<a-select-option v-for="i in 31" :key="i" :value="String(i)">{{ i }} </a-select-option>
								</a-select>
							</a-col>
							<a-col :span="4" class="text-center">
								<a-select style="width: 100px" v-model:value="state.cronForm.month">
									<a-select-option value="-">每月</a-select-option>
									<a-select-option v-for="i in 12" :key="i" :value="String(i)">{{ i }} </a-select-option>
								</a-select>
							</a-col>
							<a-col :span="4" class="text-center">
								<a-select style="width: 100px" v-model:value="state.cronForm.weekday">
									<a-select-option value="-">不限</a-select-option>
									<a-select-option value="1">星期一</a-select-option>
									<a-select-option value="2">星期二</a-select-option>
									<a-select-option value="3">星期三</a-select-option>
									<a-select-option value="4">星期四</a-select-option>
									<a-select-option value="5">星期五</a-select-option>
									<a-select-option value="6">星期六</a-select-option>
									<a-select-option value="7">星期日</a-select-option>
								</a-select>
							</a-col>
						</a-row>
						<div class="ml-8 mt-4 bg-white p-2 rounded">
							<span class="text-red-500 mr-4">当前发送频率:</span>
							<span class="font-bold">{{ cronExpression }}</span>
						</div>
					</div>
				</a-form-item>
			</template>
		</NewbieForm>
	</NewbieModal>
</template>
<script setup>
import { computed, reactive, ref, inject, useSlots } from "vue"
import { useFetch, useProcessStatusSuccess } from "jobsys-newbie/hooks"
import { message } from "ant-design-vue"

const props = defineProps({
	title: { type: String, default: "消息编辑" },
	receiverType: { type: String, default: "" },
	receivers: { type: Array, default: () => [] },
	channels: { type: Array, default: () => [] },
	beforeSubmit: { type: Function, default: null },
})

const emits = defineEmits(["success"])
const slots = useSlots()

const route = inject("route")

const formRef = ref()

const state = reactive({
	url: "",
	showEditorModal: false,
	allChannels: [],
	receiverTypeOptions: [],
	cronForm: {
		minute: "0",
		hour: "-",
		day: "-",
		month: "-",
		weekday: "-",
	},
})

const submitForm = computed(() => formRef.value?.getFormRealtime() || {})

const cronExpression = computed(() => {
	const { minute, hour, day, month, weekday } = state.cronForm
	let expression = ""
	if (month !== "-") {
		expression += `每年${month}月份`
	}

	if (weekday !== "-") {
		if (!expression) {
			expression += `每`
		}
		expression += `星期${["一", "二", "三", "四", "五", "六", "日"][Number(weekday) - 1]}`
	}

	if (day !== "-" && weekday === "-") {
		if (month === "-") {
			expression += `每个月`
		}
		expression += `${day}号`
	}

	if (hour !== "-") {
		if (day === "-" && weekday === "-") {
			expression += `每天`
		}
		expression += `${hour}点`
	} else {
		expression += `每小时`
	}

	expression += `${minute}分`

	return expression
})

const enabledChannels = computed(() => {
	if (props.channels?.length) {
		return state.allChannels.filter((item) => props.channels.includes(item.value))
	}
	return state.allChannels
})

const fetchChannels = async () => {
	const res = await useFetch().get(route("api.manager.starter.message.config"))

	useProcessStatusSuccess(res, () => {
		state.allChannels = res.result.channels
		state.receiverTypeOptions = res.result.receiver_types
	})
}

fetchChannels()

const open = () => {
	if (!props.receivers?.length && !slots.receivers) {
		message.warning("请先设定接收对象")
		return
	}
	state.showEditorModal = true
}

defineExpose({ open })

const onBeforeSubmit = ({ formatForm }) => {
	if (formatForm.send_type === "schedule") {
		formatForm.send_params = { send_at: formatForm.send_at }
	}

	if (formatForm.send_type === "cron") {
		formatForm.send_params = {
			cron: `${state.cronForm.minute} ${state.cronForm.hour} ${state.cronForm.day} ${state.cronForm.month} ${state.cronForm.weekday}`,
		}
	}

	debugger

	if (props.receiverType) {
		formatForm.receiver_type = props.receiverType
	}

	if (props.receivers?.length) {
		formatForm.receivers = props.receivers.map((item) => item.value)
	}

	if (props.beforeSubmit) {
		return props.beforeSubmit({ formatForm })
	}
	return formatForm
}

const onSubmitSuccess = (res) => {
	useProcessStatusSuccess(res, () => {
		state.showEditorModal = false
		emits("success")
	})
}

const formColumns = () => [
	{
		title: "接收对象类型",
		key: "receiver_type",
		type: "select",
		options: state.receiverTypeOptions,
		required: true,
		width: 300,
		hidden: () => Boolean(props.receiver_type),
	},
	{
		title: "发送渠道",
		key: "channels",
		type: "checkbox",
		required: true,
		options: enabledChannels.value,
		defaultValue: ["database"],
		help: "可多选，至少选择一种渠道",
	},
	{
		title: "标题",
		key: "title",
		width: "100%",
		required: true,
	},
	{
		title: "内容",
		key: "content",
		type: "textarea",
		required: true,
		defaultProps: {
			rows: 10,
		},
	},
	{
		title: "何时发送",
		key: "send_type",
		type: "radio",
		options: [
			{ label: "立即发送", value: "immediate" },
			{ label: "延迟发送", value: "schedule" },
			{ label: "循环定时发送", value: "cron" },
		],
		defaultValue: "immediate",
	},
	{
		title: "选择发送时间",
		key: "send_at",
		type: "date",
		hidden: (submitForm) => submitForm.send_type !== "schedule",
		init: ({ existingData }) => existingData.send_params?.send_at,
		required: true,
		defaultProps: {
			showTime: { format: "HH:mm" },
			format: "YYYY-MM-DD HH:mm",
		},
	},
	{
		title: "循环周期定制",
		key: "cron",
		type: "slot",
		hidden: (submitForm) => submitForm.send_type !== "cron",
	},
	{
		title: "是否启用",
		key: "is_active",
		type: "switch",
		defaultValue: true,
		options: ["是", "否"],
		help: "不启用的消息批次不会被发送",
	},
]
</script>
