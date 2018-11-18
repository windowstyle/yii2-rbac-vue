// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import Element from 'element-ui'
import Cookies from 'js-cookie'

import App from './App'
import router from './router'
import {store,i18n} from './resources'

import 'element-ui/lib/theme-chalk/index.css'
import 'normalize.css/normalize.css'
import './resources/styles/index.scss'

import './utils/permission'

Vue.use(Element, {size: Cookies.get('size') || 'medium', i18n: (key, value) => i18n.t(key, value)})

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
    el: '#app',
    router,
    store,
    i18n,
    components: {App},
    template: '<App/>'
})
