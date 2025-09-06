#!/bin/bash

# 強制更新腳本
# 用於確保 Git 暫存區和工作目錄同步

echo "🔄 開始強制更新..."

# 1. 檢查 Git 狀態
echo "📋 檢查 Git 狀態..."
git status --porcelain

# 2. 添加所有變更到暫存區
echo "📦 添加所有變更到暫存區..."
git add .

# 3. 檢查暫存區狀態
echo "✅ 暫存區狀態："
git status --porcelain

# 4. 顯示變更摘要
echo "📊 變更摘要："
git diff --cached --stat

echo "🎉 強制更新完成！"
echo ""
echo "現在可以執行："
echo "  npm run watch    # 正常監聽模式"
echo "  npm run dev      # 正常開發模式"
echo "  npm run build    # 正常建置模式"
