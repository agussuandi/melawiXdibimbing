import { createRouter, createWebHistory } from 'vue-router'

export default createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: () => import("../views/Customers/Index.vue"),
        },
        {
            path: '/customers',
            component: () => import("../views/Customers/Index.vue"),
        },
        {
            path: '/customers/create',
            component: () => import("../views/Customers/Create.vue"),
        },
        {
            path: '/customers/:id/edit',
            component: () => import("../views/Customers/Edit.vue"),
        },
        {
            path: '/customer-receipts',
            component: () => import("../views/CustomerReceipts/Index.vue"),
        },
        {
            path: '/materials',
            component: () => import("../views/Materials/Index.vue"),
        },
        {
            path: '/sales-order',
            component: () => import("../views/SalesOrder/Index.vue"),
        },
    ],
})