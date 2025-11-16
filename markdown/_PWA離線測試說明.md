# PWA 離線測試說明

## 問題描述

在離線狀態下訪問 `http://wu-cheer.local.com/verifier/dashboard` 時，頁面顯示「沒網路 無法顯示」。

## 解決方案

### 1. 確保 Service Worker 已註冊

Service Worker 需要在**在線狀態下**先訪問一次頁面，才能被註冊並快取資源。

**步驟：**
1. 確保網路連線正常
2. 訪問 `http://wu-cheer.local.com/verifier/dashboard`
3. 打開瀏覽器開發者工具（F12）
4. 切換到「Application」或「應用程式」標籤
5. 在左側選單中找到「Service Workers」
6. 確認 Service Worker 已註冊並處於「activated」狀態

### 2. 重新構建前端資源

如果 Service Worker 沒有被註冊，可能需要重新構建前端資源：

```bash
npm run build
```

或開發模式：

```bash
npm run dev
```

### 3. 清除快取並重新註冊

如果 Service Worker 已存在但無法正常工作：

1. 打開瀏覽器開發者工具（F12）
2. 切換到「Application」或「應用程式」標籤
3. 在左側選單中找到「Service Workers」
4. 點擊「Unregister」取消註冊
5. 在「Storage」中清除所有快取
6. 重新整理頁面（在線狀態）
7. 確認 Service Worker 重新註冊

### 4. 測試離線功能

**步驟：**
1. 確保在線狀態下已訪問過 `http://wu-cheer.local.com/verifier/dashboard`
2. 確認 Service Worker 已註冊並快取了頁面
3. 在瀏覽器開發者工具中：
   - 切換到「Network」或「網路」標籤
   - 勾選「Offline」或「離線」選項
4. 重新整理頁面
5. 頁面應該可以正常載入（從快取中）

### 5. 檢查 PWA 配置

確認 `vite.config.js` 中的 PWA 配置正確：

```javascript
VitePWA({
    registerType: 'autoUpdate',
    // ... 其他配置
    workbox: {
        navigateFallback: '/',
        // ... 其他配置
    }
})
```

### 6. 檢查路由配置

確認 `routes/web.php` 中的 SPA 路由配置正確：

```php
Route::get('/{any}', function () {
    return view('cms-app');
})->where('any', '.*');
```

## 常見問題

### Q: 為什麼離線時無法載入頁面？

**A:** 可能的原因：
1. Service Worker 尚未註冊（需要在線狀態下先訪問一次）
2. 頁面資源尚未被快取
3. Service Worker 配置不正確

### Q: 如何確認 Service Worker 已註冊？

**A:** 
1. 打開瀏覽器開發者工具（F12）
2. 切換到「Application」或「應用程式」標籤
3. 在左側選單中找到「Service Workers」
4. 確認狀態為「activated」或「running」

### Q: 如何強制更新 Service Worker？

**A:**
1. 在開發者工具的「Service Workers」中點擊「Update」
2. 或清除瀏覽器快取後重新訪問頁面

### Q: 離線時可以進行核銷作業嗎？

**A:** 可以！離線時：
- 頁面可以正常載入（從快取中）
- 可以輸入 barcode 進行核銷
- 核銷記錄會暫存在 `localStorage` 中
- 當網路恢復時，會自動同步到後端

## 測試檢查清單

- [ ] 在線狀態下訪問過 Dashboard
- [ ] Service Worker 已註冊並處於「activated」狀態
- [ ] 在開發者工具中確認頁面資源已被快取
- [ ] 切換到離線模式後，頁面可以正常載入
- [ ] 離線時可以進行核銷作業
- [ ] 離線記錄正確儲存在 `localStorage` 中
- [ ] 網路恢復後，離線記錄可以自動同步

## 技術細節

### Service Worker 快取策略

- **HTML 頁面**：使用 `NetworkFirst` 策略，離線時從快取提供
- **導航請求**：使用 `NetworkFirst` 策略，離線時從快取提供
- **API 請求**：使用 `NetworkFirst` 策略，離線時不提供（由前端處理）
- **靜態資源**：使用 `CacheFirst` 策略，優先從快取提供

### 離線資料儲存

- 使用 `localStorage` 儲存離線核銷記錄
- 記錄狀態：`pending`（待同步）、`synced`（已同步）、`failed`（失敗）
- 網路恢復時自動同步待同步的記錄

## 相關檔案

- `vite.config.js` - PWA 配置
- `resources/views/cms-app.blade.php` - SPA 入口頁面
- `resources/js/pages/verifierPlatform/Dashboard.vue` - 核銷作業頁面
- `resources/js/utils/offlineStorageSimple.js` - 離線儲存工具

