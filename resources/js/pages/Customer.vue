<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 客戶管理內容 -->
    <div class="max-w-7xl mx-auto">
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
          <!-- 頁面標題和操作按鈕 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
          <div>
              <h2 class="text-2xl font-bold text-gray-800 mb-2">客戶管理</h2>
              <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                  <li class="inline-flex items-center">
                    <router-link to="/dashboard" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-amber-600 transition-colors duration-200">
                      <i class="fas fa-home mr-2"></i>
                      Dashboard
                    </router-link>
                  </li>
                  <li>
                    <div class="flex items-center">
                      <i class="fas fa-chevron-right text-gray-400 mr-2"></i>
                      <span class="text-sm font-medium text-gray-500">客戶管理</span>
                    </div>
                  </li>
                </ol>
              </nav>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 mt-4 sm:mt-0">
              <button
                @click="openCreateModal"
                class="bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
              >
                <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                新增客戶
              </button>
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

          <!-- 搜尋和篩選 -->
          <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
              <div class="flex-1">
                <div class="relative">
                  <input
                    v-model="searchQuery"
                    @input="debouncedSearch"
                    type="text"
                    placeholder="搜尋客戶（公司名稱、聯絡人、電話、電子郵件、統一編號）"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                  />
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                  </div>
                </div>
              </div>
              <div class="flex gap-2">
                <select
                  v-model="statusFilter"
                  @change="loadCustomers"
                  class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                >
                  <option value="">全部狀態</option>
                  <option value="active">活躍</option>
                  <option value="inactive">停用</option>
                </select>
                <select
                  v-model="sortBy"
                  @change="loadCustomers"
                  class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                >
                  <option value="created_at">建立時間</option>
                  <option value="company_name">公司名稱</option>
                  <option value="contact_person">聯絡人</option>
                  <option value="updated_at">更新時間</option>
                </select>
                <select
                  v-model="sortOrder"
                  @change="loadCustomers"
                  class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                >
                  <option value="desc">降序</option>
                  <option value="asc">升序</option>
                </select>
              </div>
            </div>
          </div>

          <!-- 客戶列表 -->
          <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-amber-50 to-orange-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">公司名稱</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">聯絡人</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">電話</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">電子郵件</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">統一編號</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">狀態</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">建立時間</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-if="loading" class="animate-pulse">
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                      <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                      <div>載入中...</div>
                    </td>
                  </tr>
                  <tr v-else-if="customers.length === 0" class="hover:bg-gray-50">
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                      <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                      <div class="text-lg font-medium">沒有找到客戶資料</div>
                      <div class="text-sm">請嘗試調整搜尋條件或新增客戶</div>
                    </td>
                  </tr>
                  <tr v-else v-for="customer in customers" :key="customer.id" class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ customer.company_name }}</div>
                      <div v-if="customer.address" class="text-sm text-gray-500 truncate max-w-xs">{{ customer.address }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.contact_person }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.phone || '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.email || '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.tax_id || '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="customer.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                        {{ customer.status === 'active' ? '活躍' : '停用' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(customer.created_at) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <button
                          @click="openEditModal(customer)"
                          class="text-amber-600 hover:text-amber-900 transition-colors duration-200"
                          title="編輯"
                        >
                          <i class="fas fa-edit"></i>
                        </button>
                        <button
                          @click="openDeleteModal(customer)"
                          class="text-red-600 hover:text-red-900 transition-colors duration-200"
                          title="刪除"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- 分頁 -->
            <div v-if="pagination && pagination.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
              <div class="flex-1 flex justify-between sm:hidden">
                <button
                  @click="goToPage(pagination.current_page - 1)"
                  :disabled="pagination.current_page <= 1"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  上一頁
                </button>
                <button
                  @click="goToPage(pagination.current_page + 1)"
                  :disabled="pagination.current_page >= pagination.last_page"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  下一頁
                </button>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    顯示第
                    <span class="font-medium">{{ pagination.from || 0 }}</span>
                    到
                    <span class="font-medium">{{ pagination.to || 0 }}</span>
                    筆，共
                    <span class="font-medium">{{ pagination.total }}</span>
                    筆資料
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <button
                      @click="goToPage(pagination.current_page - 1)"
                      :disabled="pagination.current_page <= 1"
                      class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <i class="fas fa-chevron-left"></i>
                    </button>
                    <template v-for="page in visiblePages" :key="page">
                      <button
                        v-if="page !== '...'"
                        @click="goToPage(page)"
                        :class="page === pagination.current_page ? 'z-10 bg-amber-50 border-amber-500 text-amber-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                      >
                        {{ page }}
                      </button>
                      <span v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        ...
                      </span>
                    </template>
                    <button
                      @click="goToPage(pagination.current_page + 1)"
                      :disabled="pagination.current_page >= pagination.last_page"
                      class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <i class="fas fa-chevron-right"></i>
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>

    <!-- 新增/編輯客戶 Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ isEditing ? '編輯客戶' : '新增客戶' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
          
          <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">公司名稱 *</label>
                <input
                  v-model="form.company_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.company_name }"
                />
                <p v-if="errors.company_name" class="text-red-500 text-xs mt-1">{{ errors.company_name }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">聯絡人 *</label>
                <input
                  v-model="form.contact_person"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.contact_person }"
                />
                <p v-if="errors.contact_person" class="text-red-500 text-xs mt-1">{{ errors.contact_person }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">電話</label>
                <input
                  v-model="form.phone"
                  type="tel"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.phone }"
                />
                <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">電子郵件</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.email }"
                />
                <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">統一編號</label>
                <input
                  v-model="form.tax_id"
                  type="text"
                  maxlength="8"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.tax_id }"
                />
                <p v-if="errors.tax_id" class="text-red-500 text-xs mt-1">{{ errors.tax_id }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">狀態</label>
                <select
                  v-model="form.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.status }"
                >
                  <option value="active">活躍</option>
                  <option value="inactive">停用</option>
                </select>
                <p v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</p>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">地址</label>
              <textarea
                v-model="form.address"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.address }"
              ></textarea>
              <p v-if="errors.address" class="text-red-500 text-xs mt-1">{{ errors.address }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">備註</label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.notes }"
              ></textarea>
              <p v-if="errors.notes" class="text-red-500 text-xs mt-1">{{ errors.notes }}</p>
        </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500"
              >
                取消
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-4 py-2 text-sm font-medium text-white bg-amber-600 border border-transparent rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>
                {{ isEditing ? '更新' : '建立' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 刪除確認 Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">確認刪除</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              您確定要刪除客戶「<strong>{{ customerToDelete?.company_name }}</strong>」嗎？此操作無法復原。
            </p>
          </div>
          <div class="flex justify-center space-x-3 pt-4">
            <button
              @click="closeDeleteModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500"
            >
              取消
            </button>
            <button
              @click="confirmDelete"
              :disabled="deleting"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="deleting" class="fas fa-spinner fa-spin mr-2"></i>
              刪除
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useToast } from '@/composables/useToast'
import customerAPI from '../api/customer.js'

const { showToast } = useToast()

// 響應式資料
const customers = ref([])
const loading = ref(false)
const pagination = ref(null)
const searchQuery = ref('')
const statusFilter = ref('')
const sortBy = ref('created_at')
const sortOrder = ref('desc')

// Modal 相關
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const customerToDelete = ref(null)

// 表單資料
const form = reactive({
  company_name: '',
  contact_person: '',
  phone: '',
  email: '',
  address: '',
  tax_id: '',
  status: 'active',
  notes: ''
})

// 錯誤訊息
const errors = ref({})

// 防抖搜尋
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadCustomers()
  }, 500)
}

// 計算屬性
const visiblePages = computed(() => {
  if (!pagination.value) return []
  
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const pages = []
  
  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last)
    } else if (current >= last - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = last - 4; i <= last; i++) {
        pages.push(i)
      }
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last)
    }
  }
  
  return pages
})

// 方法
const loadCustomers = async (page = 1) => {
  try {
    loading.value = true
    const resp = await customerAPI.getCustomers({
      page,
      search: searchQuery.value,
      status: statusFilter.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
      per_page: 15
    })

    if (resp.status) {
      customers.value = Array.isArray(resp.data?.data) ? resp.data.data : []
      pagination.value = {
        current_page: resp.data.current_page,
        last_page: resp.data.last_page,
        per_page: resp.data.per_page,
        total: resp.data.total,
        from: resp.data.from,
        to: resp.data.to
      }
    } else {
      throw new Error(resp.message || '載入客戶資料失敗')
    }
  } catch (error) {
    console.error('載入客戶資料錯誤:', error)
    showToast(error.message || '載入客戶資料失敗', 'error')
  } finally {
    loading.value = false
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadCustomers(page)
  }
}

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  showModal.value = true
}

const openEditModal = (customer) => {
  isEditing.value = true
  form.id = customer.id
  form.company_name = customer.company_name
  form.contact_person = customer.contact_person
  form.phone = customer.phone || ''
  form.email = customer.email || ''
  form.address = customer.address || ''
  form.tax_id = customer.tax_id || ''
  form.status = customer.status
  form.notes = customer.notes || ''
  errors.value = {}
  showModal.value = true
}

const openDeleteModal = (customer) => {
  customerToDelete.value = customer
  showDeleteModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
  errors.value = {}
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  customerToDelete.value = null
}

const resetForm = () => {
  form.id = null
  form.company_name = ''
  form.contact_person = ''
  form.phone = ''
  form.email = ''
  form.address = ''
  form.tax_id = ''
  form.status = 'active'
  form.notes = ''
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    let resp
    if (isEditing.value) {
      resp = await customerAPI.updateCustomer(form.id, form)
    } else {
      resp = await customerAPI.createCustomer(form)
    }

    if (resp.status) {
      showToast(isEditing.value ? '客戶更新成功' : '客戶建立成功', 'success')
      closeModal()
      loadCustomers()
    } else {
      if (resp.errors) {
        errors.value = resp.errors
      } else {
        throw new Error(resp.message || '操作失敗')
      }
    }
  } catch (error) {
    console.error('提交表單錯誤:', error)
    showToast(error.message || '操作失敗', 'error')
  } finally {
    submitting.value = false
  }
}

const confirmDelete = async () => {
  try {
    deleting.value = true

    const resp = await customerAPI.deleteCustomer(customerToDelete.value.id)

    if (resp.status) {
      showToast('客戶刪除成功', 'success')
      closeDeleteModal()
      loadCustomers()
    } else {
      throw new Error(resp.message || '刪除失敗')
    }
  } catch (error) {
    console.error('刪除客戶錯誤:', error)
    showToast(error.message || '刪除失敗', 'error')
  } finally {
    deleting.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// 監聽器
watch([statusFilter, sortBy, sortOrder], () => {
  loadCustomers()
})

// 生命週期
onMounted(() => {
  loadCustomers()
})
</script>
