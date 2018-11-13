import request from '@/utils/request'

// 用户相关的api函数
export function API_FetchRoleList(query) {
	return request({
		url: '/wyvue/auth/index',
		method: 'get',
		params: query
	})
}

export function API_DoRoleSave(data) {
	return request({
		url: '/wyvue/auth/save',
		method: 'post',
		data
	})
}


export function API_DoRoleDelete(data) {
	return request({
		url: '/wyvue/auth/delete',
		method: 'post',
		data
	})
}

export function API_DoRefreshRoutes() {
	return request({
		url: '/wyvue/auth/refresh-routes',
		method: 'get'
	})
}

export function API_DoSavePermissions(data) {
	return request({
		url: '/wyvue/auth/save-permissions',
		method: 'post',
		data
	})
}
