import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router/index.js';
import baseAPI from './api/base.js';

// 建立 Vue 應用程式
const app = createApp(App);

// 使用路由
app.use(router);

// 設定 API 重導向回調
baseAPI.setRedirectCallback((path) => {
    router.push(path);
});

// 掛載應用程式
app.mount('#app');
