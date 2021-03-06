import {getToken, setToken, removeToken} from '@/utils/auth'
import axios from '@/utils/request'

const user = {
	state: {
		user: '',
		name: '',
		status: '',
		token: getToken(),
		avatar: 'static/avatar.gif',
		introduction: '',
		roles: [],
		setting: {
			articlePlatform: []
		}
	},

	mutations: {
		SET_TOKEN: (state, token) => {
			state.token = token
		},
		SET_SETTING: (state, setting) => {
			state.setting = setting
		},
		SET_STATUS: (state, status) => {
			state.status = status
		},
		SET_NAME: (state, name) => {
			state.name = name
		},
		SET_ROLES: (state, roles) => {
			state.roles = roles
		}
	},

	actions: {
		// 用户名登录
		LoginByUsername({commit}, userInfo) {
		    let data = {username: userInfo.username.trim(),password: userInfo.password}
			return new Promise((resolve, reject) => {
                axios.post('/wyrbac/login/login',data).then(response => {
					const data = response.data
					commit('SET_TOKEN', data.token)
					setToken(response.data.token)
					resolve()
				}).catch(error => {
					reject(error)
				})
			})
		},

		// 获取用户信息
		GetUserInfo({commit, state}) {
			return new Promise((resolve, reject) => {
                axios.get('/wyrbac/login/user-info').then(response => {
					if (!response.data) { // 由于mockjs 不支持自定义状态码只能这样hack
						reject('error')
					}
					const data = response.data

					if (data.roles && data.roles.length > 0) { // 验证返回的roles是否是一个非空数组
						commit('SET_ROLES', data.roles)
					} else {
						reject('getInfo: roles must be a non-null array !')
					}

					commit('SET_NAME', data.name)
					resolve(response)
				}).catch(error => {
					reject(error)
				})
			})
		},

		// 登出
		LogOut({commit, state}) {
			return new Promise((resolve, reject) => {
                axios.get('/wyrbac/login/logout').then(() => {
					commit('SET_TOKEN', '')
					commit('SET_ROLES', [])
					removeToken()
					resolve()
				}).catch(error => {
					reject(error)
				})
			})
		},

		// 前端 登出
		FedLogOut({commit}) {
			return new Promise(resolve => {
				commit('SET_TOKEN', '')
				removeToken()
				resolve()
			})
		},

		// 动态修改权限
		ChangeRoles({commit, dispatch}, role) {
			return new Promise(resolve => {
				commit('SET_TOKEN', role)
				setToken(role)
                axios.get('/wyrbac/login/user-info').then(response => {
					const data = response.data
					commit('SET_ROLES', data.roles)
					commit('SET_NAME', data.name)
					dispatch('GenerateRoutes', data) // 动态修改权限后 重绘侧边菜单
					resolve()
				})
			})
		}
	}
}

export default user
