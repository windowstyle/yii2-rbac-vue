import Vue from 'vue'
import Vuex from 'vuex'
import VueI18n from 'vue-i18n'
import Cookies from 'js-cookie'

import elementEnLocale from 'element-ui/lib/locale/lang/en'
import elementZhLocale from 'element-ui/lib/locale/lang/zh-CN'
import enLocale from './lang/en'
import zhLocale from './lang/zh'

import app from './store/app'
import user from './store/user'
import country from './store/country'
import getters from './store/getters'
import tagsView from './store/tagsView'
import permission from './store/permission'

import SvgIcon from '../components/SvgIcon'

// 引入本地存储

Vue.use(Vuex)

export const store = new Vuex.Store({
    modules: {
        app,
        user,
        country,
        tagsView,
        permission
    },
    getters
})

// 引入国际化

Vue.use(VueI18n)

const messages = {
    en: {
        ...enLocale,
        ...elementEnLocale
    },
    zh: {
        ...zhLocale,
        ...elementZhLocale
    }
}

export const i18n = new VueI18n({locale: Cookies.get('language') || 'zh', messages})

// 注册svg图标

Vue.component('svg-icon', SvgIcon)

const req = require.context('./icons', false, /\.svg$/)

const requireAll = requireContext => requireContext.keys().map(requireContext)

requireAll(req)