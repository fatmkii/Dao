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
import Echo from "laravel-echo"

//用js手动载入SocketIO.js，并且加载完再添加实例。好处是当SocketIO服务器端失效时，不影响整体网页。
var body = document.getElementsByTagName('body')[0];
var SocketJS = document.createElement('script');
SocketJS.setAttribute('type', 'text/javascript');
SocketJS.setAttribute('src', process.env.MIX_SOCKET_IO_HOST + "/socket.io/socket.io.js");
body.appendChild(SocketJS);
SocketJS.onload = () => {
    var echo_instance = new Echo({
        broadcaster: 'socket.io',
        host: process.env.MIX_SOCKET_IO_HOST,//从环境变量获取socket服务端
        namespace: '', //命名空间设置为''，使前端echo监听直接监听纯事件名
        authEndpoint: '/broadcasting/auth'
    });
    Vue.prototype.$echo = echo_instance

    echo_instance.connector.socket.on('connect', function () {
        window.axios.defaults.headers.common['X-Socket-Id'] = echo_instance.socketId();
    });

    //窗口关闭时，断开socket连接
    window.addEventListener("beforeunload", () => {
        echo_instance.connector.socket.disconnect(true);
    })
}

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
        if (!error.response) {
            console.log(error)
            throw error
        }
        // if (error.response.status !== undefined && error.response.status === 401) {
        //     localStorage.clear('Binggan')   //如果遇到401错误(用户未认证)，就清除Binggan和Token
        //     localStorage.clear('Token')
        //     delete axios.defaults.headers.Authorization;
        //     if (error.response.data !== undefined) {
        //         alert(error.response.data.message)
        //     } else {
        //         alert('此页面需先登录喔！');
        //     }
        //     window.location.href = '/login' //统一跳转到登陆页面
        // } else {
        alert(error.response.data.message);
        throw error
        // }
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
    store,
    data: {
        eventHub: new Vue()
    }
})
Vue.prototype.$eventHub = Vue.prototype.$eventHub || new Vue()
app.$mount('#app')