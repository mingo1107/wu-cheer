<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 使用者管理內容 -->
    <div class="max-w-7xl mx-auto">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
          <!-- 頁面標題和操作按鈕 -->
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
            <div>
              <h2 class="text-2xl font-bold text-gray-800 mb-2">使用者管理</h2>
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
                      <span class="text-sm font-medium text-gray-500">使用者管理</span>
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
                新增使用者
              </button>
            </div>
          </div>

          <!-- 搜尋和篩選 -->
          <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
              <div class="flex-1">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="搜尋使用者姓名或電子郵件..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                  @input="debouncedSearch"
                >
              </div>
              <button
                @click="loadUsers"
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

          <!-- 使用者列表 -->
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
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
              <p class="text-gray-600 text-lg">沒有找到使用者</p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">使用者</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">電子郵件</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">角色</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">狀態</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">建立時間</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in dataList" :key="user?.id || Math.random()" class="hover:bg-gray-50" v-if="dataList">
                    <td class="td-base">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <div class="h-10 w-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">{{ (user.name || 'U').charAt(0).toUpperCase() }}</span>
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ user.name || '未知使用者' }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="td-base">
                      <div class="text-sm text-gray-900">{{ user.email || '未知郵件' }}</div>
                    </td>
                    <td class="td-base">
                      <span v-if="user.role === 0" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                        管理員
                      </span>
                      <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        一般使用者
                      </span>
                    </td>
                    <td class="td-base">
                      <span v-if="user.email_verified_at" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        已驗證
                      </span>
                      <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        未驗證
                      </span>
                    </td>
                    <td class="td-base text-gray-500">
                      {{ formatDate(user.created_at) }}
                    </td>
                    <td class="td-base font-medium">
                      <div class="action-buttons">
                        <button
                          @click="openEditModal(user)"
                          class="action-btn action-btn--edit"
                          :disabled="!user?.id"
                          title="編輯使用者"
                        >
                          <i class="fas fa-edit action-icon"></i>
                        </button>
                        <button
                          @click="confirmDelete(user)"
                          class="action-btn action-btn--delete"
                          :disabled="!user?.id"
                          title="刪除使用者"
                        >
                          <i class="fas fa-trash action-icon"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 分頁區塊 -->
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
                  顯示第 <span class="font-medium">{{ pagination.from || 0 }}</span> 到
                  <span class="font-medium">{{ pagination.to || 0 }}</span> 筆，共
                  <span class="font-medium">{{ pagination.total }}</span> 筆資料
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
                    <span v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
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

    <!-- 新增/編輯使用者 Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <!-- Modal 標題 -->
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ isEditing ? '編輯使用者' : '新增使用者' }}
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
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">姓名</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                placeholder="請輸入姓名"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">電子郵件</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                placeholder="請輸入電子郵件"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                密碼
                <span v-if="isEditing" class="text-sm text-gray-500">(留空表示不修改)</span>
              </label>
              <input
                v-model="form.password"
                type="password"
                :required="!isEditing"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                :placeholder="isEditing ? '留空表示不修改密碼' : '請輸入密碼'"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">確認密碼</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                :required="!isEditing"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                :placeholder="isEditing ? '留空表示不修改密碼' : '請再次輸入密碼'"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">角色</label>
              <select
                v-model="form.role"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              >
                <option :value="1">一般使用者</option>
                <option :value="0">管理員</option>
              </select>
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

    <!-- 刪除確認 Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">確認刪除</h3>
          <p class="text-sm text-gray-600 text-center mb-6">
            您確定要刪除使用者「{{ userToDelete?.name }}」嗎？此操作無法復原。
          </p>
          <div class="flex justify-center space-x-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200"
            >
              取消
            </button>
            <button
              @click="deleteUser"
              :disabled="isSubmitting"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <span v-if="!isSubmitting">刪除</span>
              <span v-else class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                刪除中...
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import userAPI from '../api/user.js';
import { useToast } from '../composables/useToast.js';
import { usePagination } from '@/composables/usePagination'

const router = useRouter();
const { success, error: showError } = useToast();

// 響應式資料
const dataList = ref([]);
const isLoading = ref(false);
const isSubmitting = ref(false);
const error = ref(null);
const searchQuery = ref('');
const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const userToDelete = ref(null);
const pagination = ref(null);

// 確保 dataList 是陣列
if (!Array.isArray(dataList.value)) {
  dataList.value = [];
}

// 表單資料（由後端 show/0 取得預設值）
const form = ref({ id: null, name: '', email: '', password: '', password_confirmation: '', role: 1 });

const loadUserDefaults = async () => {
  try {
    const resp = await userAPI.getUser(0);
    if (resp.status && resp.data) {
      form.value = { id: null, ...resp.data, password: '', password_confirmation: '' };
    }
  } catch (e) {
    // ignore, keep local defaults
  }
};

// 搜尋防抖
let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadUsers();
  }, 500);
};

// 使用共用的分頁邏輯
const { visiblePages, goToPage } = usePagination(pagination, loadUsers)

// 修改 loadUsers 以支援分頁
const loadUsers = async (page = 1) => {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await userAPI.getUsers({
      search: searchQuery.value,
      page,
      per_page: 15
    });

    if (response.status) {
      if (response.data.users && response.data.users.data) {
        dataList.value = response.data.users.data;
        // 設定分頁資訊
        pagination.value = {
          current_page: response.data.users.current_page,
          last_page: response.data.users.last_page,
          per_page: response.data.users.per_page,
          total: response.data.users.total,
          from: response.data.users.from,
          to: response.data.users.to
        };
      } else if (Array.isArray(response.data.data)) {
        dataList.value = response.data.data;
        // 設定分頁資訊
        pagination.value = {
          current_page: response.data.current_page || 1,
          last_page: response.data.last_page || 1,
          per_page: response.data.per_page || 15,
          total: response.data.total || 0,
          from: response.data.from || 0,
          to: response.data.to || 0
        };
      } else {
        dataList.value = [];
        pagination.value = null;
      }
    } else {
      error.value = response.message || '載入使用者列表失敗';
    }
  } catch (e) {
    console.error('載入使用者列表錯誤:', e);
    error.value = e.message || '載入使用者列表失敗';
    dataList.value = [];
    pagination.value = null;
  } finally {
    isLoading.value = false;
  }
};

const openCreateModal = async () => {
  isEditing.value = false;
  await loadUserDefaults();
  error.value = null;
  showModal.value = true;
};

const openEditModal = (user) => {
  if (!user || !user.id) {
    error.value = '無效的使用者資料';
    return;
  }

  isEditing.value = true;
  form.value = {
    id: user.id,
    name: user.name || '',
    email: user.email || '',
    password: '',
    password_confirmation: '',
    role: user.role !== undefined ? user.role : 1
  };
  error.value = null;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  error.value = null;
};

const submitForm = async () => {
  isSubmitting.value = true;
  error.value = null;

  try {
    let response;
    if (isEditing.value) {
      if (!form.value.id) {
        error.value = '無效的使用者 ID';
        return;
      }
      response = await userAPI.updateUser(form.value.id, form.value);
    } else {
      response = await userAPI.createUser(form.value);
    }

    if (response.status) {
      showModal.value = false;
      await loadUsers();
      // 顯示成功 toast
      if (isEditing.value) {
        success('使用者更新成功！');
      } else {
        success('使用者新增成功！');
      }
    } else {
      error.value = response.message;
      showError(response.message || '操作失敗');
    }
  } catch (e) {
    error.value = e.message || '操作失敗';
    showError(e.message || '操作失敗');
  } finally {
    isSubmitting.value = false;
  }
};

const confirmDelete = (user) => {
  if (!user || !user.id) {
    error.value = '無效的使用者資料';
    return;
  }

  userToDelete.value = user;
  showDeleteModal.value = true;
};

const deleteUser = async () => {
  if (!userToDelete.value || !userToDelete.value.id) {
    error.value = '無效的使用者資料';
    return;
  }

  isSubmitting.value = true;
  error.value = null;

  try {
    const response = await userAPI.deleteUser(userToDelete.value.id);
    if (response.status) {
      showDeleteModal.value = false;
      await loadUsers();
      success('使用者刪除成功！');
    } else {
      error.value = response.message;
      showError(response.message || '刪除失敗');
    }
  } catch (e) {
    error.value = e.message || '刪除失敗';
    showError(e.message || '刪除失敗');
  } finally {
    isSubmitting.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '未知';
  return new Date(dateString).toLocaleDateString('zh-TW');
};

// 生命週期
onMounted(() => {
  loadUserDefaults();
  loadUsers();
});
</script>
