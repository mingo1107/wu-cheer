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
            <div class="flex gap-2">
            <input
              v-model="earthInput"
              @input="debouncedLoadDatalist"
                @change="handleEarthInputChange"
              @keyup.enter="loadSelectedEarth"
              list="earth-data-list"
              placeholder="輸入關鍵字（批號/工程/客戶）後從下拉選擇"
                class="flex-1 px-3 py-2 border rounded-md"
            />
            <datalist id="earth-data-list">
              <option v-for="opt in earthOptions" :key="opt.id" :value="`${opt.id}｜${opt.text}`"></option>
            </datalist>
              <button
                v-if="selected"
                @click="clearSelection"
                class="px-3 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
                title="清除選擇"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
          <div class="flex gap-2">
            <button v-if="selected" @click="refreshDetails" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
              <i class="fas fa-sync-alt mr-2"></i>
              重整
            </button>
            <button v-if="selected" @click="exportDetails" class="px-4 py-2 bg-emerald-600 text-white rounded-md">匯出</button>
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
              <h3 class="font-medium">明細列表</h3>
              <div v-if="selected" class="flex items-center gap-2">
                <label class="text-sm text-gray-600">狀態篩選：</label>
                <select v-model="detailStatusFilter" @change="loadDetails(selected.id)" class="px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500">
                  <option v-for="item in statusList" :key="item.value" :value="item.value">{{ item.label }}</option>
                </select>
              </div>
            </div>
            <div v-if="selected && stats" class="flex gap-4 text-sm">
              <div class="flex items-center gap-1">
                <span class="text-gray-600">總數量：</span>
                <span class="font-semibold text-gray-900">{{ stats.total || 0 }}</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="text-gray-600">已列印：</span>
                <span class="font-semibold text-blue-600">{{ stats.printed || 0 }}</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="text-gray-600">已核銷：</span>
                <span class="font-semibold text-green-600">{{ stats.used || 0 }}</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="text-gray-600">作廢：</span>
                <span class="font-semibold text-red-600">{{ stats.voided || 0 }}</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="text-gray-600">回收：</span>
                <span class="font-semibold text-orange-600">{{ stats.recycled || 0 }}</span>
              </div>
            </div>
          </div>
          
          <!-- 批量操作區域 -->
          <div v-if="selected" class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex flex-col gap-4">
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                  <label class="text-sm font-medium text-gray-700">動作選項：</label>
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" value="void" v-model="batchAction" class="text-red-600 focus:ring-red-500" />
                    <span class="text-sm">作廢</span>
                  </label>
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" value="recycle" v-model="batchAction" class="text-orange-600 focus:ring-orange-500" />
                    <span class="text-sm">回收</span>
                  </label>
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" value="updateDates" v-model="batchAction" class="text-blue-600 focus:ring-blue-500" />
                    <span class="text-sm">更改期限</span>
                  </label>
                </div>
                <div class="flex-1"></div>
                <div class="text-sm text-gray-600">
                  已選擇 <span class="font-semibold text-amber-600">{{ selectedDetailIds.length }}</span> 筆
                </div>
                <button
                  @click="printSelected"
                  :disabled="selectedDetailIds.length === 0"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  title="列印選中的明細"
                >
                  <i class="fas fa-print mr-2"></i>
                  列印
                </button>
                <button
                  @click="submitBatchAction"
                  :disabled="!batchAction || selectedDetailIds.length === 0 || submittingBatch"
                  class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-md hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <i v-if="submittingBatch" class="fas fa-spinner fa-spin mr-2"></i>
                  確認
                </button>
              </div>
              <!-- 更改期限的日期輸入 -->
              <div v-if="batchAction === 'updateDates'" class="flex items-center gap-4 pt-2 border-t border-gray-300">
                <div class="flex items-center gap-2">
                  <label class="text-sm font-medium text-gray-700">使用起始日期：</label>
                  <input
                    type="date"
                    v-model="batchUseStartDate"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div class="flex items-center gap-2">
                  <label class="text-sm font-medium text-gray-700">使用結束日期：</label>
                  <input
                    type="date"
                    v-model="batchUseEndDate"
                    :min="batchUseStartDate"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
            </div>
          </div>

          <div v-if="!selected" class="text-gray-500">請先選擇工程</div>
          <div v-else class="border rounded-md max-h-[60vh] overflow-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 py-2 text-left w-12">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      @change="toggleSelectAll"
                      class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                    />
                  </th>
                  <th class="px-3 py-2 text-left">Barcode</th>
                  <th class="px-3 py-2 text-left">狀態</th>
                  <th class="px-3 py-2 text-left">使用起迄日</th>
                  <th class="px-3 py-2 text-left">列印時間</th>
                  <th class="px-3 py-2 text-left">核銷人員/時間</th>
                  <th class="px-3 py-2 text-left">建立時間</th>
                  <th class="px-3 py-2 text-left">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="d in details" :key="d.id">
                  <td class="px-3 py-2">
                    <input
                      v-if="isSelectable(d.status)"
                      type="checkbox"
                      :value="d.id"
                      v-model="selectedDetailIds"
                      class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                    />
                    <span v-else class="text-xs text-gray-400">-</span>
                  </td>
                  <td class="px-3 py-2 font-mono">{{ d.barcode }}</td>
                  <td class="px-3 py-2">
                    <span :class="getStatusClass(d.status)">
                      {{ getStatusLabel(d.status) }}
                    </span>
                  </td>
                  <td class="px-3 py-2">{{ formatDate(d.use_start_date) }}<br>{{ formatDate(d.use_end_date) }}</td>
                  <td class="px-3 py-2">{{ formatDateTimeShort(d.print_at) }}</td>
                  <td class="px-3 py-2">
                    <div v-if="d.verified_at || d.verified_by_name">
                      <div v-if="d.verified_by_name" class="font-medium">{{ d.verified_by_name }}</div>
                      <div v-if="d.verified_at" class="text-xs text-gray-500">{{ formatDateTimeShort(d.verified_at) }}</div>
                    </div>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="px-3 py-2">{{ formatDateTimeShort(d.created_at) }}</td>
                  <td class="px-3 py-2">
                    <span v-if="d.status === 3" class="text-xs text-gray-400">已作廢</span>
                    <span v-else-if="d.status === 4" class="text-xs text-gray-400">已回收</span>
                    <span v-else-if="d.status === 2" class="text-xs text-gray-400">已核銷</span>
                    <span v-else class="text-xs text-gray-400">-</span>
                  </td>
                </tr>
                <tr v-if="details.length === 0">
                  <td class="px-3 py-6 text-center text-gray-500" colspan="9">無明細</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <Toast />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import earthDataAPI from '../api/earthData.js'
import commonAPI from '../api/common.js'
import { useToast } from '../composables/useToast.js'
import Toast from '../components/Toast.vue'
import { formatDate, formatDateTime, formatDateTimeShort} from '../utils/date.js'

const { success, error } = useToast()

const status = ref('all')
const earthInput = ref('')
const earthOptions = ref([])
const selected = ref(null)
const details = ref([])
const stats = ref(null)
const detailStatusFilter = ref(null) // 明細狀態篩選
const statusList = ref([]) // 狀態列表（從 API 取得）

// batch action state
const selectedDetailIds = ref([])
const batchAction = ref('')
const batchUseStartDate = ref('')
const batchUseEndDate = ref('')
const submittingBatch = ref(false)

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

// 處理工程輸入變更（當從 datalist 選擇時）
const handleEarthInputChange = () => {
  const raw = (earthInput.value || '').trim()
  if (!raw) return
  
  // 檢查是否包含分隔符號（表示是從 datalist 選擇的）
  if (raw.includes('｜')) {
    loadSelectedEarth()
  }
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
    await loadDetails(id)
  }
}

const loadDetails = async (id) => {
  const params = {}
  if (detailStatusFilter.value !== null) {
    params.status = detailStatusFilter.value
  }
  const d = await earthDataAPI.details(id, params)
    if (d.status) {
      details.value = Array.isArray(d.data?.details) ? d.data.details : []
    stats.value = d.data?.stats || null
    // 清除選擇
    selectedDetailIds.value = []
    batchAction.value = ''
    batchUseStartDate.value = ''
    batchUseEndDate.value = ''
  }
}

const clearSelection = () => {
  selected.value = null
  earthInput.value = ''
  details.value = []
  stats.value = null
  selectedDetailIds.value = []
  batchAction.value = ''
  batchUseStartDate.value = ''
  batchUseEndDate.value = ''
  detailStatusFilter.value = null
  loadEarthDatalist()
}

// 重整明細資料
const refreshDetails = async () => {
  if (!selected.value?.id) return
  await loadDetails(selected.value.id)
  success('明細資料已更新')
}

const exportDetails = () => {
  if (!selected.value?.id) return
  const token = localStorage.getItem('token') || ''
  const base = `/api/earth-data/${selected.value.id}/details/export`
  const url = token ? `${base}?token=${encodeURIComponent(token)}` : base
  window.open(url, '_blank')
}

// 判斷是否可以選擇（作廢/回收/已核銷不可選）
const isSelectable = (status) => {
  return status !== 3 && status !== 4 && status !== 2
}

// 全選/取消全選
const isAllSelected = computed(() => {
  const selectableDetails = details.value.filter(d => isSelectable(d.status))
  if (selectableDetails.length === 0) return false
  return selectableDetails.every(d => selectedDetailIds.value.includes(d.id))
})

const toggleSelectAll = () => {
  const selectableDetails = details.value.filter(d => isSelectable(d.status))
  if (isAllSelected.value) {
    // 取消全選
    selectedDetailIds.value = selectedDetailIds.value.filter(id => 
      !selectableDetails.some(d => d.id === id)
    )
  } else {
    // 全選
    const selectableIds = selectableDetails.map(d => d.id)
    selectedDetailIds.value = [...new Set([...selectedDetailIds.value, ...selectableIds])]
  }
}

// 列印選中的明細
const printSelected = () => {
  if (!selected.value?.id) return
  if (selectedDetailIds.value.length === 0) {
    error('請選擇要列印的明細')
    return
  }

  // 構建 URL，將 detail_ids 作為查詢參數傳遞
  const detailIdsParam = selectedDetailIds.value.join(',')
  const url = `/print/earth-data/${selected.value.id}/selected?detail_ids=${detailIdsParam}`
  window.open(url, '_blank')
  
  // 列印後重新載入明細以更新狀態
  setTimeout(() => {
    loadDetails(selected.value.id)
  }, 1000)
}

// 批量處理
const submitBatchAction = async () => {
  if (!selected.value?.id) return
  if (!batchAction.value || selectedDetailIds.value.length === 0) {
    error('請選擇動作和要處理的明細')
    return
  }

  submittingBatch.value = true
  try {
    if (batchAction.value === 'updateDates') {
      // 更改期限
      if (!batchUseStartDate.value && !batchUseEndDate.value) {
        error('請至少輸入一個日期')
        submittingBatch.value = false
        return
      }
      if (!confirm(`確定要更新選中的 ${selectedDetailIds.value.length} 筆明細的使用期限嗎？`)) {
        submittingBatch.value = false
        return
      }
      const resp = await earthDataAPI.batchUpdateDates(
        selected.value.id,
        selectedDetailIds.value,
        batchUseStartDate.value || null,
        batchUseEndDate.value || null
      )
      if (resp.status) {
        success(`成功更新 ${resp.data?.updated_count || selectedDetailIds.value.length} 筆明細的使用期限`)
        // 重新載入明細和統計
        await loadDetails(selected.value.id)
        batchUseStartDate.value = ''
        batchUseEndDate.value = ''
      } else {
        error(resp.message || '更新期限失敗')
      }
    } else {
      // 作廢或回收
      const actionLabel = batchAction.value === 'void' ? '作廢' : '回收'
      if (!confirm(`確定要${actionLabel}選中的 ${selectedDetailIds.value.length} 筆明細嗎？`)) {
        submittingBatch.value = false
        return
      }
      const status = batchAction.value === 'void' ? 3 : 4
      const resp = await earthDataAPI.batchUpdateStatus(selected.value.id, selectedDetailIds.value, status)
      if (resp.status) {
        success(`成功${actionLabel} ${resp.data?.updated_count || selectedDetailIds.value.length} 筆明細`)
        // 重新載入明細和統計
        await loadDetails(selected.value.id)
      } else {
        error(resp.message || `${actionLabel}失敗`)
      }
    }
  } catch (e) {
    error('操作失敗：' + (e.message || '未知錯誤'))
  } finally {
    submittingBatch.value = false
  }
}

const getStatusLabel = (status) => {
  const statusItem = statusList.value.find(item => item.value === status)
  return statusItem?.label || '未知'
}

const getStatusClass = (status) => {
  const base = 'px-2 py-1 text-xs rounded font-medium'
  switch (status) {
    case 0: // 未列印
      return `${base} bg-gray-100 text-gray-700`
    case 1: // 已列印
      return `${base} bg-blue-100 text-blue-700`
    case 2: // 已使用
      return `${base} bg-green-100 text-green-700`
    case 3: // 作廢
      return `${base} bg-red-100 text-red-700`
    case 4: // 回收
      return `${base} bg-orange-100 text-orange-700`
    default:
      return `${base} bg-gray-100 text-gray-700`
  }
}



// 載入狀態列表
const loadStatusList = async () => {
  try {
    const resp = await commonAPI.earthDataDetailStatusList()
    if (resp.status && Array.isArray(resp.data)) {
      statusList.value = resp.data
    }
  } catch (e) {
    console.error('載入狀態列表失敗', e)
  }
}

// initial datalist
loadEarthDatalist()
// 載入狀態列表
onMounted(() => {
  loadStatusList()
})
</script>
