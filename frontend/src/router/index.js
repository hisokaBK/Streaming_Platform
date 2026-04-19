import { createRouter, createWebHistory } from 'vue-router'

import LoginPage from '@/pages/auth/LoginPage.vue'
import RegisterPage from '@/pages/auth/RegisterPage.vue'

import MyProfilePage from '@/pages/Profile/MyProfilePage.vue'
import UserProfilePage from '@/pages/Profile/UserProfilePage.vue'

import StreamsPage from '@/pages/streams/StreamsPage.vue'
import StreamShowPage from '@/pages/streams/StreamShowPage.vue'
import CreateStreamPage from '@/pages/streams/CreateStreamPage.vue'
import EditStreamPage from '@/pages/streams/EditStreamPage.vue'

import VideosPage from '@/pages/videos/VideosPage.vue'
import VideoShowPage from '@/pages/videos/VideoShowPage.vue'

import MessagesPage from '@/pages/messages/MessagesPage.vue'

import NotificationsPage from '@/pages/notifications/NotificationsPage.vue'

import DashboardPage from '@/pages/admin/DashboardPage.vue'
import UsersPage from '@/pages/admin/UsersPage.vue'
import CategoriesPage from '@/pages/admin/CategoriesPage.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage,
  },

  {
    path: '/',
    name: 'streams',
    component: StreamsPage,
  },
  {
    path: '/streams',
    name: 'streams-list',
    component: StreamsPage,
  },
  {
    path: '/streams/:id',
    name: 'stream-show',
    component: StreamShowPage,
    props: true,
  },
  {
    path: '/streams/create',
    name: 'stream-create',
    component: CreateStreamPage,
    meta: { requiresAuth: true },
  },
  {
    path: '/streams/:id/edit',
    name: 'stream-edit',
    component: EditStreamPage,
    props: true,
    meta: { requiresAuth: true },
  },

  {
    path: '/videos',
    name: 'videos',
    component: VideosPage,
  },
  {
    path: '/videos/:id',
    name: 'video-show',
    component: VideoShowPage,
    props: true,
  },

  {
    path: '/profile',
    name: 'my-profile',
    component: MyProfilePage,
    meta: { requiresAuth: true },
  },
  {
    path: '/profile/:id',
    name: 'user-profile',
    component: UserProfilePage,
    props: true,
  },

  {
    path: '/messages',
    name: 'messages',
    component: MessagesPage,
    meta: { requiresAuth: true },
  },

  {
    path: '/notifications',
    name: 'notifications',
    component: NotificationsPage,
    meta: { requiresAuth: true },
  },

  {
    path: '/admin/dashboard',
    name: 'admin-dashboard',
    component: DashboardPage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: UsersPage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/categories',
    name: 'admin-categories',
    component: CategoriesPage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const storedUser = JSON.parse(localStorage.getItem('user') || 'null')

  const role = storedUser?.role || storedUser?.user?.role

  if (to.meta.requiresAuth && !token) {
    return next({ name: 'login' })
  }

  if (to.meta.requiresAdmin && role !== 'admin') {
    return next({ name: 'streams' })
  }

  next()
})

export default router