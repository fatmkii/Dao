import Vue from 'vue'
import VueRouter from 'vue-router'

import homepage from '../vue/homepage/homepage.vue'
import login_page from '../vue/user/login_page.vue'
import forum_page from '../vue/forum/forum_page.vue'
import thread_page from '../vue/thread/thread_page.vue'
import new_thread from '../vue/thread/new_thread.vue'
import user_center from '../vue/user/user_center.vue'
import admin_login_page from '../vue/admin/admin_login_page.vue'
import admin_center from '../vue/admin/admin_center'
import check_user_post from '../vue/admin/check_user_post'
import emoji_contest from "../vue/emoji_contest/emoji_contest.vue"

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
        path: '/admin_login',
        name: 'admin_login',
        component: admin_login_page,
    },
    {
        path: '/admin_center',
        name: 'admin_center',
        component: admin_center,
    },
    {
        path: '/admin/check_user_post/:page?',
        name: 'check_user_post',
        props: route => ({
            page: parseInt(isNaN(route.params.page) ? 1 : route.params.page),
        }),
        component: check_user_post,
    },
    {
        path: '/forum/:forum_id/:page?',
        name: 'forum',
        props: route => ({
            forum_id: parseInt(route.params.forum_id),
            page: parseInt(isNaN(route.params.page) ? 1 : route.params.page),
        }),
        component: forum_page,
    },
    {
        path: '/thread/:thread_id/:page?',
        name: 'thread',
        props: route => ({
            thread_id: parseInt(route.params.thread_id),
            page: parseInt(isNaN(route.params.page) ? 1 : route.params.page),
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
    {
        path: '/emoji_moe',
        name: 'emoji_contest',
        component: emoji_contest,
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
            return { x: 0, y: 0, behavior: 'auto', }
        }
    }
})

export default router