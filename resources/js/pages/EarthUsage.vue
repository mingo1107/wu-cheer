<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold mb-4">土單使用明細</h2>

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
              list="earth-data-list"
              placeholder="輸入關鍵字（批號/工程/客戶）後從下拉選擇"
              class="w-full px-3 py-2 border rounded-md"
            />
            <datalist id="earth-data-list">
              <option v-for="opt in earthOptions" :key="opt.id" :value="`${opt.id}｜${opt.text}`"></option>
            </datalist>
          </div>
          <div class="flex gap-2">
            <button @click="loadSelectedEarth" class="px-4 py-2 bg-amber-600 text-white rounded-md">載入</button>
            <button v-if="selected" @click="exportDetails" class="px-4 py-2 bg-emerald-600 text-white rounded-md">匯出</button>
          </div>
        </div>

        <div>
          <h3 class="font-medium mb-2">明細列表</h3>
          <div v-if="!selected" class="text-gray-500">請先選擇工程</div>
          <div v-else class="border rounded-md max-h-[60vh] overflow-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 py-2 text-left">Barcode</th>
                  <th class="px-3 py-2 text-left">列印時間</th>
                  <th class="px-3 py-2 text-left">核銷時間</th>
                  <th class="px-3 py-2 text-left">核銷人員</th>
                  <th class="px-3 py-2 text-left">建立時間</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="d in details" :key="d.id">
                  <td class="px-3 py-2 font-mono">{{ d.barcode }}</td>
                  <td class="px-3 py-2">{{ formatDateTime(d.print_at) }}</td>
                  <td class="px-3 py-2">{{ formatDateTime(d.verified_at) }}</td>
                  <td class="px-3 py-2">{{ d.verified_by_name || '-' }}</td>
                  <td class="px-3 py-2">{{ formatDateTime(d.created_at) }}</td>
                </tr>
                <tr v-if="details.length === 0">
                  <td class="px-3 py-6 text-center text-gray-500" colspan="5">無明細</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import earthDataAPI from '../api/earthData.js'
import commonAPI from '../api/common.js'

const status = ref('all')
const earthInput = ref('')
const earthOptions = ref([])
const selected = ref(null)
const details = ref([])

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
  // expected input pattern: "<id>｜<text>" or just "<id>"
  const raw = (earthInput.value || '').trim()
  if (!raw) return
  const idStr = raw.split('｜')[0].trim()
  const id = parseInt(idStr, 10)
  if (Number.isNaN(id)) return
  const resp = await earthDataAPI.get(id)
  if (resp.status) {
    selected.value = resp.data
    const d = await earthDataAPI.details(id)
    if (d.status) {
      details.value = Array.isArray(d.data?.details) ? d.data.details : []
    }
  }
}

const exportDetails = () => {
  if (!selected.value?.id) return
  const token = localStorage.getItem('token') || ''
  const base = `/api/earth-data/${selected.value.id}/details/export`
  const url = token ? `${base}?token=${encodeURIComponent(token)}` : base
  window.open(url, '_blank')
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString('zh-TW')
}

// initial datalist
loadEarthDatalist()
</script>
