import Vue from 'vue'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import '../css/app.scss'
import VueRouter from 'vue-router'
import Vuex from 'vuex'
Vue.use(BootstrapVue)
Vue.use(IconsPlugin)
Vue.use(VueRouter)
Vue.use(Vuex)
import store from './store/store'
import router from './routes.js'

window.sha256 = require('js-sha256')
window.axios = require('axios')
window.CryptoJS = require("crypto-js");

// axios.defaults.withCredentials = false; // 在全局 axios 实例中启用 withCredentials 选项
// axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
//axios拦截器
axios.interceptors.response.use(
    (response) => {
        return response
    },
    error => {
        if (error.response.status !== undefined && error.response.status === 401) {
            localStorage.clear('Binggan')   //如果遇到401错误(用户未认证)，就清除Binggan和Token
            localStorage.clear('Token')
            delete axios.defaults.headers.Authorization;
            if (error.response.data !== undefined) {
                alert(error.response.data.message)
            } else {
                alert('此页面需先登录喔！');
            }
            window.location.href = '/login' //统一跳转到登陆页面
        } else {
            throw error
        }
    }
);

//全局通用导航栏
Vue.component('navigation', require('../vue/navigation.vue').default);
//全局app.vue，用来做一些全剧请求（forums，user等）
Vue.component('app', require('../vue/app.vue').default);
//全局通用底部
Vue.component('footer_navi', require('../vue/footer_navi.vue').default);

const app = new Vue({
    router,
    store
}).$mount('#app')