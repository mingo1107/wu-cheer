<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 土單使用統計表內容 -->
    <div class="max-w-7xl mx-auto">
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
        <!-- 頁面標題 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">土單使用統計表</h2>
            <p class="text-gray-600">查看使用統計報表</p>
          </div>
        </div>

        <!-- 功能開發中提示 -->
        <div class="text-center py-12">
          <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">土單使用統計系統</h3>
          <p class="text-gray-600 mb-6">此功能正在開發中，敬請期待</p>
          <router-link
            to="/dashboard"
            class="bg-gradient-to-r from-amber-500 to-orange-600 text-white py-2 px-6 rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all duration-200"
          >
            返回儀表板
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// EarthStatistics 組件邏輯
</script>
