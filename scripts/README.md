# 強制更新腳本說明

## 問題描述

在開發過程中，有時會遇到 Git 暫存區和工作目錄不同步的問題，導致：
- 檔案已修改但暫存區未更新
- 編輯器顯示的內容與實際檔案內容不一致
- 建置時使用舊版本的檔案

## 解決方案

### 1. 快速命令

```bash
# 強制更新所有變更到暫存區
npm run force-update

# 強制更新後執行監聽模式
npm run watch:clean

# 強制更新後執行開發模式
npm run dev:clean
```

### 2. 手動命令

```bash
# 添加所有變更到暫存區
git add .

# 檢查狀態
git status

# 查看變更摘要
git diff --cached --stat
```

### 3. 進階命令

```bash
# 簡單強制更新
npm run watch:force    # git add . && vite build --watch
npm run dev:force      # git add . && vite
npm run build:force    # git add . && vite build

# 完整強制更新（包含狀態檢查）
npm run watch:clean    # 腳本 + vite build --watch
npm run dev:clean      # 腳本 + vite
```

## 使用時機

- 當編輯器顯示的內容與實際檔案不一致時
- 當建置結果與預期不符時
- 當 Git 狀態顯示有未暫存的變更時
- 當需要確保所有變更都已同步時

## 注意事項

- 這些命令會將所有變更添加到暫存區，請確保變更是您想要的
- 建議在提交前檢查 `git status` 和 `git diff --cached`
- 如果只想更新特定檔案，請使用 `git add <檔案名>`
