# Vue 3 前端架構

## 概述

這是一個基於 **Vue 3** 和 **Laravel Vite** 的現代化前端架構，使用 Composition API 和 TypeScript 支援。

## 檔案結構

```
resources/js/
├── api/
│   ├── base.js          # 基礎 API 工具類別
│   └── user.js          # 使用者相關 API 服務
├── composables/
│   └── useAuth.js       # 認證相關的 Composable
├── components/
│   └── App.vue          # 主要 Vue 組件
├── app.js               # 主要入口檔案
└── README-Vue3.md       # 使用說明
```

## 快速開始

### 1. 開發環境

```bash
# 安裝依賴
npm install

# 啟動開發伺服器
npm run dev

# 建置生產版本
npm run build
```

### 2. 訪問測試頁面

```
http://localhost:8000/test-api
```

## Vue 3 特色功能

### 1. Composition API

使用 Vue 3 的 Composition API 提供更好的邏輯復用和型別推斷：

```javascript
// composables/useAuth.js
import { ref, computed, readonly } from 'vue';
import userAPI from '../api/user.js';

export function useAuth() {
    const user = ref(null);
    const isLoading = ref(false);
    const error = ref(null);

    const isAuthenticated = computed(() => !!user.value);

    const login = async (email, password) => {
        // 登入邏輯
    };

    return {
        user: readonly(user),
        isLoading: readonly(isLoading),
        error: readonly(error),
        isAuthenticated,
        login
    };
}
```

### 2. 在組件中使用

```vue
<template>
  <div>
    <div v-if="!isAuthenticated">
      <form @submit.prevent="handleLogin">
        <input v-model="loginForm.email" type="email" required>
        <input v-model="loginForm.password" type="password" required>
        <button type="submit" :disabled="isLoading">
          {{ isLoading ? '登入中...' : '登入' }}
        </button>
      </form>
    </div>
    
    <div v-else>
      <p>歡迎回來，{{ userName }}！</p>
      <button @click="handleLogout">登出</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuth } from '../composables/useAuth.js';

const {
  user,
  isLoading,
  error,
  isAuthenticated,
  userName,
  login,
  logout
} = useAuth();

const loginForm = ref({
  email: 'test@example.com',
  password: 'password123'
});

const handleLogin = async () => {
  const result = await login(loginForm.value.email, loginForm.value.password);
  if (result.success) {
    console.log('登入成功:', result.data);
  }
};

const handleLogout = async () => {
  const result = await logout();
  if (result.success) {
    console.log('登出成功:', result.data);
  }
};
</script>
```

## API 使用方式

### 1. 直接使用 API 服務

```javascript
import userAPI from '../api/user.js';

// 登入
const result = await userAPI.login('test@example.com', 'password123');

// 登出
const result = await userAPI.logout();

// 取得使用者資訊
const result = await userAPI.getCurrentUser();
```

### 2. 使用 Composable

```javascript
import { useAuth } from '../composables/useAuth.js';

const { login, logout, getCurrentUser, isAuthenticated } = useAuth();

// 登入
const result = await login('test@example.com', 'password123');

// 檢查登入狀態
if (isAuthenticated.value) {
  console.log('使用者已登入');
}
```

## 狀態管理

### 全域狀態

使用 Composable 提供全域狀態管理：

```javascript
// composables/useAuth.js
const user = ref(null);
const isLoading = ref(false);
const error = ref(null);

export function useAuth() {
  // 所有組件共享同一個狀態
  return {
    user: readonly(user),
    isLoading: readonly(isLoading),
    error: readonly(error)
  };
}
```

### 響應式資料

```javascript
// 在組件中使用
const { user, isLoading } = useAuth();

// 自動響應狀態變化
watch(user, (newUser) => {
  if (newUser) {
    console.log('使用者已登入:', newUser);
  }
});
```

## 錯誤處理

### 1. API 層級錯誤處理

```javascript
// api/base.js
handleError(error) {
  if (error.response?.status === 401) {
    this.redirectToLogin();
  }
  // 其他錯誤處理...
}
```

### 2. Composable 層級錯誤處理

```javascript
// composables/useAuth.js
const login = async (email, password) => {
  try {
    const response = await userAPI.login(email, password);
    if (response.status) {
      user.value = response.data.user;
      return { success: true, data: response.data };
    } else {
      error.value = response.message;
      return { success: false, message: response.message };
    }
  } catch (err) {
    error.value = err.message || '登入失敗';
    return { success: false, message: error.value };
  }
};
```

### 3. 組件層級錯誤處理

```vue
<template>
  <div v-if="error" class="error-message">
    {{ error }}
  </div>
</template>

<script setup>
const { error, clearError } = useAuth();

// 清除錯誤
const handleSubmit = async () => {
  clearError();
  // 執行操作...
};
</script>
```

## 與 Laravel 後端整合

### 1. CSRF Token

在 Blade 模板中設定：

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 2. API 路由對應

```javascript
// 登入: POST /api/user/login
// 登出: POST /api/user/logout
// 取得使用者資訊: GET /api/user/me
```

## 開發工具

### 1. Vue DevTools

安裝 Vue DevTools 瀏覽器擴充功能來除錯 Vue 應用程式。

### 2. 熱重載

使用 `npm run dev` 啟動開發伺服器，支援熱重載。

### 3. 型別檢查

可以添加 TypeScript 支援：

```bash
npm install -D typescript @vue/tsconfig
```

## 最佳實踐

1. **使用 Composable 管理狀態**：將相關的狀態和邏輯封裝在 Composable 中
2. **響應式設計**：使用 Tailwind CSS 建立響應式介面
3. **錯誤處理**：在每個層級都提供適當的錯誤處理
4. **載入狀態**：提供良好的使用者體驗
5. **型別安全**：考慮使用 TypeScript 提供更好的開發體驗

## 優勢

1. **現代化**: 使用 Vue 3 和 Composition API
2. **響應式**: 自動響應狀態變化
3. **模組化**: 清晰的檔案結構和邏輯分離
4. **可復用**: Composable 提供邏輯復用
5. **型別安全**: 支援 TypeScript
6. **開發體驗**: 熱重載和 Vue DevTools
7. **效能**: Vue 3 的優化渲染
