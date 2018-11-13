import request from '@/utils/request'

// 用户相关的api函数
export function API_FetchGameCategoryList(query) {
	return request({
		url: '/game-category/index',
		method: 'get',
		params: query
	})
}

export function API_DoGameCategorySave(data) {
	return request({
		url: '/game-category/save',
		method: 'post',
		data
	})
}

export function API_DoGameCategoryDelete(data) {
	return request({
		url: '/game-category/delete',
		method: 'post',
		data
	})
}