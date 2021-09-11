import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const router = new VueRouter({
    mode: 'history',
    base: '/',
    routes: [
        {
            path: '/',
            name: 'dashboard',
            component: require('./pages/Dashboard/Index').default,
        },
    ]
})

export default router