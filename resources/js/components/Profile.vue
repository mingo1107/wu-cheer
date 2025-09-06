<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 個人資料內容 -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
          <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">個人資料</h2>
            <p class="text-gray-600">管理您的帳戶資訊</p>
          </div>

          <!-- 使用者資訊 -->
          <div v-if="user" class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 mb-8">
            <div class="flex items-center mb-4">
              <svg class="w-6 h-6 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              <h3 class="text-lg font-semibold text-green-800">使用者資訊</h3>
            </div>
            <div class="space-y-3">
              <div class="flex items-center">
                <span class="text-sm font-medium text-green-700 w-20">姓名:</span>
                <span class="text-sm text-green-600">{{ user.name }}</span>
              </div>
              <div class="flex items-center">
                <span class="text-sm font-medium text-green-700 w-20">Email:</span>
                <span class="text-sm text-green-600">{{ user.email }}</span>
              </div>
              <div class="flex items-center">
                <span class="text-sm font-medium text-green-700 w-20">註冊時間:</span>
                <span class="text-sm text-green-600">{{ formatDate(user.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- 導航按鈕 -->
          <div class="flex flex-wrap gap-4 justify-center">
            <router-link 
              to="/dashboard" 
              class="bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
              </svg>
              返回儀表板
            </router-link>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { useAuth } from '../composables/useAuth.js';

const { user } = useAuth();

const formatDate = (dateString) => {
  if (!dateString) return '未知';
  return new Date(dateString).toLocaleDateString('zh-TW');
};
</script>
