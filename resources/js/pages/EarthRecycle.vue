<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 土單回收作業內容 -->
    <div class="max-w-7xl mx-auto">
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
        <!-- 頁面標題 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">土單回收作業</h2>
            <p class="text-gray-600">處理土單回收流程</p>
          </div>
        </div>

        <!-- 功能開發中提示 -->
        <div class="text-center py-12">
          <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">土單回收作業系統</h3>
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
// EarthRecycle 組件邏輯
</script>
