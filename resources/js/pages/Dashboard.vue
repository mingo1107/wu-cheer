<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 儀表板內容 -->
    <div class="max-w-6xl mx-auto">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">歡迎來到儀表板</h2>
        <p class="text-gray-600">這裡是土方石清運管理系統的主要控制台</p>
      </div>

      <!-- 主要功能區塊 -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- 案件管理 -->
        <router-link
          to="/case"
          class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold">0</div>
              <div class="text-sm opacity-90">進行中</div>
            </div>
          </div>
          <h3 class="text-lg font-semibold mb-2">案件管理</h3>
          <p class="text-sm opacity-90">管理土方石清運案件</p>
        </router-link>

        <!-- 土單資料管理 -->
        <router-link
          to="/earth-data"
          class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold">0</div>
              <div class="text-sm opacity-90">總數</div>
            </div>
          </div>
          <h3 class="text-lg font-semibold mb-2">土單資料管理</h3>
          <p class="text-sm opacity-90">維護土單基本資料</p>
        </router-link>

        <!-- 土單回收作業 -->
        <router-link
          to="/earth-recycle"
          class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold">0</div>
              <div class="text-sm opacity-90">待回收</div>
            </div>
          </div>
          <h3 class="text-lg font-semibold mb-2">土單回收作業</h3>
          <p class="text-sm opacity-90">處理土單回收流程</p>
        </router-link>

        <!-- 土單使用統計表 -->
        <router-link
          to="/earth-statistics"
          class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
              </svg>
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold">0</div>
              <div class="text-sm opacity-90">本月</div>
            </div>
          </div>
          <h3 class="text-lg font-semibold mb-2">土單使用統計表</h3>
          <p class="text-sm opacity-90">查看使用統計報表</p>
        </router-link>
      </div>

        <!-- 功能卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-blue-800">運輸管理</h3>
            </div>
            <p class="text-blue-600 text-sm">管理土方石運輸任務和車輛調度</p>
          </div>

          <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-green-500 rounded-lg flex items中心 justify-center mr-4">
                <svg class="w-6 h-6 text白" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-green-800">清運場管理</h3>
            </div>
            <p class="text-green-600 text-sm">監控清運場的接收和處理狀況</p>
          </div>

          <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-purple-800">報表統計</h3>
            </div>
            <p class="text-purple-600 text-sm">查看運輸和清運的統計報表</p>
          </div>
        </div>

        </div>
      </div>
    </div>
</template>

<script setup>
// Dashboard 組件不需要額外的邏輯
</script>
