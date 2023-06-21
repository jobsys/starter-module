<template>
	<div>
		<a-input-password v-model:value="password" @change="onChange" :minlength="minLength" :maxlength="maxLength"></a-input-password>
		<div class="strength-indicator-container" :class="[classList[strength]]">
			<div class="strength-indicator" :class="[strength >= 1 ? 'active' : '']"></div>
			<div class="strength-indicator" :class="[strength >= 2 ? 'active' : '']"></div>
			<div class="strength-indicator" :class="[strength >= 3 ? 'active' : '']"></div>
			<div class="strength-indicator" :class="[strength >= 4 ? 'active' : '']"></div>
		</div>

		<div v-if="showError" class="error-message">{{ errorMessage }}</div>
	</div>
</template>

<script setup>
import { computed, ref, watch } from "vue"

const props = defineProps({
	modelValue: {
		type: String,
		default: "",
	},
	minLength: {
		type: Number,
		default: 8,
	},
	maxLength: {
		type: Number,
		default: 20,
	},
})

const emits = defineEmits(["update:modelValue", "change"])

const password = ref("")

const strength = computed(() => {
	let score = 0
	const regexList = [
		/\d/, // 包含数字
		/[a-z]/, // 包含小写字母
		/[A-Z]/, // 包含大写字母
		/[^a-zA-Z0-9]/, // 包含特殊符号
	]

	regexList.forEach((regex) => {
		if (regex.test(password.value)) {
			score += 1
		}
	})

	return score
})

const classList = ref(["", "bad", "weak", "medium", "strong"])

const onChange = (e) => {
	const { value } = e.target
	emits("change", e)
	emits("update:modelValue", value)
}

const showError = computed(() => {
	return (password.value.length > 0 && password.value.length < props.minLength) || password.value.length > props.maxLength || strength.value < 4
})

const errorMessage = computed(() => {
	if (password.value.length > 0 && password.value.length < props.minLength) {
		return `密码长度不能少于${props.minLength}个字符`
	}
	if (password.value.length > props.maxLength) {
		return `密码长度不能超过${props.maxLength}个字符`
	}
	if (strength.value < 4) {
		return "密码必须包含数字、小写字母、大写字母和特殊符号"
	}

	return ""
})

watch(
	() => props.modelValue,
	() => {
		password.value = props.modelValue
	},
)

defineExpose({ strength })
</script>

<style scoped lang="less">
.strength-indicator-container {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	margin-top: 5px;

	.strength-indicator {
		height: 5px;
		border: 1px solid #e8e8e8;
	}

	&.bad {
		.active {
			background: #ff0000;
		}
	}

	&.weak {
		.active {
			background: #ffa500;
		}
	}

	&.medium {
		.active {
			background: #ffff00;
		}
	}

	&.strong {
		.active {
			background: #008000;
		}
	}
}

.error-message {
	color: red;
	margin-top: 5px;
}
</style>
