<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 公告欄內容 -->
    <div class="max-w-7xl mx-auto">
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
        <!-- 頁面標題 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">公告欄</h2>
            <p class="text-gray-600">系統公告與重要訊息</p>
          </div>
        </div>

        <!-- 功能開發中提示 -->
        <div class="text-center py-12">
          <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">公告欄管理</h3>
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
// Announcement 組件邏輯
</script>
