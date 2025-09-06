<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 客戶資料內容 -->
    <div class="max-w-7xl mx-auto">
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
        <!-- 頁面標題 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">客戶資料</h2>
            <p class="text-gray-600">管理系統中的所有客戶資料</p>
          </div>
        </div>

        <!-- 功能開發中提示 -->
        <div class="text-center py-12">
          <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">客戶資料管理</h3>
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
// Customer 組件邏輯
</script>
