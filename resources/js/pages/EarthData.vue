<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">土單資料管理</h2>
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
                    <span class="text-sm font-medium text-gray-500">土單資料管理</span>
                  </div>
                </li>
              </ol>
            </nav>
          </div>
          <div class="flex flex-col sm:flex-row gap-4 mt-4 sm:mt-0">
            <button @click="openCreateModal" class="bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
              <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
              </svg>
              新增工程
            </button>
          </div>
        </div>

        <div class="mb-6">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <div class="relative">
                <input v-model="searchQuery" @input="debouncedSearch" type="text" placeholder="搜尋（批號、客戶、工程名稱）" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-gray-400"></i>
                </div>
              </div>
            </div>
            <input v-model="issueDateFrom" @change="loadEarthData()" type="date" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200" />
            <span class="self-center text-gray-500">~</span>
            <input v-model="issueDateTo" @change="loadEarthData()" type="date" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200" />
            <select v-model="sortBy" @change="loadEarthData" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200">
              <option value="created_at">建立時間</option>
              <option value="issue_date">開立日期</option>
              <option value="batch_no">批號</option>
            </select>
            <select v-model="sortOrder" @change="loadEarthData" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200">
              <option value="desc">降序</option>
              <option value="asc">升序</option>
            </select>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full table-auto divide-y divide-gray-200">
              <thead class="bg-gradient-to-r from-amber-50 to-orange-50">
                <tr>
                  <th class="th-base min-w-[120px]">批號</th>
                  <th class="th-base min-w-[160px]">工程名稱</th>
                  <th class="th-base min-w-[140px]">管制編號</th>
                  <th class="th-base min-w-[120px]">開立日期</th>
                  <th class="th-base min-w-[110px]">開立張數</th>
                  <th class="th-base min-w-[120px]">客戶</th>
                  <th class="th-base min-w-[110px]">清運業者</th>
                  <th class="th-base min-w-[100px]">有效起</th>
                  <th class="th-base min-w-[100px]">有效迄</th>
                  <th class="th-base min-w-[110px]">載運數量</th>
                  <th class="th-base min-w-[120px]">載運土質</th>
                  <th class="th-base min-w-[160px]">狀態說明</th>
                  <th class="th-base min-w-[160px]">備註說明</th>
                  <th class="th-base min-w-[110px]">建檔人員</th>
                  <th class="th-base min-w-[110px]">修改人員</th>
                  <th class="th-base min-w-[140px]">系統流水號</th>
                  <th class="th-base min-w-[90px]">狀態</th>
                  <th class="th-base min-w-[100px]">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="loading" class="animate-pulse">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>載入中...</div>
                  </td>
                </tr>
                <tr v-else-if="rows.length === 0" class="hover:bg-gray-50">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-file-alt text-4xl mb-4 text-gray-300"></i>
                    <div class="text-lg font-medium">沒有找到土單資料</div>
                    <div class="text-sm">請嘗試調整搜尋條件或新增土單</div>
                  </td>
                </tr>
                <tr v-else v-for="item in rows" :key="item.id" class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="td-base">{{ item.batch_no }}</td>
                  <td class="td-base truncate max-w-[20rem]" :title="item.project_name">{{ item.project_name }}</td>
                  <td class="td-base">{{ item.flow_control_no }}</td>
                  <td class="td-base">{{ formatDate(item.issue_date) }}</td>
                  <td class="td-base">
                    <div class="flex items-center gap-2">
                      <span>{{ item.issue_count ?? 0 }}</span>
                      <button @click="openAdjustModal(item)" class="btn-adjust" title="調整張數">調整</button>
                    </div>
                  </td>
                  <td class="td-base">{{ item.customer_name || '-' }}</td>
                  <td class="td-base">
                    <div class="flex items-center gap-2 flex-wrap">
                      <template v-if="item.cleaners && item.cleaners.length > 0">
                        <span v-for="cleaner in item.cleaners" :key="cleaner.id" class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                          {{ cleaner.cleaner_name }}
                        </span>
                      </template>
                      <span v-else class="text-gray-400">-</span>
                    </div>
                  </td>
                  <td class="td-base">{{ formatDate(item.valid_date_from) }}</td>
                  <td class="td-base">{{ formatDate(item.valid_date_to) }}</td>
                  <td class="td-base">{{ item.carry_qty }}</td>
                  <td class="td-base">{{ item.carry_soil_type }}</td>
                  <td class="td-base truncate max-w-[20rem]" :title="item.status_desc">
                    {{ item.status_desc || '-' }}
                  </td>
                  <td class="td-base truncate max-w-[20rem]" :title="item.remark_desc">
                    {{ item.remark_desc || '-' }}
                  </td>
                  <td class="td-base">{{ item.created_by_name }}</td>
                  <td class="td-base">{{ item.updated_by_name }}</td>
                  <td class="td-base">{{ item.sys_serial_no }}</td>
                  <td class="td-base">{{ item.status }}</td>
                  <td class="td-base font-medium">
                    <div class="action-buttons">
                      <button @click="openEditModal(item)" class="action-btn action-btn--edit" title="編輯">
                        <i class="fas fa-edit action-icon"></i>
                      </button>
                      <button @click="openDeleteModal(item)" class="action-btn action-btn--delete" title="刪除">
                        <i class="fas fa-trash action-icon"></i>
                      </button>
                      <button @click="openPrint(item)" class="action-btn action-btn--print" title="列印未列印">
                        <i class="fas fa-print action-icon"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="pagination && pagination.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
              <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">上一頁</button>
              <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">下一頁</button>
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
                  <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  <template v-for="page in visiblePages" :key="page">
                    <button v-if="page !== '...'" @click="goToPage(page)" :class="page === pagination.current_page ? 'z-10 bg-amber-50 border-amber-500 text-amber-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                      {{ page }}
                    </button>
                    <span v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                  </template>
                  <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 h-full w-full z-50 flex items-center justify-center p-4">
      <div class="relative mx-auto px-6 md:px-8 py-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white h-[80vh] max-h-[80vh] overflow-hidden flex flex-col min-h-0">
        <div class="mt-1 flex flex-col h-full min-h-0">
          <div class="flex items-center justify-between mb-4 shrink-0 sticky top-0 bg-white z-10 pb-2">
            <h3 class="text-lg font-medium text-gray-900">{{ isEditing ? '編輯土單' : '新增土單' }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <form @submit.prevent="submitForm" class="flex flex-col h-full min-h-0">
            <div class="flex-1 overflow-y-scroll pr-1 min-h-0 px-4 md:px-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                  <label class="label-base">批號 *</label>
                  <input v-model="form.batch_no" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': errors.batch_no }" />
                  <p v-if="errors.batch_no" class="text-red-500 text-xs mt-1">{{ errors.batch_no }}</p>
                </div>
                <div>
                  <label class="label-base">開立日期</label>
                  <input v-model="form.issue_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': errors.issue_date }" />
                  <p v-if="errors.issue_date" class="text-red-500 text-xs mt-1">{{ errors.issue_date }}</p>
                </div>
                <div>
                  <label class="label-base">工程名稱</label>
                  <input v-model="form.project_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                </div>
                <div>
                  <label class="label-base">工程流向管制編號</label>
                  <input v-model="form.flow_control_no" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                </div>
                <div>
                  <label class="label-base">客戶</label>
                  <select v-model.number="form.customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': errors.customer_id }">
                    <option :value="0">請選擇</option>
                    <option v-for="c in customerOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                  <p v-if="errors.customer_id" class="text-red-500 text-xs mt-1">{{ errors.customer_id }}</p>
                </div>
                <div>
                  <label class="label-base">土方清運業者（可多選）</label>
                  <Multiselect
                    v-model="form.cleaner_ids"
                    :options="cleanerOptions"
                    mode="tags"
                    :close-on-select="false"
                    :searchable="true"
                    :clear-on-select="false"
                    :preserve-search="true"
                    placeholder="請選擇清運業者"
                    label="name"
                    valueProp="id"
                    trackBy="id"
                    :maxHeight="200"
                    :class="{ 'border-red-500': errors.cleaner_ids }"
                  />
                  <p v-if="errors.cleaner_ids" class="text-red-500 text-xs mt-1">{{ errors.cleaner_ids }}</p>
                  <p class="text-xs text-gray-500 mt-1">已選擇 {{ form.cleaner_ids?.length || 0 }} 個清運業者</p>
                </div>
                <div>
                  <label class="label-base">有效期限（起）</label>
                  <input v-model="form.valid_date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                </div>
                <div>
                  <label class="label-base">有效期限（迄）</label>
                  <input v-model="form.valid_date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                </div>
                <div>
                  <label class="label-base">載運數量</label>
                  <input v-model.number="form.carry_qty" type="number" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                </div>
                <div>
                  <label class="label-base">載運土質</label>
                  <input v-model="form.carry_soil_type" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                </div>
                <div class="md:col-span-2">
                  <label class="label-base">狀態說明</label>
                  <input v-model="form.status_desc" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                </div>
                <div class="md:col-span-2">
                  <label class="label-base">備註說明</label>
                  <textarea v-model="form.remark_desc" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t mt-4 bg-white shrink-0 sticky bottom-0 z-10 px-4 md:px-6">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">取消</button>
              <button type="submit" :disabled="submitting" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 border border-transparent rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>
                {{ isEditing ? '更新' : '建立' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">確認刪除</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">您確定要刪除土單「<strong>{{ itemToDelete?.batch_no }}</strong>」嗎？此操作無法復原。</p>
          </div>
          <div class="flex justify-center space-x-3 pt-4">
            <button @click="closeDeleteModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">取消</button>
            <button @click="confirmDelete" :disabled="deleting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
              <i v-if="deleting" class="fas fa-spinner fa-spin mr-2"></i>
              刪除
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Adjust Count Modal -->
    <div v-if="showAdjustModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 h-full w-full z-50 flex items-center justify-center p-4">
      <div class="relative mx-auto px-6 py-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-medium text-gray-900">調整開立張數</h3>
          <button @click="closeAdjustModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="space-y-4">
          <div>
            <label class="label-base">操作</label>
            <div class="flex items-center gap-6">
              <label class="inline-flex items-center gap-2">
                <input type="radio" value="add" v-model="adjustAction" />
                <span>增加</span>
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="radio" value="remove" v-model="adjustAction" />
                <span>減少（僅刪未核銷）</span>
              </label>
            </div>
          </div>
          <div class="text-sm text-gray-700 space-y-1">
            <div v-if="loadingAdjustTotals" class="text-gray-500 flex items-center gap-2">
              <i class="fas fa-spinner fa-spin"></i>
              讀取統計中...
            </div>
            <template v-else>
              <div>總張數：<span class="font-semibold">{{ adjustTotals?.total ?? 0 }}</span></div>
              <div>已印數量：<span class="font-semibold">{{ adjustTotals?.verified ?? 0 }}</span></div>
              <div>未印張數：<span class="font-semibold">{{ adjustTotals?.pending ?? 0 }}</span></div>
            </template>
          </div>
          <div>
            <label class="label-base">數量</label>
            <input
              type="number"
              min="1"
              :max="adjustAction === 'remove' ? (adjustTotals?.pending || 0) : null"
              v-model.number="adjustCount"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
            <p v-if="adjustAction === 'remove' && Number(adjustCount) > (adjustTotals?.pending || 0)" class="text-red-600 text-xs mt-1">
              減少數量不可超過未印張數（{{ adjustTotals?.pending || 0 }}）
            </p>
          </div>
        </div>
        <div class="mt-5 flex justify-end gap-3">
          <button @click="closeAdjustModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">取消</button>
          <button @click="submitAdjust" :disabled="submittingAdjust" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 border border-transparent rounded-md hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed">
            <i v-if="submittingAdjust" class="fas fa-spinner fa-spin mr-2"></i>
            確認
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- Print Dialog -->
  <PrintDialog
    v-model="showPrintModal"
    :totals="printTotals"
    :loading="loadingPrintTotals"
    v-model:count="printCount"
    @submit="submitPrint"
  />
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'
import PrintDialog from '@/components/PrintDialog.vue'
import { useToast } from '@/composables/useToast'
import earthDataAPI from '../api/earthData.js'
import commonAPI from '../api/common.js'

const { showToast } = useToast()

const rows = ref([])
const loading = ref(false)
const pagination = ref(null)
const searchQuery = ref('')
const issueDateFrom = ref('')
const issueDateTo = ref('')
const sortBy = ref('created_at')
const sortOrder = ref('desc')

const showModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const itemToDelete = ref(null)
// adjust count modal state
const showAdjustModal = ref(false)
const adjustTarget = ref(null)
const adjustAction = ref('add')
const adjustCount = ref(1)
const submittingAdjust = ref(false)
const adjustTotals = ref({ total: 0, verified: 0, pending: 0 })
const loadingAdjustTotals = ref(false)

// print dialog state
const showPrintModal = ref(false)
const printTarget = ref(null)
const printTotals = ref({ total: 0, verified: 0, pending: 0 })
const printCount = ref(1)
const loadingPrintTotals = ref(false)

// options for selects
const cleanerOptions = ref([])
const customerOptions = ref([])

// inline edit state
const editing = reactive({}) // map: id -> shallow copy of editing values
const editingField = reactive({}) // map: id -> active field name

const isEditingCell = (id, field) => editingField[id] === field

const startInline = (item, field) => {
  editing[item.id] = { status_desc: item.status_desc || '', remark_desc: item.remark_desc || '' }
  editingField[item.id] = field
}

const cancelInline = (id, field) => {
  delete editing[id]
  delete editingField[id]
}

const submitInline = async (item, field) => {
  try {
    const payload = { [field]: editing[item.id]?.[field] ?? '' }
    const resp = await earthDataAPI.update(item.id, payload)
    if (resp.status) {
      item[field] = payload[field]
      showToast('已更新', 'success')
    } else {
      throw new Error(resp.message || '更新失敗')
    }
  } catch (e) {
    showToast(e.message || '更新失敗', 'error')
  } finally {
    // keep editing buffers so inputs remain editable
  }
}

const form = reactive({
  id: null,
  batch_no: '',
  issue_date: '',
  issue_count: 0,
  customer_id: 0,
  valid_date_from: '',
  valid_date_to: '',
  cleaner_ids: [],
  project_name: '',
  flow_control_no: '',
  carry_qty: 0,
  carry_soil_type: '',
  status_desc: '',
  remark_desc: '',
  sys_serial_no: '',
  status: 'active'
})

const errors = ref({})

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadEarthData()
  }, 500)
}

// load select options
const loadCleanerOptions = async () => {
  try {
    const resp = await commonAPI.cleaners()
    if (resp.status) {
      cleanerOptions.value = Array.isArray(resp.data) ? resp.data : []
    }
  } catch (e) {}
}

const loadCustomerOptions = async () => {
  try {
    const resp = await commonAPI.customers()
    if (resp.status) {
      customerOptions.value = Array.isArray(resp.data) ? resp.data : []
    }
  } catch (e) {}
}

const visiblePages = computed(() => {
  if (!pagination.value) return []
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const pages = []
  if (last <= 7) {
    for (let i = 1; i <= last; i++) pages.push(i)
  } else if (current <= 4) {
    for (let i = 1; i <= 5; i++) pages.push(i)
    pages.push('...')
    pages.push(last)
  } else if (current >= last - 3) {
    pages.push(1)
    pages.push('...')
    for (let i = last - 4; i <= last; i++) pages.push(i)
  } else {
    pages.push(1)
    pages.push('...')
    for (let i = current - 1; i <= current + 1; i++) pages.push(i)
    pages.push('...')
    pages.push(last)
  }
  return pages
})

const loadEarthData = async (page = 1) => {
  try {
    loading.value = true
    const resp = await earthDataAPI.list({
      page,
      search: searchQuery.value,
      issue_date_from: issueDateFrom.value,
      issue_date_to: issueDateTo.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
      per_page: 15
    })

    if (resp.status) {
      rows.value = Array.isArray(resp.data?.data) ? resp.data.data : []
      pagination.value = {
        current_page: resp.data.current_page,
        last_page: resp.data.last_page,
        per_page: resp.data.per_page,
        total: resp.data.total,
        from: resp.data.from,
        to: resp.data.to
      }
    } else {
      throw new Error(resp.message || '載入土單資料失敗')
    }
  } catch (error) {
    console.error('載入土單資料錯誤:', error)
    showToast(error.message || '載入土單資料失敗', 'error')
  } finally {
    loading.value = false
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadEarthData(page)
  }
}

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  // ensure options are ready
  if (cleanerOptions.value.length === 0) loadCleanerOptions()
  if (customerOptions.value.length === 0) loadCustomerOptions()
  showModal.value = true
}

const openEditModal = (item) => {
  isEditing.value = true
  form.id = item.id
  form.batch_no = item.batch_no || ''
  // 處理多個清運業者
  if (item.cleaners && Array.isArray(item.cleaners) && item.cleaners.length > 0) {
    form.cleaner_ids = item.cleaners.map(c => c.id)
  } else {
    form.cleaner_ids = []
  }
  form.project_name = item.project_name || ''
  form.flow_control_no = item.flow_control_no || ''
  form.issue_date = item.issue_date || ''
  form.issue_count = item.issue_count ?? 0
  form.customer_id = item.customer_id || 0
  form.valid_date_from = item.valid_date_from || ''
  form.valid_date_to = item.valid_date_to || ''
  form.carry_qty = item.carry_qty ?? 0
  form.carry_soil_type = item.carry_soil_type || ''
  form.status_desc = item.status_desc || ''
  form.remark_desc = item.remark_desc || ''
  form.sys_serial_no = item.sys_serial_no || ''
  form.status = item.status || 'active'
  // ensure options are ready
  if (cleanerOptions.value.length === 0) loadCleanerOptions()
  if (customerOptions.value.length === 0) loadCustomerOptions()
  errors.value = {}
  showModal.value = true
}

const openDeleteModal = (item) => {
  itemToDelete.value = item
  showDeleteModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
  errors.value = {}
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  itemToDelete.value = null
}

const openAdjustModal = async (item) => {
  adjustTarget.value = item
  adjustAction.value = 'add'
  adjustCount.value = 1
  adjustTotals.value = { total: 0, verified: 0, pending: 0 }
  showAdjustModal.value = true
  loadingAdjustTotals.value = true
  try {
    const resp = await earthDataAPI.usageStats(item.id)
    if (resp.status) {
      adjustTotals.value = resp.data?.totals || { total: 0, verified: 0, pending: 0 }
    }
  } catch (e) {
    // ignore; keep zeros
  } finally {
    loadingAdjustTotals.value = false
  }
}

const closeAdjustModal = () => {
  showAdjustModal.value = false
  adjustTarget.value = null
}

const submitAdjust = async () => {
  if (!adjustTarget.value) return
  try {
    submittingAdjust.value = true
    if (adjustAction.value === 'remove') {
      const pending = Number(adjustTotals.value?.pending || 0)
      const count = Number(adjustCount.value || 0)
      if (!Number.isFinite(count) || count <= 0) {
        showToast('請輸入正確的數量', 'error')
        return
      }
      if (count > pending) {
        showToast(`減少數量不可超過未印張數（${pending}）`, 'error')
        return
      }
    }
    const resp = await earthDataAPI.adjustDetails(adjustTarget.value.id, {
      action: adjustAction.value,
      count: adjustCount.value
    })
    if (resp.status) {
      // update current row issue_count
      const newCount = resp.data?.issue_count
      if (typeof newCount === 'number') {
        adjustTarget.value.issue_count = newCount
      } else {
        // fallback reload
        await loadEarthData(pagination.value?.current_page || 1)
      }
      // 若有 totals，可嘗試本地更新
      if (adjustAction.value === 'add') {
        adjustTotals.value.total = Number(adjustTotals.value.total || 0) + Number(adjustCount.value || 0)
        adjustTotals.value.pending = Number(adjustTotals.value.pending || 0) + Number(adjustCount.value || 0)
      } else if (adjustAction.value === 'remove') {
        adjustTotals.value.total = Math.max(0, Number(adjustTotals.value.total || 0) - Number(adjustCount.value || 0))
        adjustTotals.value.pending = Math.max(0, Number(adjustTotals.value.pending || 0) - Number(adjustCount.value || 0))
      }
      showToast('調整成功', 'success')
      closeAdjustModal()
    } else {
      throw new Error(resp.message || '調整失敗')
    }
  } catch (e) {
    showToast(e.message || '調整失敗', 'error')
  } finally {
    submittingAdjust.value = false
  }
}

// 開啟列印對話框：顯示統計與輸入本次列印數量
const openPrint = async (item) => {
  if (!item || !item.id) return
  printTarget.value = item
  printCount.value = 1
  printTotals.value = { total: 0, verified: 0, pending: 0 }
  showPrintModal.value = true
  loadingPrintTotals.value = true
  try {
    const resp = await earthDataAPI.usageStats(item.id)
    if (resp.status) {
      const totals = resp.data?.totals || {}
      const t = Number(totals.total ?? 0)
      const v = Number(totals.verified ?? 0)
      const p = Math.max(0, t - v)
      printTotals.value = { total: t, verified: v, pending: p }
      // clamp default
      if (printCount.value > p) printCount.value = p || 1
    } else {
      showToast(resp.message || '取得統計失敗', 'error')
    }
  } catch (e) {
    showToast(e.message || '取得統計失敗', 'error')
  } finally {
    loadingPrintTotals.value = false
  }
}

const closePrintModal = () => {
  showPrintModal.value = false
  printTarget.value = null
}

const submitPrint = () => {
  const pending = Number(printTotals.value.pending || 0)
  let count = Number(printCount.value || 0)
  if (!printTarget.value?.id) return
  if (!Number.isFinite(count) || count <= 0) {
    showToast('請輸入正確的列印數量', 'error');
    return
  }
  if (count > pending) {
    showToast(`本次列印數量不可超過未印數量（${pending}）`, 'error')
    return
  }
  const url = `/print/earth-data/${printTarget.value.id}/pending?count=${count}`
  window.open(url, '_blank')
  closePrintModal()
}

const resetForm = () => {
  form.id = null
  form.batch_no = ''
  form.issue_date = ''
  form.issue_count = 0
  form.customer_id = 0
  form.valid_date_from = ''
  form.valid_date_to = ''
  form.cleaner_ids = []
  form.project_name = ''
  form.flow_control_no = ''
  form.carry_qty = 0
  form.carry_soil_type = ''
  form.status_desc = ''
  form.remark_desc = ''
  form.sys_serial_no = ''
  form.status = 'active'
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    let resp
    if (isEditing.value) {
      resp = await earthDataAPI.update(form.id, form)
    } else {
      resp = await earthDataAPI.create(form)
    }

    if (resp.status) {
      showToast(isEditing.value ? '土單更新成功' : '土單建立成功', 'success')
      closeModal()
      loadEarthData()
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
    const resp = await earthDataAPI.delete(itemToDelete.value.id)
    if (resp.status) {
      showToast('土單刪除成功', 'success')
      closeDeleteModal()
      loadEarthData()
    } else {
      throw new Error(resp.message || '刪除失敗')
    }
  } catch (error) {
    console.error('刪除土單錯誤:', error)
    showToast(error.message || '刪除失敗', 'error')
  } finally {
    deleting.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-TW', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-TW', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

watch([sortBy, sortOrder], () => { loadEarthData() })

onMounted(() => { 
  loadEarthData()
  loadCleanerOptions()
  loadCustomerOptions()
})
</script>

<style>
/* Multiselect 自訂樣式 */
.multiselect {
  @apply border-gray-300 rounded-md min-h-[42px];
}

.multiselect:focus-within {
  @apply ring-2 ring-amber-500 border-transparent;
}

.multiselect.is-active {
  @apply border-amber-500;
}

.multiselect-tags {
  @apply bg-transparent min-h-[40px] py-1 px-2;
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

.multiselect-tag {
  @apply bg-amber-100 text-amber-700 border border-amber-300 rounded px-2 py-1 text-xs font-medium;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.multiselect-tag i {
  @apply text-amber-700 cursor-pointer;
  margin-left: 0.25rem;
}

.multiselect-tag i:hover {
  @apply text-amber-900;
}

.multiselect-placeholder {
  @apply text-gray-400;
}

.multiselect-single-label {
  @apply text-gray-900;
}

.multiselect-option {
  @apply text-gray-900;
}

.multiselect-option.is-selected {
  @apply bg-amber-50 text-amber-700;
}

.multiselect-option.is-pointed {
  @apply bg-amber-100;
}

.multiselect-option.is-selected.is-pointed {
  @apply bg-amber-200;
}
</style>
