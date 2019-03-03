/** When your routing table is too long, you can split it into small modules**/

import LayoutVue from '@/views/layout/Layout'
import UserVue from '@/views/system/user'
import RoleVue from '@/views/system/role'

const rbacRouter = {
	path: '/system',
	component: LayoutVue,
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
			component: UserVue,
			name: 'systemUser',
			meta: {title: 'systemUser',icon: 'user',roles: ['superadmin']}
		},
		{
			path: 'role',
			component: RoleVue,
			name: 'systemRole',
			meta: {title: 'systemRole',icon: 'role',roles: ['superadmin']}
		}
	]
}

export default rbacRouter
