# Laravel Vite 前端 API 工具

## 概述

這是一套基於 Laravel Vite 的前端 API 呼叫工具，提供現代化的 ES6 模組化架構。

## 檔案結構

```
resources/js/
├── api/
│   ├── base.js      # 基礎 API 工具類別
│   └── user.js      # 使用者相關 API 服務
├── app.js           # 主要入口檔案
└── README.md        # 使用說明
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

### 2. 在 Blade 模板中使用

```html
<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <script>
        // 使用新的 API 方法
        userAPI.login('test@example.com', 'password123', function(data) {
            console.log('登入成功:', data);
        });

        // 使用舊版方法（向後相容）
        doAjaxRequest('post', '/user/login', {
            email: 'test@example.com',
            password: 'password123'
        }, function(data) {
            console.log('登入成功:', data);
        });
    </script>
</body>
</html>
```

## API 使用方式

### 1. 基礎 API 工具 (base.js)

```javascript
import api from './api/base.js';

// GET 請求
api.get('/user/me', null, function(data) {
    console.log('使用者資訊:', data);
});

// POST 請求
api.post('/user/login', {
    email: 'test@example.com',
    password: 'password123'
}, function(data) {
    console.log('登入成功:', data);
});

// PUT 請求
api.put('/user/profile', {
    name: '新名稱'
}, function(data) {
    console.log('更新成功:', data);
});

// DELETE 請求
api.delete('/user/account', null, function(data) {
    console.log('刪除成功:', data);
});
```

### 2. 使用者 API 服務 (user.js)

```javascript
import userAPI from './api/user.js';

// 登入
userAPI.login('test@example.com', 'password123', function(data) {
    console.log('登入成功:', data);
});

// 登出
userAPI.logout(function(data) {
    console.log('登出成功:', data);
});

// 取得使用者資訊
userAPI.getCurrentUser(function(data) {
    console.log('使用者資訊:', data);
});

// 更新使用者資料
userAPI.updateProfile({
    name: '新名稱',
    email: 'new@example.com'
}, function(data) {
    console.log('更新成功:', data);
});

// 變更密碼
userAPI.changePassword('oldPassword', 'newPassword', function(data) {
    console.log('密碼變更成功:', data);
});
```

### 3. 使用 Promise/async-await

```javascript
// 使用 async/await
async function loginUser() {
    try {
        const result = await userAPI.login('test@example.com', 'password123');
        console.log('登入成功:', result);
    } catch (error) {
        console.error('登入失敗:', error);
    }
}

// 使用 Promise
userAPI.login('test@example.com', 'password123')
    .then(data => {
        console.log('登入成功:', data);
    })
    .catch(error => {
        console.error('登入失敗:', error);
    });
```

## 錯誤處理

工具會自動處理常見的 HTTP 錯誤：

- **401**: 未授權，自動重導向到登入頁面
- **403**: 權限不足
- **404**: 資源不存在
- **422**: 驗證錯誤，顯示詳細錯誤訊息
- **500**: 伺服器錯誤

## 自訂設定

### 修改 Base URL

```javascript
// 在 base.js 中修改
this.baseURL = '/api/v2';
```

### 自訂錯誤處理

```javascript
// 覆寫錯誤處理方法
api.handleError = function(error) {
    // 自訂錯誤處理邏輯
    console.log('自訂錯誤處理:', error);
};
```

## 測試

訪問測試頁面：`http://localhost:8000/test-api`

## 與 Laravel 後端整合

### 1. 設定 CSRF Token

在 Blade 模板中加入：

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 2. API 路由對應

```javascript
// 登入
userAPI.login(email, password, callback);

// 登出
userAPI.logout(callback);

// 取得使用者資訊
userAPI.getCurrentUser(callback);
```

## 優勢

1. **現代化**: 使用 ES6 模組和 Vite
2. **模組化**: 清晰的檔案結構
3. **型別安全**: 支援 TypeScript（可選）
4. **熱重載**: 開發時自動重新載入
5. **最佳化**: 生產環境自動壓縮和最佳化
6. **向後相容**: 保留舊版 API 方法

## 注意事項

1. 確保在 Blade 模板中正確引入 Vite 資源
2. 設定正確的 CSRF Token
3. 後端 API 需要支援 CORS（如果前後端分離）
4. 使用 `npm run dev` 啟動開發環境
5. 使用 `npm run build` 建置生產版本
