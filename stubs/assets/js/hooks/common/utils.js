export function useFlattenWithChildren(arr, childrenKey = "children") {
	const result = []
	arr.forEach((item) => {
		result.push(item)
		if (item[childrenKey]) {
			result.push(...useFlattenWithChildren(item[childrenKey], childrenKey))
		}
	})
	return result
}
