<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
        <!-- 標題與操作 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">公告欄</h2>
            <p class="text-gray-600">管理系統公告與重要訊息</p>
          </div>
          <div class="flex gap-3 mt-4 sm:mt-0">
            <button @click="openCreate" class="bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
              <i class="fas fa-plus mr-2"></i>新增公告
            </button>
            <router-link
              to="/dashboard"
              class="bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              返回儀表板
            </router-link>
          </div>
        </div>

        <!-- 篩選列 -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <input v-model="filters.search" type="text" placeholder="搜尋標題/內文" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent" @input="debouncedReload" />

          <select v-model="filters.is_active" @change="reload" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            <option value="">全部狀態</option>
            <option value="1">啟用</option>
            <option value="0">停用</option>
          </select>

          <input v-model="filters.starts_from" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent" @change="reload" />
          <input v-model="filters.ends_to" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent" @change="reload" />
        </div>

        <!-- 列表 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gradient-to-r from-amber-50 to-orange-50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">標題</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">期間</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">狀態</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">更新時間</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="loading">
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>載入中...</td>
                </tr>
                <tr v-else-if="items.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500">沒有公告資料</td>
                </tr>
                <tr v-else v-for="it in items" :key="it.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ it.title }}</div>
                    <div v-if="it.content" class="text-sm text-gray-500 truncate max-w-xl">{{ it.content }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDateTime(it.starts_at) }} ~ {{ formatDateTime(it.ends_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="it.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ it.is_active ? '啟用' : '停用' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(it.updated_at) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex gap-2">
                      <button @click="openEdit(it)" class="text-amber-600 hover:text-amber-900"><i class="fas fa-edit"></i></button>
                      <button @click="confirmDelete(it)" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 分頁 -->
          <div v-if="pager && pager.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="text-sm text-gray-700">顯示第 <span class="font-medium">{{ pager.from || 0 }}</span> 到 <span class="font-medium">{{ pager.to || 0 }}</span> 筆，共 <span class="font-medium">{{ pager.total }}</span> 筆</div>
            <div class="flex items-center gap-2">
              <button :disabled="pager.current_page <= 1" @click="goPage(pager.current_page-1)" class="px-3 py-1 border rounded disabled:opacity-50">上一頁</button>
              <button :disabled="pager.current_page >= pager.last_page" @click="goPage(pager.current_page+1)" class="px-3 py-1 border rounded disabled:opacity-50">下一頁</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 新增/編輯 Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">{{ editing ? '編輯公告' : '新增公告' }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
          </div>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">標題 *</label>
                <input v-model="form.title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': errors.title }" />
                <p v-if="errors.title" class="text-red-500 text-xs mt-1">{{ errors.title }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">開始時間</label>
                <input v-model="form.starts_at" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">結束時間</label>
                <input v-model="form.ends_at" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">啟用狀態</label>
                <select v-model="form.is_active" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                  <option :value="true">啟用</option>
                  <option :value="false">停用</option>
                </select>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">公告內文</label>
                <textarea v-model="form.content" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">取消</button>
              <button type="submit" :disabled="submitting" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-md hover:bg-amber-700 disabled:opacity-50">
                <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>{{ editing ? '更新' : '建立' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useToast } from '../composables/useToast.js'
import announcementAPI from '../api/announcement.js'

const { success, error: showError } = useToast()

const items = ref([])
const loading = ref(false)
const pager = ref(null)
const filters = reactive({ search: '', is_active: '', starts_from: '', ends_to: '', sort_by: 'starts_at', sort_order: 'desc', per_page: 15 })

let searchTimeout = null
const debouncedReload = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => reload(), 500) }

const showModal = ref(false)
const editing = ref(false)
const submitting = ref(false)
const currentId = ref(null)
const form = reactive({ title: '', content: '', starts_at: '', ends_at: '', is_active: true })
const errors = reactive({})

const load = async (page = 1) => {
  loading.value = true
  try {
    const resp = await announcementAPI.list({ ...filters, page })
    if (resp.status) {
      items.value = Array.isArray(resp.data?.data) ? resp.data.data : []
      pager.value = {
        current_page: resp.data.current_page,
        last_page: resp.data.last_page,
        per_page: resp.data.per_page,
        total: resp.data.total,
        from: resp.data.from,
        to: resp.data.to
      }
    } else {
      throw new Error(resp.message || '載入公告失敗')
    }
  } catch (e) {
    console.error('載入公告錯誤:', e)
    showError(e.message || '載入公告失敗')
  } finally {
    loading.value = false
  }
}

const reload = () => load(pager.value?.current_page || 1)
const goPage = (p) => { if (p >= 1 && p <= pager.value.last_page) load(p) }

const openCreate = () => {
  editing.value = false
  currentId.value = null
  Object.assign(form, { title: '', content: '', starts_at: '', ends_at: '', is_active: true })
  Object.keys(errors).forEach(k => delete errors[k])
  showModal.value = true
}

const openEdit = (it) => {
  editing.value = true
  currentId.value = it.id
  Object.assign(form, {
    title: it.title || '',
    content: it.content || '',
    starts_at: it.starts_at ? toDatetimeLocal(it.starts_at) : '',
    ends_at: it.ends_at ? toDatetimeLocal(it.ends_at) : '',
    is_active: !!it.is_active
  })
  Object.keys(errors).forEach(k => delete errors[k])
  showModal.value = true
}

const closeModal = () => { showModal.value = false }

const submit = async () => {
  submitting.value = true
  try {
    // convert datetime-local to ISO if present
    const payload = {
      title: form.title,
      content: form.content,
      starts_at: form.starts_at ? new Date(form.starts_at).toISOString() : null,
      ends_at: form.ends_at ? new Date(form.ends_at).toISOString() : null,
      is_active: !!form.is_active
    }
    let resp
    if (editing.value) {
      resp = await announcementAPI.update(currentId.value, payload)
    } else {
      resp = await announcementAPI.create(payload)
    }
    if (resp.status) {
      success(editing.value ? '公告更新成功' : '公告建立成功')
      showModal.value = false
      await load()
    } else if (resp.errors) {
      Object.assign(errors, resp.errors)
      showError(resp.message || '操作失敗')
    } else {
      throw new Error(resp.message || '操作失敗')
    }
  } catch (e) {
    console.error('提交公告錯誤:', e)
    showError(e.message || '操作失敗')
  } finally {
    submitting.value = false
  }
}

const confirmDelete = async (it) => {
  if (!confirm(`確認刪除公告「${it.title}」？`)) return
  try {
    const resp = await announcementAPI.remove(it.id)
    if (resp.status) {
      success('公告刪除成功')
      await load()
    } else {
      throw new Error(resp.message || '刪除失敗')
    }
  } catch (e) {
    console.error('刪除公告錯誤:', e)
    showError(e.message || '刪除失敗')
  }
}

const formatDateTime = (s) => {
  if (!s) return '-'
  const d = new Date(s)
  return d.toLocaleString('zh-TW', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}
const toDatetimeLocal = (s) => {
  // yyyy-MM-ddTHH:mm for input
  const d = new Date(s)
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

onMounted(() => load())
</script>
