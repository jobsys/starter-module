import { Modal } from "ant-design-vue"

export function useModalConfirm(msg, okFn, loading, cancelFn) {
	const modal = Modal.confirm({
		title: "提示",
		content: msg || "",
		onOk: () => {
			if (okFn) {
				okFn()
			}
			if (loading) {
				modal.update({
					okButtonProps: {
						loading: true,
					},
				})
				return new Promise(() => {})
			}
			return null
		},
		onCancel: () => {
			if (cancelFn) {
				cancelFn()
			}
		},
	})
	return modal
}
