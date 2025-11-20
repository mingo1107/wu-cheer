<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
      <!-- 頁面標題 -->
      <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-800">儀表板</h1>
        <p class="text-gray-600">歡迎回來，{{ user?.name || '使用者' }}</p>
      </div>

      <!-- 載入狀態 -->
      <div v-if="isLoading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-600"></div>
      </div>

      <!-- 主要內容 -->
      <div v-else>
        <!-- 統計卡片區 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- 土單總數 -->
          <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-xl shadow-lg">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold">{{ stats.earth_data?.total || 0 }}</div>
                <div class="text-sm opacity-90">土單總數</div>
              </div>
            </div>
            <h3 class="text-lg font-semibold mb-1">土單資料</h3>
            <p class="text-sm opacity-90">進行中：{{ stats.earth_data?.active || 0 }}</p>
          </div>

          <!-- 已核銷數量 -->
          <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-xl shadow-lg">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold">{{ stats.earth_data_detail?.used || 0 }}</div>
                <div class="text-sm opacity-90">已核銷</div>
              </div>
            </div>
            <h3 class="text-lg font-semibold mb-1">核銷統計</h3>
            <p class="text-sm opacity-90">今日：{{ stats.earth_data_detail?.today_used || 0 }}</p>
          </div>

          <!-- 待處理數量 -->
          <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white p-6 rounded-xl shadow-lg">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold">{{ stats.earth_data_detail?.unprinted || 0 }}</div>
                <div class="text-sm opacity-90">待列印</div>
              </div>
            </div>
            <h3 class="text-lg font-semibold mb-1">待處理</h3>
            <p class="text-sm opacity-90">已列印：{{ stats.earth_data_detail?.printed || 0 }}</p>
          </div>

          <!-- 基礎資料統計 -->
          <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-xl shadow-lg">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold">{{ stats.basic?.customers || 0 }}</div>
                <div class="text-sm opacity-90">客戶數</div>
              </div>
            </div>
            <h3 class="text-lg font-semibold mb-1">基礎資料</h3>
            <p class="text-sm opacity-90">清運業者：{{ stats.basic?.cleaners || 0 }} | 核銷人員：{{ stats.basic?.verifiers || 0 }}</p>
          </div>
        </div>

        <!-- 詳細統計卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <!-- 土單明細狀態分布 -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
              </svg>
              土單明細狀態
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">總數</span>
                <span class="font-semibold text-gray-800">{{ stats.earth_data_detail?.total || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">未列印</span>
                <span class="font-semibold text-yellow-600">{{ stats.earth_data_detail?.unprinted || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">已列印</span>
                <span class="font-semibold text-blue-600">{{ stats.earth_data_detail?.printed || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">已核銷</span>
                <span class="font-semibold text-green-600">{{ stats.earth_data_detail?.used || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">作廢</span>
                <span class="font-semibold text-red-600">{{ stats.earth_data_detail?.voided || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">回收</span>
                <span class="font-semibold text-gray-600">{{ stats.earth_data_detail?.recycled || 0 }}</span>
              </div>
            </div>
          </div>

          <!-- 本月核銷趨勢 -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              本月核銷趨勢
            </h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
              <div
                v-for="(item, index) in stats.monthly_trend"
                :key="index"
                class="flex justify-between items-center py-1"
              >
                <span class="text-sm text-gray-600">{{ formatDate(item.date) }}</span>
                <div class="flex items-center">
                  <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                    <div
                      class="bg-green-500 h-2 rounded-full"
                      :style="{ width: `${getPercentage(item.count)}%` }"
                    ></div>
                  </div>
                  <span class="text-sm font-semibold text-gray-800 w-8 text-right">{{ item.count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 最近活動 -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
            </svg>
            最近活動
          </h3>
          <div v-if="stats.recent_activities && stats.recent_activities.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="(activity, index) in stats.recent_activities"
              :key="index"
              class="flex items-start p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200"
            >
              <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3"
                   :class="getActivityIconClass(activity.type)">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path v-if="activity.type === 'earth_data'" fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                  <path v-else-if="activity.type === 'verify'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  <path v-else fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                <p class="text-sm text-gray-500 truncate">{{ activity.subtitle }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ formatDateTimeShort(activity.created_at) }}</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            暫無最近活動
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../composables/useAuth.js'
import { useToast } from '../composables/useToast.js'
import { formatDateTimeShort } from '../utils/date.js'
import dashboardAPI from '../api/dashboard.js'

const { user } = useAuth()
const { error: showErrorToast } = useToast()

// 響應式資料
const isLoading = ref(true)
const stats = ref({
  earth_data: {
    total: 0,
    active: 0,
    inactive: 0
  },
  earth_data_detail: {
    total: 0,
    unprinted: 0,
    printed: 0,
    used: 0,
    voided: 0,
    recycled: 0,
    today_used: 0
  },
  basic: {
    customers: 0,
    cleaners: 0,
    verifiers: 0
  },
  recent_activities: [],
  monthly_trend: []
})

// 載入統計資料
const loadStats = async () => {
  isLoading.value = true
  try {
    const response = await dashboardAPI.getStats()
    if (response.status && response.data) {
      stats.value = response.data
    } else {
      showErrorToast('載入統計資料失敗')
    }
  } catch (error) {
    console.error('載入統計資料失敗:', error)
    showErrorToast('載入統計資料失敗')
  } finally {
    isLoading.value = false
  }
}

// 格式化日期（僅顯示月/日）
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return ''
  const month = date.getMonth() + 1
  const day = date.getDate()
  return `${month}/${day}`
}

// 取得活動圖示樣式
const getActivityIconClass = (type) => {
  const classes = {
    earth_data: 'bg-blue-500',
    verify: 'bg-green-500',
    announcement: 'bg-amber-500'
  }
  return classes[type] || 'bg-gray-500'
}

// 計算百分比（用於趨勢圖）
const getPercentage = (count) => {
  if (!stats.value.monthly_trend || stats.value.monthly_trend.length === 0) return 0
  const maxCount = Math.max(...stats.value.monthly_trend.map(item => item.count))
  if (maxCount === 0) return 0
  return Math.round((count / maxCount) * 100)
}

// 初始化
onMounted(() => {
  loadStats()
})
</script>
