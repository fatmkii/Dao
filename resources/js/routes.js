import Vue from 'vue'
import VueRouter from 'vue-router'

import homepage from '../vue/homepage/homepage.vue'
import login_page from '../vue/user/login_page.vue'
import forum_page from '../vue/forum/forum_page.vue'
import thread_page from '../vue/thread/thread_page.vue'
import new_thread from '../vue/thread/new_thread.vue'
import user_center from '../vue/user/user_center.vue'

Vue.use(VueRouter);

const routes = [
    {
        path: '/',
        name: 'homepage',
        component: homepage,
    },
    {
        path: '*',
        component: homepage,
    },
    {
        path: '/login',
        name: 'login',
        component: login_page,
    },
    {
        path: '/forum/:forum_id/:page?',
        name: 'forum',
        props: route => ({
            forum_id: parseInt(route.params.forum_id),
            page: parseInt(route.params.page),
        }),
        component: forum_page,
    },
    {
        path: '/thread/:thread_id/:page?',
        name: 'thread',
        props: route => ({
            thread_id: parseInt(route.params.thread_id),
            page: parseInt(route.params.page),
        }),
        component: thread_page,
    },
    {
        path: '/new_thread/:forum_id',
        name: 'new_thread',
        props: route => ({
            forum_id: parseInt(route.params.forum_id),
        }),
        component: new_thread,
    },
    {
        path: '/user-center',
        name: 'user-center',
        component: user_center,
    },
]

const router = new VueRouter({
    mode: 'history',
    routes: routes, // (缩写) 相当于 routes: routes
    //控制前端路由的滚动
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        } else {
            return { x: 0, y: 0, behavior: 'smooth', }
        }
    }
})

export default router