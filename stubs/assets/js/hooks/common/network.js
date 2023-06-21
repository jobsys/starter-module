import axios from "axios"

export function useFetch(fetcher) {
	if (!fetcher) {
		fetcher = {}
	}
	fetcher.loading = true

	return {
		get(url, config) {
			return new Promise((resolve, reject) => {
				axios
					.get(url, config)
					.then((res) => {
						resolve(res)
					})
					.catch((err) => {
						reject(err)
					})
					.finally(() => {
						fetcher.loading = false
					})
			})
		},
		post(url, data, config) {
			return new Promise((resolve, reject) => {
				axios
					.post(url, data, config)
					.then((res) => {
						resolve(res)
					})
					.catch((err) => {
						reject(err)
					})
					.finally(() => {
						fetcher.loading = false
					})
			})
		},
		load(url, data) {
			return new Promise((resolve, reject) => {
				axios
					.post(url, data)
					.then((res) => {
						resolve(res)
					})
					.catch((err) => {
						reject(err)
					})
					.finally(() => {
						fetcher.loading = false
					})
			})
		},
	}
}

export const STATUS = {
	STATE_CODE_SUCCESS: "SUCCESS", // 成功
	STATE_CODE_FAIL: "FAIL", // 失败
	STATE_CODE_NOT_FOUND: "NOT_FOUND", // 找不到资源
	STATE_CODE_INFO_NOT_COMPLETE: "INCOMPLETE", // 信息不完整
	STATE_CODE_NOT_ALLOWED: "NOT_ALLOWED", //没有权限
}

export const usePage = async (pagination, refresh, process) => {
	pagination.finishedText = "加载完毕"
	pagination.loading = true

	if (!pagination.uri) {
		console.error("URI is required in pagination")
		return
	}

	let res = {}
	try {
		res = await axios.get(pagination.uri, {
			params: { ...(pagination.params || {}), page: refresh || !pagination.page ? 1 : pagination.page + 1 },
		})
	} catch (e) {
		pagination.loading = false
		pagination.finished = false
		pagination.error = true
		return
	}

	if (res.status !== STATUS.STATE_CODE_SUCCESS) {
		pagination.loading = false
		pagination.error = true
		pagination.errorText = res.result
		return
	}

	let items = res.result.data

	if (process) {
		items = process(res.result.data)
	}

	if (!refresh && pagination.items) {
		items = pagination.items.concat(items)
	}

	pagination.loading = false
	pagination.error = false
	pagination.page = res.result.current_page
	pagination.items = items
	pagination.finished = res.result.last_page === 0 || res.result.last_page === res.result.current_page
	pagination.empty = res.result.total === 0
}
