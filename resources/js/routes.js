import Vue from 'vue'
import VueRouter from 'vue-router'
Vue.use(VueRouter);

const routes = [
    {
        path: '/',
        name: 'homepage',
        component: (resolve) => require(['../vue/homepage/homepage.vue'], resolve),
    },
    {
        path: '*',
        component: (resolve) => require(['../vue/homepage/homepage.vue'], resolve),
    },
    {
        path: '/login',
        name: 'login',
        component: (resolve) => require(['../vue/user/login_page.vue'], resolve),
    },
    {
        path: '/forum/:forum_id/:page?',
        name: 'forum',
        props: route => ({
            forum_id: parseInt(route.params.forum_id),
            page: parseInt(route.params.page),
        }),
        component: (resolve) => require(['../vue/forum/forum_page.vue'], resolve),
    },
    {
        path: '/thread/:thread_id/:page?',
        name: 'thread',
        props: route => ({
            thread_id: parseInt(route.params.thread_id),
            page: parseInt(route.params.page),
        }),
        component: (resolve) => require(['../vue/thread/thread_page.vue'], resolve),
    },
    {
        path: '/new_thread/:forum_id',
        name: 'new_thread',
        props: route => ({
            forum_id: parseInt(route.params.forum_id),
        }),
        component: (resolve) => require(['../vue/thread/new_thread.vue'], resolve),
    },
    {
        path: '/user-center',
        name: 'user-center',
        component: (resolve) => require(['../vue/user/user_center.vue'], resolve),
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