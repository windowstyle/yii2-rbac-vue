/** When your routing table is too long, you can split it into small modules**/

import Layout from '@/views/layout/Layout'

const gameRouter = {
	path: '/game',
	component: Layout,
	redirect: '/game/index',
	name: 'game',
	meta: {
		title: 'game',
		icon: 'list',
		roles: ['superadmin']
	},
	children: [
		{
			path: 'index',
			component: () => import('@/views/game/index'),
			name: 'gameIndex',
			meta: {title: 'gameIndex',icon: 'user',roles: ['superadmin']}
		},
		{
			path: 'category',
			component: () => import('@/views/game/category'),
			name: 'gameCategory',
			meta: {title: 'gameCategory',icon: 'role',roles: ['superadmin']}
		}
	]
}

export default gameRouter
