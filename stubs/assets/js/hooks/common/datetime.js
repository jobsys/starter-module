import dayjs from "dayjs"

export function useCreateDateFromFormat(date, format) {
	return format ? dayjs(date, format) : dayjs(date)
}

export function useDateFormat(date, format) {
	if (!date) {
		return ""
	}
	return dayjs(date).format(format || "YYYY-MM-DD HH:mm")
}

export function useDateUnix(date) {
	if (!date) {
		return ""
	}
	if (!dayjs.isDayjs(date)) {
		date = dayjs(date)
	}

	return date.unix()
}
