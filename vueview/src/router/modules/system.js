/** When your routing table is too long, you can split it into small modules**/

import Layout from '@/views/layout/Layout'

const rbacRouter = {
	path: '/system',
	component: Layout,
	redirect: '/system/user',
	name: 'system',
	meta: {
		title: 'system',
		icon: 'list',
		roles: ['superadmin']
	},
	children: [
		{
			path: 'user',
			component: () => import('@/views/system/user'),
			name: 'systemUser',
			meta: {title: 'systemUser',icon: 'user',roles: ['superadmin']}
		},
		{
			path: 'role',
			component: () => import('@/views/system/role'),
			name: 'systemRole',
			meta: {title: 'systemRole',icon: 'role',roles: ['superadmin']}
		}
	]
}

export default rbacRouter
