import { createRouter, createWebHistory } from 'vue-router';
import Login from '../pages/Login.vue';
import Layout from '../components/Layout.vue';

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { 
      title: '登入 | 土方石清運管理系統',
      requiresAuth: false 
    }
  },
  {
    path: '/',
    component: Layout,
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../pages/Dashboard.vue'),
        meta: { 
          title: '儀表板 | 土方石清運管理系統',
          requiresAuth: true 
        }
      },
      {
        path: 'profile',
        name: 'Profile',
        component: () => import('../pages/Profile.vue'),
        meta: { 
          title: '個人資料 | 土方石清運管理系統',
          requiresAuth: true 
        }
      },
      {
        path: 'user',
        name: 'User',
        component: () => import('../pages/User.vue'),
        meta: {
          title: '使用者管理 | 土方石清運管理系統',
          requiresAuth: true
        }
      },
      {
        path: 'customer',
        name: 'Customer',
        component: () => import('../pages/Customer.vue'),
        meta: {
          title: '客戶管理 | 土方石清運管理系統',
          requiresAuth: true
        }
      },
      {
        path: 'announcement',
        name: 'Announcement',
        component: () => import('../pages/Announcement.vue'),
        meta: {
          title: '公告欄 | 土方石清運管理系統',
          requiresAuth: true
        }
      },
      {
        path: 'case',
        name: 'Case',
        component: () => import('../pages/Case.vue'),
        meta: {
          title: '案件管理 | 土方石清運管理系統',
          requiresAuth: true
        }
      },
      {
        path: 'earth-data',
        name: 'EarthData',
        component: () => import('../pages/EarthData.vue'),
        meta: {
          title: '土單資料管理 | 土方石清運管理系統',
          requiresAuth: true
        }
      },
      {
        path: 'earth-recycle',
        name: 'EarthRecycle',
        component: () => import('../pages/EarthRecycle.vue'),
        meta: {
          title: '土單回收作業 | 土方石清運管理系統',
          requiresAuth: true
        }
      },
      {
        path: 'earth-statistics',
        name: 'EarthStatistics',
        component: () => import('../pages/EarthStatistics.vue'),
        meta: {
          title: '土單使用統計表 | 土方石清運管理系統',
          requiresAuth: true
        }
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../pages/NotFound.vue'),
    meta: { 
      title: '頁面不存在 | 土方石清運管理系統'
    }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// 路由守衛 - 檢查認證狀態
router.beforeEach((to, from, next) => {
  // 設定頁面標題
  if (to.meta.title) {
    document.title = to.meta.title;
  }

  // 檢查使用者認證狀態
  const userData = localStorage.getItem('user');
  const token = localStorage.getItem('token');
  const isAuthenticated = userData !== null && token !== null;

  // 如果已登入且要前往登入頁，重導向到儀表板
  if (to.path === '/login' && isAuthenticated) {
    next('/dashboard');
    return;
  }

  // 檢查是否需要認證
  if (to.meta.requiresAuth) {
    if (!isAuthenticated) {
      // 未登入，重導向到登入頁
      next('/login');
      return;
    }
  }

  next();
});

export default router;
