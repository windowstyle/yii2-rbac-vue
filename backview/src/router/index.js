import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

/* Layout */
import LayoutVue from '../views/layout/Layout'
import RedirectVue from '@/views/redirect/index'
import LoginVue from '@/views/login/index'
import DashboardVue from '@/views/dashboard/index'

/* Router Modules */
import systemRouter  from './modules/system'

export const asyncRouterMap = [
    systemRouter,

    {path: '*', redirect: 'dashboard', hidden: true}
]

export const constantRouterMap = [
    {
        path: '/redirect',
        component: LayoutVue,
        hidden: true,
        children: [
            {
                path: '/redirect/:path*',
                component: RedirectVue
            }
        ]
    },
    {
        path: '/login',
        component: LoginVue,
        hidden: true
    },
    {
        path: '',
        component: LayoutVue,
        redirect: 'dashboard',
        children: [
            {
                path: 'dashboard',
                component: DashboardVue,
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
