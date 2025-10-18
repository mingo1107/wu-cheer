<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 土單資料管理內容 -->
    <div class="max-w-7xl mx-auto">
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
        <!-- 頁面標題和操作按鈕 -->
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
            <button
              @click="openCreateModal"
              class="bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
              </svg>
              新增土單
            </button>
            <button
              @click="exportToExcel"
              class="bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
              匯出 Excel
            </button>
            <router-link
              to="/dashboard"
              class="bg-gradient-to-r from-gray-500 to-gray-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
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
              <input
                v-model="searchQuery"
                type="text"
                placeholder="搜尋（批號、工程名稱、客戶代號、土方清運業者、狀態說明）"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                @input="debouncedSearch"
              >
            </div>
            <button
              @click="loadEarthData"
              :disabled="isLoading"
              class="bg-gradient-to-r from-amber-500 to-orange-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-amber-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
              </svg>
              搜尋
            </button>
          </div>
        </div>

        <!-- Excel 編輯器 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div v-if="isLoading" class="p-8 text-center">
            <svg class="animate-spin h-8 w-8 text-amber-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-600">載入中...</p>
          </div>

          <div v-else-if="dataList.length === 0" class="p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"/>
            </svg>
            <p class="text-gray-600 text-lg">沒有找到土單資料</p>
          </div>

          <div v-else class="p-4">
            <HotTable
              :data="dataList"
              :columns="hotColumns"
              :colHeaders="colHeaders"
              :rowHeaders="true"
              :contextMenu="true"
              :manualColumnResize="true"
              :manualRowResize="true"
              :filters="true"
              :dropdownMenu="true"
              :copyPaste="true"
              :undoRedo="true"
              :height="500"
              :width="'100%'"
              :licenseKey="'non-commercial-and-evaluation'"
              @after-change="onAfterChange"
              @after-create-row="onAfterCreateRow"
              @after-remove-row="onAfterRemoveRow"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- 新增土單 Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <!-- Modal 標題 -->
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ isEditing ? '編輯土單' : '新增土單' }}
            </h3>
            <button
              @click="closeModal"
              class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Modal 表單 -->
          <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">批號</label>
                <input v-model="form.batch_no" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="請輸入批號" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">文件序號明細</label>
                <input v-model="form.doc_seq_detail" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="請輸入文件序號明細" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">開立日期</label>
                <input v-model="form.issue_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">開立張數</label>
                <input v-model.number="form.issue_count" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="0" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">客戶代號</label>
                <input v-model="form.customer_code" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="請輸入客戶代號" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">有效期限（起）</label>
                <input v-model="form.valid_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">有效期限（迄）</label>
                <input v-model="form.valid_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">土方清運業者</label>
                <input v-model="form.cleaner_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="請輸入清運業者名稱" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">工程名稱</label>
                <input v-model="form.project_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="請輸入工程名稱" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">工程流向管制編號</label>
                <input v-model="form.flow_control_no" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="請輸入管制編號" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">載運數量</label>
                <input v-model.number="form.carry_qty" type="number" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="0" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">載運土質</label>
                <input v-model="form.carry_soil_type" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="例如：一般土方" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">狀態說明</label>
                <input v-model="form.status_desc" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="請輸入狀態說明" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">備註說明</label>
                <textarea v-model="form.remark_desc" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" placeholder="請輸入備註說明"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">建檔人員</label>
                <input v-model="form.created_by" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">建檔日期</label>
                <input v-model="form.created_at" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">修改人員</label>
                <input v-model="form.updated_by" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">修改日期</label>
                <input v-model="form.updated_at" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">系統流水號</label>
                <input v-model="form.sys_serial_no" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">狀態</label>
                <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                  <option value="">請選擇</option>
                  <option value="active">有效</option>
                  <option value="inactive">無效</option>
                </select>
              </div>
            </div>

            <!-- 錯誤訊息 -->
            <div v-if="error" class="p-4 bg-red-50 border-l-4 border-red-400 rounded">
              <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                  <p class="text-sm text-red-700 font-medium">操作失敗</p>
                  <p class="text-sm text-red-600">{{ error }}</p>
                </div>
              </div>
            </div>

            <!-- Modal 按鈕 -->
            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200"
              >
                取消
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
              >
                <span v-if="!isSubmitting">
                  {{ isEditing ? '更新' : '建立' }}
                </span>
                <span v-else class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  處理中...
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from '../composables/useToast.js';
import { HotTable } from '@handsontable/vue3';
import { registerAllModules } from 'handsontable/registry';
import 'handsontable/dist/handsontable.full.min.css';

// 註冊所有 Handsontable 模組
registerAllModules();

const router = useRouter();
const { success, error: showError } = useToast();

// 響應式資料
const hotColumns = ref([
  { data: 'batch_no', title: '批號', type: 'text', width: 100 },
  { data: 'doc_seq_detail', title: '文件序號明細', type: 'text', width: 150 },
  { data: 'issue_date', title: '開立日期', type: 'date', width: 120 },
  { data: 'issue_count', title: '開立張數', type: 'numeric', width: 100 },
  { data: 'customer_code', title: '客戶代號', type: 'text', width: 100 },
  { data: 'valid_from', title: '有效期限（起）', type: 'date', width: 120 },
  { data: 'valid_to', title: '有效期限（迄）', type: 'date', width: 120 },
  { data: 'cleaner_name', title: '土方清運業者', type: 'text', width: 150 },
  { data: 'project_name', title: '工程名稱', type: 'text', width: 150 },
  { data: 'flow_control_no', title: '工程流向管制編號', type: 'text', width: 150 },
  { data: 'carry_qty', title: '載運數量', type: 'numeric', width: 100 },
  { data: 'carry_soil_type', title: '載運土質', type: 'text', width: 100 },
  { data: 'status_desc', title: '狀態說明', type: 'text', width: 150 },
  { data: 'remark_desc', title: '備註說明', type: 'text', width: 150 },
  { data: 'created_by', title: '建檔人員', type: 'text', width: 100 },
  { data: 'created_at', title: '建檔日期', type: 'date', width: 120 },
  { data: 'updated_by', title: '修改人員', type: 'text', width: 100 },
  { data: 'updated_at', title: '修改日期', type: 'date', width: 120 },
  { data: 'sys_serial_no', title: '系統流水號', type: 'text', width: 150 },
  { data: 'status', title: '狀態', type: 'text', width: 100 }
]);
const colHeaders = hotColumns.value.map(c => c.title);
const isLoading = ref(false);
const isSubmitting = ref(false);
const error = ref(null);
const searchQuery = ref('');
const showModal = ref(false);

// Grid data and edit state
const dataList = ref([]);
const isEditing = ref(false);
const editingItem = ref(null);
const form = ref({
  batch_no: '',
  doc_seq_detail: '',
  issue_date: '',
  issue_count: 0,
  customer_code: '',
  valid_from: '',
  valid_to: '',
  cleaner_name: '',
  project_name: '',
  flow_control_no: '',
  carry_qty: 0,
  carry_soil_type: '',
  status_desc: '',
  remark_desc: '',
  created_by: '',
  created_at: '',
  updated_by: '',
  updated_at: '',
  sys_serial_no: '',
  status: ''
});

// Debounced search
let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadEarthData();
  }, 500);
};

// Load mock data (replace with API later)
const loadEarthData = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    await new Promise(r => setTimeout(r, 300));
    dataList.value = [
      {
        id: 1,
        batch_no: 'B-2025-001',
        doc_seq_detail: '001-010',
        issue_date: '2025-10-01',
        issue_count: 10,
        customer_code: 'CUST-001',
        valid_from: '2025-10-01',
        valid_to: '2025-12-31',
        cleaner_name: '伍齊清運',
        project_name: '台北市信義區建案A',
        flow_control_no: 'FC-0001',
        carry_qty: 150,
        carry_soil_type: '一般土方',
        status_desc: '已開立',
        remark_desc: '第一批',
        created_by: 'admin',
        created_at: '2025-10-01',
        updated_by: 'admin',
        updated_at: '2025-10-02',
        sys_serial_no: 'SYS-10001',
        status: 'active'
      }
    ];
  } catch (e) {
    error.value = e.message || '載入失敗';
    dataList.value = [];
  } finally {
    isLoading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  editingItem.value = null;
  form.value = {
    batch_no: '',
    doc_seq_detail: '',
    issue_date: '',
    issue_count: 0,
    customer_code: '',
    valid_from: '',
    valid_to: '',
    cleaner_name: '',
    project_name: '',
    flow_control_no: '',
    carry_qty: 0,
    carry_soil_type: '',
    status_desc: '',
    remark_desc: '',
    created_by: '',
    created_at: '',
    updated_by: '',
    updated_at: '',
    sys_serial_no: '',
    status: ''
  };
  error.value = null;
  showModal.value = true;
};

const editItem = (item) => {
  if (!item || !item.id) {
    error.value = '無效的土單資料';
    return;
  }
  
  isEditing.value = true;
  editingItem.value = item;
  form.value = {
    batch_no: item.batch_no || '',
    doc_seq_detail: item.doc_seq_detail || '',
    issue_date: item.issue_date || '',
    issue_count: item.issue_count ?? 0,
    customer_code: item.customer_code || '',
    valid_from: item.valid_from || '',
    valid_to: item.valid_to || '',
    cleaner_name: item.cleaner_name || '',
    project_name: item.project_name || '',
    flow_control_no: item.flow_control_no || '',
    carry_qty: item.carry_qty ?? 0,
    carry_soil_type: item.carry_soil_type || '',
    status_desc: item.status_desc || '',
    remark_desc: item.remark_desc || '',
    created_by: item.created_by || '',
    created_at: item.created_at || '',
    updated_by: item.updated_by || '',
    updated_at: item.updated_at || '',
    sys_serial_no: item.sys_serial_no || '',
    status: item.status || ''
  };
  error.value = null;
  showModal.value = true;
};

const deleteItem = (item) => {
  if (!item || !item.id) {
    error.value = '無效的土單資料';
    return;
  }
  
  if (confirm(`確定要刪除土單「${item.batch_no}」嗎？`)) {
    const index = dataList.value.findIndex(i => i.id === item.id);
    if (index > -1) {
      dataList.value.splice(index, 1);
      success('土單刪除成功！');
    }
  }
};

const closeModal = () => {
  showModal.value = false;
  error.value = null;
};

const submitForm = async () => {
  isSubmitting.value = true;
  error.value = null;
  
  try {
    // 模擬 API 呼叫
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    if (isEditing.value && editingItem.value) {
      // 編輯模式
      const index = dataList.value.findIndex(i => i.id === editingItem.value.id);
      if (index > -1) {
        dataList.value[index] = {
          ...dataList.value[index],
          ...form.value,
          earth_quantity: Number(form.value.earth_quantity) || 0
        };
        success('土單更新成功！');
      }
    } else {
      // 新增模式
      const newItem = {
        id: Date.now(),
        ...form.value,
        earth_quantity: Number(form.value.earth_quantity) || 0,
        status: '已開立',
        created_date: new Date().toISOString().split('T')[0]
      };
      
      dataList.value.unshift(newItem);
      success('土單新增成功！');
    }
    
    showModal.value = false;
  } catch (e) {
    error.value = e.message || '操作失敗';
    showError(e.message || '操作失敗');
  } finally {
    isSubmitting.value = false;
  }
};

const exportToExcel = () => {
  // 模擬匯出功能
  success('Excel 匯出功能開發中...');
};

// Handsontable 事件處理
const onAfterChange = (changes, source) => {
  if (source !== 'loadData') {
    console.log('資料變更:', changes);
    // 這裡可以加入自動儲存邏輯
  }
};

const onAfterCreateRow = (index, amount) => {
  console.log('新增行:', index, amount);
  // 新增空行到資料列表
  for (let i = 0; i < amount; i++) {
    const newRow = {
      id: Date.now() + Math.random(),
      batch_no: '',
      doc_seq_detail: '',
      issue_date: '',
      issue_count: 0,
      customer_code: '',
      valid_from: '',
      valid_to: '',
      cleaner_name: '',
      project_name: '',
      flow_control_no: '',
      carry_qty: 0,
      carry_soil_type: '',
      status_desc: '',
      remark_desc: '',
      created_by: '',
      created_at: new Date().toISOString().split('T')[0],
      updated_by: '',
      updated_at: '',
      sys_serial_no: '',
      status: ''
    };
    dataList.value.splice(index + i, 0, newRow);
  }
};

const onAfterRemoveRow = (index, amount) => {
  console.log('刪除行:', index, amount);
  // 從資料列表中移除行
  dataList.value.splice(index, amount);
};

// 生命週期
onMounted(() => {
  loadEarthData();
});
</script>
