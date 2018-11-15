import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

/* Layout */
import Layout from '@/views/layout/Layout'

/* Router Modules */
import systemRouter  from './modules/system'

export const constantRouterMap = [
	{
		path: '/redirect',
		component: Layout,
		hidden: true,
		children: [
			{
				path: '/redirect/:path*',
				component: () => import('@/views/redirect/index')
			}
		]
	},
	{
		path: '/login',
		component: () => import('@/views/login/index'),
		hidden: true
	},
	{
		path: '/404',
		component: () => import('@/views/errorPage/404'),
		hidden: true
	},
	{
		path: '/401',
		component: () => import('@/views/errorPage/401'),
		hidden: true
	},
	{
		path: '',
		component: Layout,
		redirect: 'dashboard',
		children: [
			{
				path: 'dashboard',
				component: () => import('@/views/dashboard/index'),
				name: 'Dashboard',
				meta: {title: 'dashboard', icon: 'dashboard', noCache: true}
			}
		]
	}
]

export default new Router({
	scrollBehavior: () => ({y: 0}),
	routes: constantRouterMap
})

export const asyncRouterMap = [
	systemRouter,
	{path: '*', redirect: '/404', hidden: true}
]
