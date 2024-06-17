<template>
	<div class="h-screen flex justify-center body-bg" :style="{ backgroundImage: 'url(' + Background + ')' }">
		<div class="flex flex-col items-center w-5/6 sm:w-2/3 md:w-2/5 lg:w-1/3 xl:w-1/4">
			<img :src="logoUrl" class="mt-10 mb-8 rounded h-1/5" />
			<div
				class="w-full shadow-lg rounded bg-white transition duration-700 ease-in-out overflow-hidden hover:shadow-xl"
				@keydown.enter="doLogin"
			>
				<h1 class="text-center font-bold text-xl mt-10 md:text-2xl">欢迎使用</h1>
				<div class="mx-auto my-2 w-24 border-b-2 bg-gray-300 h-0.5 2xl:my-6"></div>
				<a-form ref="form" :model="state.loginForm" :rules="state.loginFormRules">
					<div class="px-8 py-4 md:py-4">
						<input type="password" style="display: none" />
						<a-form-item name="name">
							<a-input v-model:value="state.loginForm.name" placeholder="请输入用户名" autocomplete="false" size="large">
								<template #prefix>
									<UserOutlined></UserOutlined>
								</template>
							</a-input>
						</a-form-item>
						<a-form-item name="password">
							<a-input-password
								v-model:value="state.loginForm.password"
								autocomplete="new-password"
								placeholder="请输入密码"
								size="large"
							>
								<template #prefix>
									<LockOutlined></LockOutlined>
								</template>
							</a-input-password>
						</a-form-item>
						<a-form-item name="captcha">
							<a-input v-model:value="state.loginForm.captcha" placeholder="请计算右侧算式答案" size="large">
								<template #prefix>
									<CalculatorOutlined></CalculatorOutlined>
								</template>
								<template #addonAfter>
									<img
										class="captcha_code"
										style="cursor: pointer"
										alt="点击更新"
										title="看不清？请点击更新"
										:src="state.captchaUrl"
										@click="refreshVerifyCode"
									/>
								</template>
							</a-input>
						</a-form-item>
					</div>
					<div class="px-10 py-6 bg-gray-100 border-t border-gray-100 flex justify-center items-center">
						<a-form-item style="margin-bottom: 0">
							<NewbieButton :fetcher="state.loginFetcher" type="primary" :button-props="{ size: 'large' }" block @click="doLogin"
								>登录
							</NewbieButton>
						</a-form-item>
					</div>
				</a-form>
			</div>

			<!--			<div v-else class="w-full shadow-lg rounded bg-white transition duration-700 ease-in-out overflow-hidden hover:shadow-xl">
				<h1 class="text-center font-bold text-xl mt-10 md:text-2xl">请选择您的角色</h1>
				<div class="mx-auto my-2 w-24 border-b-2 bg-gray-300 h-0.5 2xl:my-6"></div>
				<div class="py-3" v-if="state.roleOptions && state.roleOptions.length">
					<div
						class="flex items-center mb-1 px-4 py-2 mx-4 rounded cursor-pointer hover:bg-gray-100 hover:font-bold"
						v-for="option in state.roleOptions"
						:key="option.value"
						@click="goRedirect(option.value)"
					>
						<div class="rounded p-2 shadow bg-emerald-600 text-white">
							<UserOutlined :style="{ fontSize: '30px' }"></UserOutlined>
						</div>
						<div class="ml-2">
							{{ option.label }}
						</div>
					</div>
				</div>
			</div>-->
		</div>
	</div>
</template>

<script setup>
import { inject, onMounted, reactive, ref } from "vue"
import { message } from "ant-design-vue"
import { CalculatorOutlined, LockOutlined, UserOutlined } from "@ant-design/icons-vue"
import { useFetch, useProcessStatus, useSm3 } from "jobsys-newbie/hooks"
import { cloneDeep } from "lodash-es"
import Background from "@public/images/backgrounds/sun-tornado-dark-blue.svg"
import { useLandCustomerAsset } from "@/js/hooks/land"

const route = inject("route")
const http = inject("http")
const form = ref(null)
const logoUrl = useLandCustomerAsset("/images/default/logo-large.png")

/*
const props = defineProps({
	roleOptions: {
		type: Array,
		default: () => [],
	},
})
*/

const state = reactive({
	//isPending: true,
	loginFetcher: { loading: false },
	loginForm: {
		name: "",
		password: "",
		captcha: "",
		key: "",
		remember: true,
	},
	loginFormRules: {
		name: [
			{
				required: true,
				message: "请填写用户名",
				trigger: "blur",
			},
		],
		password: [
			{
				required: true,
				message: "请填写密码",
				trigger: "blur",
			},
			{
				min: 6,
				max: 30,
				message: "长度在 6 到 30 个字符",
				trigger: "blur",
			},
		],
		captcha: [
			{
				required: true,
				message: "请填写验证码",
				trigger: "blur",
			},
		],
	},
	captchaUrl: "",
	roleOptions: [],
})

const refreshVerifyCode = async () => {
	const url = `${route().t.url}/captcha/api/math`
	const res = await http.get(url)
	state.loginForm.key = res.key
	state.captchaUrl = res.img
}

const doLogin = () => {
	form.value.validate().then(async () => {
		try {
			const data = cloneDeep(state.loginForm)
			data.password = useSm3(data.password)
			const res = await useFetch(state.loginFetcher).post(route("api.login"), data)
			useProcessStatus(res, {
				SUCCESS: () => {
					message.success("登录成功")
					location.href = route("page.manager.dashboard")
				},
				FAIL: () => {
					message.error(res.result)
					refreshVerifyCode()
				},
			})
		} catch (e) {
			await refreshVerifyCode()
		}
	})
}

onMounted(() => {
	refreshVerifyCode()
	/*if (props.roleOptions && props.roleOptions.length) {
		state.roleOptions = props.roleOptions
		state.isPending = false
	} else {
		refreshVerifyCode()
	}*/
})
</script>

<style lang="less" scoped>
.body-bg {
	background-size: 100%;
	/*background-color: #9921e8;
	background-image: linear-gradient(315deg, #9921e8 0%, #5f72be 74%);*/
}

.ant-form {
	:deep(.ant-input-group-addon) {
		overflow: hidden;
		padding: 0;
	}
}
</style>
