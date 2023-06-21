export function useFindParent(instance, { name }) {
	let parent = null
	while (instance.parent) {
		if (name && instance.parent.vnode.type.name === name) {
			parent = instance.parent
			break
		}
		instance = instance.parent
	}
	return parent
}
