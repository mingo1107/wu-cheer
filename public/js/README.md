# Base.js API 呼叫工具

## 概述

`base.js` 是一個基於 axios 的統一 API 請求處理工具，提供簡潔易用的方法來與後端 API 進行通訊。

## 功能特色

- ✅ 統一的錯誤處理
- ✅ 自動載入狀態管理
- ✅ CSRF Token 自動處理
- ✅ 請求/回應攔截器
- ✅ 支援所有 HTTP 方法
- ✅ 檔案下載功能
- ✅ 向後相容性

## 快速開始

### 1. 引入檔案

```html
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="/js/base.js"></script>
```

### 2. 基本使用

```javascript
// 使用新的 API 方法
api.post('/user/login', {
    email: 'test@example.com',
    password: 'password123'
}, function(data) {
    console.log('登入成功:', data);
});

// 使用舊版方法（向後相容）
doAjaxRequest('post', '/user/login', {
    email: 'test@example.com',
    password: 'password123'
}, function(data) {
    console.log('登入成功:', data);
});
```

## API 方法

### 主要方法

#### `api.doAjaxRequest(method, url, data, callback, options)`

執行 AJAX 請求的主要方法。

**參數：**
- `method` (string): HTTP 方法 (get, post, put, delete)
- `url` (string): API 端點
- `data` (object): 請求資料
- `callback` (function): 成功回調函數
- `options` (object): 額外選項

**範例：**
```javascript
api.doAjaxRequest('post', '/user/login', {
    email: 'test@example.com',
    password: 'password123'
}, function(data) {
    console.log('回應:', data);
});
```

#### 簡化方法

```javascript
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

### 檔案下載

```javascript
// 下載檔案
api.download('/export/users', {
    format: 'excel'
}, 'users.xlsx');
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
// 在建立實例後修改
api.baseURL = '/api/v2';
```

### 自訂錯誤處理

```javascript
// 覆寫錯誤處理方法
api.handleError = function(error) {
    // 自訂錯誤處理邏輯
    console.log('自訂錯誤處理:', error);
};
```

## 與 Laravel 後端整合

### 1. 設定 CSRF Token

在 HTML head 中加入：

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 2. API 路由對應

```javascript
// 登入
api.post('/user/login', { email, password }, callback);

// 登出
api.post('/user/logout', null, callback);

// 取得使用者資訊
api.get('/user/me', null, callback);
```

## 完整範例

```html
<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/js/base.js"></script>
</head>
<body>
    <button onclick="login()">登入</button>
    <button onclick="getUserInfo()">取得使用者資訊</button>
    <button onclick="logout()">登出</button>
    
    <script>
        function login() {
            api.post('/user/login', {
                email: 'test@example.com',
                password: 'password123'
            }, function(data) {
                console.log('登入成功:', data);
            });
        }

        function getUserInfo() {
            api.get('/user/me', null, function(data) {
                console.log('使用者資訊:', data);
            });
        }

        function logout() {
            api.post('/user/logout', null, function(data) {
                console.log('登出成功:', data);
            });
        }
    </script>
</body>
</html>
```

## 注意事項

1. 確保在引入 `base.js` 之前先引入 `axios`
2. 設定正確的 CSRF Token
3. 後端 API 需要支援 CORS（如果前後端分離）
4. 錯誤處理會自動執行，但可以自訂處理邏輯
