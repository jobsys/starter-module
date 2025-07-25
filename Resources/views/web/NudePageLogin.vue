<template>
	<div class="h-screen bg-cover" :style="{ backgroundImage: 'url(' + backgroundUrl + ')' }">
		<div class="h-[80px] bg-white/50 flex items-center px-10">
			<img :src="logoUrl" class="h-[48px]" alt="" />
			<div class="ml-4">
				<span class="text-[24px] text-gray-700 tracking-widest">{{ props.appName }}</span>
			</div>
		</div>
		<div class="flex items-center justify-center mt-20 md:mt-20 lg:mt-30 xl:mt-40 transition-all duration-200">
			<div class="flex flex-col items-center w-5/6 sm:w-2/3 md:w-3/5 lg:w-2/5 xl:w-2/5 2xl:w-1/4 transition-all duration-200">
				<div
					class="w-full shadow-lg rounded bg-white transition duration-700 ease-in-out overflow-hidden hover:shadow-xl z-10"
					@keydown.enter="doLogin"
				>
					<h1 class="text-center text-xl mt-10 md:text-2xl mb-2!">欢迎使用</h1>
					<h1 class="text-center font-bold text-xl md:text-2xl mb-0!">{{ props.appName }}</h1>
					<div class="mx-auto w-24 border-b-2 bg-gray-300 h-0.5 my-2 2xl:my-6"></div>
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
			</div>

			<div class="text-gray-300 font-bold !py-2 !text-[12px] fixed bottom-0 !bg-transparent z-0">
				<p class="mb-1 text-center">职迅学生工作管理系统 ©版权所属</p>
				<p class="mb-0 text-center">
					技术支持： <a href="https://jobsys.cn" target="_blank" class="text-gray-300 font-bold">职迅科技 JOBSYS.cn</a>
				</p>
			</div>
		</div>
	</div>
</template>

<script setup>
import { inject, onMounted, reactive, ref } from "vue"
import { message } from "ant-design-vue"
import { CalculatorOutlined, LockOutlined, UserOutlined } from "@ant-design/icons-vue"
import { useFetch, useProcessStatus, useSm2 } from "jobsys-newbie/hooks"
import { cloneDeep } from "lodash-es"
import { useLandCustomerAsset } from "@/js/hooks/land"

const route = inject("route")
const http = inject("http")
const form = ref(null)
const logoUrl = useLandCustomerAsset("/images/default/logo-large.png")
const backgroundUrl = useLandCustomerAsset("/images/default/login-bg.png")

const props = defineProps({
	appName: { type: String, default: "" },
	sm2PublicKey: { type: String, default: "" },
})

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
			const sm2 = useSm2()
			data.password = sm2.doEncrypt(data.password, props.sm2PublicKey)
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
		} catch {
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
.ant-form {
	:deep(.ant-input-group-addon) {
		overflow: hidden;
		padding: 0;
	}
}
</style>
