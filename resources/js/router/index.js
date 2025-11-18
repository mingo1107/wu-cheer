import { createRouter, createWebHistory } from 'vue-router';
import Login from '../pages/Login.vue';
import Layout from '../components/Layout.vue';
import { useToast } from '../composables/useToast.js';

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
          requiresAuth: true,
          requiresAdmin: true
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
        path: 'cleaner',
        name: 'Cleaner',
        component: () => import('../pages/Cleaner.vue'),
        meta: {
          title: '清運業者管理 | 土方石清運管理系統',
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
        path: 'earth-data',
        name: 'EarthData',
        component: () => import('../pages/EarthData.vue'),
        meta: {
          title: '土單資料管理 | 土方石清運管理系統',
          requiresAuth: true
        }
      },
      {
        path: 'earth-usage',
        name: 'EarthUsage',
        component: () => import('../pages/EarthUsage.vue'),
        meta: {
          title: '土單使用明細 | 土方石清運管理系統',
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
      },
      {
        path: 'verifier',
        name: 'Verifier',
        component: () => import('../pages/Verifier.vue'),
        meta: {
          title: '核銷人員管理 | 土方石清運管理系統',
          requiresAuth: true,
          requiresAdmin: true
        }
      },
      {
        path: 'user-logs',
        name: 'UserLogs',
        component: () => import('../pages/UserLog.vue'),
        meta: {
          title: '使用者操作紀錄 | 土方石清運管理系統',
          requiresAuth: true,
          requiresAdmin: true
        }
      },
    ]
  },
  {
    path: '/verifier/login',
    name: 'VerifierLogin',
    component: () => import('../pages/verifierPlatform/Login.vue'),
    meta: { 
      title: '核銷平台登入 | 土方石清運管理系統',
      requiresVerifierAuth: false 
    }
  },
  {
    path: '/verifier/dashboard',
    name: 'VerifierDashboard',
    component: () => import('../pages/verifierPlatform/Dashboard.vue'),
    meta: { 
      title: '核銷作業 | 土方石清運管理系統',
      requiresVerifierAuth: true
    }
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

  // 檢查是否為核銷平台路由
  const isVerifierRoute = to.path.startsWith('/verifier');
  
  if (isVerifierRoute) {
    // 核銷平台路由處理
    const verifierData = localStorage.getItem('verifier_user');
    const verifierToken = localStorage.getItem('verifier_token');
    const isVerifierAuthenticated = verifierData !== null && verifierToken !== null;

    // 如果已登入且要前往登入頁，重導向到儀表板
    if (to.path === '/verifier/login' && isVerifierAuthenticated) {
      next('/verifier/dashboard');
      return;
    }

    // 檢查是否需要核銷平台認證
    if (to.meta.requiresVerifierAuth) {
      if (!isVerifierAuthenticated) {
        // 未登入，重導向到核銷平台登入頁
        next('/verifier/login');
        return;
      }
    }
  } else {
    // CMS 後台路由處理
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

      // 檢查是否需要管理員權限
      if (to.meta.requiresAdmin) {
        try {
          const user = JSON.parse(userData);
          const isAdmin = user.role === 0;
          
          if (!isAdmin) {
            // 非管理員，重導向到儀表板並顯示錯誤訊息
            const { error: showErrorToast } = useToast();
            showErrorToast('您沒有權限訪問此頁面');
            next('/dashboard');
            return;
          }
        } catch (err) {
          console.error('解析使用者資料失敗:', err);
          next('/login');
          return;
        }
      }
    }
  }

  next();
});

export default router;
