import request from '@/utils/request'

// 用户相关的api函数
export function API_FetchDemoList(query) {
	return request({
		url: '/demo/index',
		method: 'get',
		params: query
	})
}

export function API_DoDemoSave(data) {
	return request({
		url: '/demo/save',
		method: 'post',
		data
	})
}

export function API_DoDemoDelete(data) {
	return request({
		url: '/demo/delete',
		method: 'post',
		data
	})
}