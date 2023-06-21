<template>
	<a-button v-bind="config" :type="type" :style="style" :loading="fetcher.loading" @click="clickEvent" v-auth="auth">
		<template v-if="icon" #icon>
			<NewbieIcon :icon="icon" />
		</template>
		<slot>{{ label }}</slot>
	</a-button>
</template>

<script setup>
import NewbieIcon from "./NewbieIcon.vue"

defineProps({
	type: { type: String, default: "" },
	icon: { type: String, default: "" },
	label: { type: String, default: "" },
	auth: { type: [String, Array], default: "" },
	fetcher: {
		type: Object,
		default: () => ({ loading: false }),
	},
	style: {
		type: Object,
		default: () => ({}),
	},
	config: {
		type: Object,
		default: () => ({}),
	},
})

const emits = defineEmits(["click"])
const clickEvent = (e) => {
	emits("click", e)
}
</script>
<style lang="less" scoped>
.ex-button {
	margin: 16px auto;
	width: 80%;
}
</style>
