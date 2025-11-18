import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router/index.js';
import baseAPI from './api/base.js';
import { useToast } from './composables/useToast.js';

// 建立 Vue 應用程式
const app = createApp(App);

// 使用路由
app.use(router);

// 設定 API 重導向回調
baseAPI.setRedirectCallback((path) => {
    router.push(path);
});

// 設置全域 Toast 觸發器（供 public/js/base.js 使用）
const { showToast } = useToast();
window.showToastNotification = (message, type = 'error') => {
    showToast(message, type, type === 'error' ? 5000 : 3000);
};

// 掛載應用程式
app.mount('#app');
