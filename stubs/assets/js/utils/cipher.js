import { sm3 } from "sm-crypto"

// 非对称/公钥加密
export function encryptBySM2() {}

// 密码杂凑/散列
export function encryptBySM3(password) {
	return sm3(password, null) // 杂凑
}
