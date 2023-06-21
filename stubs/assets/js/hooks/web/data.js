import XLSX from "xlsx"

/**
 * 通过数组数据前端导出excel
 * @param {Object} options
 * @param {Array} options.columns - 数据格式，json 格式
 * @param {String} options.columns.*.title - 表头标题
 * @param {String | Function} options.columns.*.key? - 表头对应的数据的 key，如果是函数，返回值为函数的返回值
 * @param {String | Function} options.columns.*.dataIndex? - key 的别名，如果是函数，返回值为函数的返回值
 * @param {Array} options.data - 数据，json 格式
 * @param {String} options.fileName - 文件名
 * @param {Array} options.sum? - 合计，可选，只需要传入需要合计的列的 key，默认第一列为 “合计” 标题，不可以列入合计
 * @returns {void}
 *
 */
export function useExportArray(options) {
	if (!options.data || !options.columns || !options.fileName) throw new Error("缺少必需参数")
	const arr = options.data.map((record) => {
		return options.columns.map((column) => {
			column.key = column.dataIndex || column.key
			if (typeof column.key === "function") {
				return column.key(record)
			}
			return record[column.key]
		})
	})
	if (options.sum && options.sum.length) {
		const sum = options.columns.map((columns, index) => {
			if (index === 0) {
				return "合计"
			}
			if (options.sum.includes(columns.key)) {
				return arr.reduce((prev, cur) => {
					return prev + cur[index]
				}, 0)
			}
			//如果不需要合计的列就标记为 "-"
			return "-"
		})
		arr.push(sum)
	}

	arr.unshift(options.columns.map((i) => i.title))

	const ws = XLSX.utils.aoa_to_sheet(arr)
	const colWidth = arr.map((row) =>
		row.map((val) => {
			if (val == null) {
				return { wch: 10 }
			}
			if (val.toString().charCodeAt(0) > 255) {
				return { wch: val.toString().length * 2 }
			}
			return { wch: val.toString().length }
		}),
	)
	const result = colWidth[0]
	for (let i = 1; i < colWidth.length; i += 1) {
		for (let j = 0; j < colWidth[i].length; j += 1) {
			if (result[j].wch < colWidth[i][j].wch) {
				result[j].wch = colWidth[i][j].wch
			}
		}
	}
	ws["!cols"] = result
	const wb = XLSX.utils.book_new()
	XLSX.utils.book_append_sheet(wb, ws, options.fileName)
	XLSX.writeFile(wb, `${options.fileName}.xlsx`)
}
