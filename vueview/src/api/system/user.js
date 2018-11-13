import request from '@/utils/request'

// 用户相关的api函数
export function API_FetchUserList(query) {
	return request({
		url: '/wyvue/user/index',
		method: 'get',
		params: query
	})
}

export function API_DoUserSave(data) {
	return request({
		url: '/wyvue/user/save',
		method: 'post',
		data
	})
}

export function API_DoUserDelete(data) {
	return request({
		url: '/wyvue/user/delete',
		method: 'post',
		data
	})
}

export function API_DoUserSaveRole(data) {
	return request({
		url: '/wyvue/user/save-role',
		method: 'post',
		data
	})
}