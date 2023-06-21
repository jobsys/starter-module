/**
 *
 * @param {function} callback
 * @param {any?} input
 */
const useChain = async (callback, input) => {
	debugger
	const output = await callback({ input })
	return new Promise((resolve) => resolve({ output }))
}

export { useChain }
