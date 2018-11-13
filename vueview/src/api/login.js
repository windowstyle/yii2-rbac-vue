import request from '@/utils/request'

export function loginByUsername(username, password) {
	const data = {
		username,
		password
	}
	return request({
		url: '/wyvue/login/login',
		method: 'post',
		data
	})
}

export function logout() {
	return request({
		url: '/wyvue/login/logout',
		method: 'post'
	})
}

export function getUserInfo() {
	return request({
		url: '/wyvue/login/user-info',
		method: 'get'
	})
}

