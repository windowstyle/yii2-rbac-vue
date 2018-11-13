import request from '@/utils/request'

// 用户相关的api函数
export function API_FetchGameList(query) {
	return request({
		url: '/game/index',
		method: 'get',
		params: query
	})
}

export function API_DoGameSave(data) {
	return request({
		url: '/game/save',
		method: 'post',
		data
	})
}

export function API_DoGameDelete(data) {
	return request({
		url: '/game/delete',
		method: 'post',
		data
	})
}