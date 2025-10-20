<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">土單使用統計表</h2>
            <p class="text-gray-600">篩選工程並查看統計圖表</p>
          </div>
        </div>

        <!-- 篩選列（狀態 + 可搜尋工程） -->
        <div class="flex flex-col md:flex-row items-stretch md:items-end gap-3 mb-6">
          <div>
            <label class="block text-xs text-gray-500 mb-1">狀態</label>
            <select v-model="status" @change="debouncedLoadDatalist" class="px-3 py-2 border rounded-md">
              <option value="all">全部</option>
              <option value="active">啟用中</option>
              <option value="inactive">已結案</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-xs text-gray-500 mb-1">選擇工程（可搜尋）</label>
            <input
              v-model="earthInput"
              @input="debouncedLoadDatalist"
              @keyup.enter="loadSelectedEarth"
              list="earth-data-stats-list"
              placeholder="輸入關鍵字（批號/工程/客戶）後從下拉選擇"
              class="w-full px-3 py-2 border rounded-md"
            />
            <datalist id="earth-data-stats-list">
              <option v-for="opt in earthOptions" :key="opt.id" :value="`${opt.id}｜${opt.text}`"></option>
            </datalist>
          </div>
          <div class="flex gap-2">
            <button @click="loadSelectedEarth" class="px-4 py-2 bg-amber-600 text-white rounded-md">載入</button>
          </div>
        </div>

        <!-- 統計圖表區 -->
        <div v-if="!selected" class="text-gray-500">請先選擇工程</div>
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- 圓餅圖：總數/已核銷/待使用 -->
          <div class="bg-white border rounded-xl p-4">
            <h3 class="font-medium mb-3">總覽</h3>
            <div class="flex items-center gap-6">
              <div
                class="w-40 h-40 rounded-full"
                :style="pieStyle"
                aria-label="usage pie"
              ></div>
              <div class="text-sm">
                <div class="flex items-center gap-2 mb-1">
                  <span class="inline-block w-3 h-3 rounded-sm" style="background:#60a5fa"></span>
                  <span>已核銷：</span>
                  <strong>{{ totals.verified }}</strong>
                </div>
                <div class="flex items-center gap-2 mb-1">
                  <span class="inline-block w-3 h-3 rounded-sm" style="background:#fbbf24"></span>
                  <span>待使用：</span>
                  <strong>{{ totals.pending }}</strong>
                </div>
                <div class="flex items-center gap-2">
                  <span class="inline-block w-3 h-3 rounded-sm" style="background:#e5e7eb"></span>
                  <span>總數：</span>
                  <strong>{{ totals.total }}</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- 長條圖：每日核銷數 -->
          <div class="bg-white border rounded-xl p-4">
            <h3 class="font-medium mb-3">每日使用統計</h3>
            <div v-if="daily.length === 0" class="text-gray-500">無資料</div>
            <div v-else class="w-full">
              <div class="w-full h-[220px] border-b border-gray-200 flex flex-wrap items-end">
                <div
                  v-for="(d, idx) in daily"
                  :key="d.day"
                  class="flex flex-col items-center justify-end"
                  :style="{ width: barCellWidth + 'px', height: chartHeight + 'px' }"
                >
                  <div
                    class="w-6 bg-blue-400 rounded-t"
                    :style="{ height: barHeight(d.count) + 'px' }"
                    :title="`${d.day}: ${d.count}`"
                  ></div>
                  <div class="mt-2 text-[10px] text-gray-500">{{ shortDay(d.day) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </template>

<script setup>
import { ref, computed } from 'vue'
import earthDataAPI from '../api/earthData.js'
import commonAPI from '../api/common.js'

const status = ref('all')
const earthInput = ref('')
const earthOptions = ref([])
const selected = ref(null)
const totals = ref({ total: 0, verified: 0, pending: 0 })
const daily = ref([])

let datalistTimer = null
const debouncedLoadDatalist = () => {
  clearTimeout(datalistTimer)
  datalistTimer = setTimeout(loadEarthDatalist, 400)
}

const loadEarthDatalist = async () => {
  try {
    const resp = await commonAPI.earthDataDatalist({ status: status.value, q: earthInput.value })
    if (resp.status) {
      earthOptions.value = Array.isArray(resp.data) ? resp.data : []
    }
  } catch (e) {}
}

const loadSelectedEarth = async () => {
  const raw = (earthInput.value || '').trim()
  if (!raw) return
  const idStr = raw.split('｜')[0].trim()
  const id = parseInt(idStr, 10)
  if (Number.isNaN(id)) return
  const info = await earthDataAPI.get(id)
  if (info.status) {
    selected.value = info.data
    const s = await earthDataAPI.usageStats(id)
    if (s.status) {
      totals.value = s.data?.totals || { total: 0, verified: 0, pending: 0 }
      daily.value = Array.isArray(s.data?.daily) ? s.data.daily : []
    }
  }
}

// Pie 圖樣式（使用 conic-gradient）
const pieStyle = computed(() => {
  const t = Math.max(0, Number(totals.value.total) || 0)
  const v = Math.max(0, Math.min(t, Number(totals.value.verified) || 0))
  const p = Math.max(0, Math.min(t, Number(totals.value.pending) || 0))
  const vPct = t ? (v / t) * 100 : 0
  const pPct = t ? (p / t) * 100 : 0
  const g = `conic-gradient(#60a5fa 0 ${vPct}%, #fbbf24 ${vPct}% ${vPct + pPct}%, #e5e7eb ${vPct + pPct}% 100%)`
  return { background: g }
})

// Bar 圖設定
const chartHeight = 220
const barStep = 48
const barCellWidth = 48
const barMax = computed(() => Math.max(1, ...daily.value.map(d => d.count || 0)))
const barHeight = (val) => {
  const maxH = chartHeight - 60
  if (!barMax.value) return 0
  return Math.round((Math.max(0, val) / barMax.value) * maxH)
}
const shortDay = (day) => {
  // day is YYYY-MM-DD
  return (day || '').slice(5)
}

// 初始載入 datalist
loadEarthDatalist()
</script>
