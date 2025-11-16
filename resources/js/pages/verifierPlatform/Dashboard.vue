<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 pb-20">
    <!-- 網路狀態提示 -->
    <div
      v-if="!isOnline"
      class="fixed top-0 left-0 right-0 bg-amber-500 text-white px-4 py-3 text-center z-50 shadow-md"
    >
      <i class="fas fa-wifi-slash mr-2"></i>
      <span class="text-sm sm:text-base">離線模式：資料將暫存在本地</span>
    </div>

    <!-- 頂部導航 -->
    <div class="bg-white shadow-sm sticky top-0 z-40">
      <div class="container mx-auto px-4 py-3 sm:py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <h1 class="text-lg sm:text-xl font-bold text-gray-800">核銷作業</h1>
          </div>
          <div class="flex items-center gap-3">
            <!-- 登入者資訊 -->
            <div v-if="verifierName" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700">
              <i class="fas fa-user-circle text-gray-500"></i>
              <span class="font-medium">{{ verifierName }}</span>
            </div>
            <!-- 離線記錄數量提示 -->
            <button
              v-if="pendingCount > 0"
              @click="showOfflineRecords = true"
              class="relative px-3 py-2 bg-amber-100 text-amber-700 rounded-lg text-sm font-medium hover:bg-amber-200 transition-colors"
            >
              <i class="fas fa-database mr-1"></i>
              待同步 {{ pendingCount }}
            </button>
            <!-- 登出按鈕 -->
            <button
              @click="handleLogout"
              class="px-3 py-2 text-gray-600 hover:text-gray-800 transition-colors"
              title="登出"
            >
              <i class="fas fa-sign-out-alt text-lg"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 py-4 sm:py-6">
      <!-- 核銷輸入區 -->
      <div class="max-w-2xl mx-auto mb-6">
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6">
          <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">輸入 Barcode</h2>
          
          <form @submit.prevent="handleVerify" class="space-y-4">
            <!-- Barcode 輸入 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-barcode mr-2"></i>
                Barcode
              </label>
              <div class="flex gap-2">
                <input
                  v-model="barcodeInput"
                  type="text"
                  required
                  placeholder="請輸入或掃描 barcode"
                  autocomplete="off"
                  inputmode="text"
                  class="flex-1 px-4 py-3.5 sm:py-3 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :disabled="isVerifying"
                  ref="barcodeInputRef"
                >
                <button
                  type="button"
                  @click="startScan"
                  class="px-4 py-3.5 sm:py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors touch-manipulation"
                  title="掃描條碼"
                >
                  <i class="fas fa-camera text-lg"></i>
                </button>
              </div>
            </div>

            <!-- 提交按鈕 -->
            <button
              type="submit"
              :disabled="isVerifying || !barcodeInput"
              class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-4 sm:py-3 px-6 rounded-lg font-semibold text-base sm:text-sm hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95 touch-manipulation"
            >
              <span v-if="!isVerifying" class="flex items-center justify-center">
                <i class="fas fa-check-circle mr-2"></i>
                核銷
              </span>
              <span v-else class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                處理中...
              </span>
            </button>
          </form>
        </div>
      </div>

      <!-- 統計資訊 -->
      <div class="max-w-2xl mx-auto mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
          <div class="bg-white rounded-lg shadow p-3 sm:p-4 text-center">
            <div class="text-2xl sm:text-3xl font-bold text-blue-600">{{ stats.today || 0 }}</div>
            <div class="text-xs sm:text-sm text-gray-600 mt-1">今日核銷</div>
          </div>
          <div class="bg-white rounded-lg shadow p-3 sm:p-4 text-center">
            <div class="text-2xl sm:text-3xl font-bold text-green-600">{{ stats.total || 0 }}</div>
            <div class="text-xs sm:text-sm text-gray-600 mt-1">總計</div>
          </div>
          <div class="bg-white rounded-lg shadow p-3 sm:p-4 text-center">
            <div class="text-2xl sm:text-3xl font-bold text-amber-600">{{ pendingCount }}</div>
            <div class="text-xs sm:text-sm text-gray-600 mt-1">待同步</div>
          </div>
          <div class="bg-white rounded-lg shadow p-3 sm:p-4 text-center">
            <div class="text-2xl sm:text-3xl font-bold" :class="isOnline ? 'text-green-600' : 'text-red-600'">
              <i :class="isOnline ? 'fas fa-wifi' : 'fas fa-wifi-slash'"></i>
            </div>
            <div class="text-xs sm:text-sm text-gray-600 mt-1">{{ isOnline ? '在線' : '離線' }}</div>
          </div>
        </div>
      </div>

      <!-- 最近核銷記錄 -->
      <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-gray-800">最近核銷記錄</h2>
            <button
              @click="refreshRecords"
              class="px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
            >
              <i class="fas fa-sync-alt mr-1" :class="{ 'animate-spin': isRefreshing }"></i>
              重整
            </button>
          </div>
          
          <div v-if="recentRecords.length === 0" class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-4xl mb-2 opacity-50"></i>
            <p>尚無核銷記錄</p>
          </div>
          
          <div v-else class="space-y-2 max-h-96 overflow-y-auto">
            <div
              v-for="record in recentRecords"
              :key="record.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-800 truncate">{{ record.barcode }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ formatTime(record.created_at) }}</div>
              </div>
              <div class="ml-3">
                <span
                  class="px-2 py-1 text-xs rounded-full"
                  :class="{
                    'bg-green-100 text-green-700': record.status === 'synced',
                    'bg-amber-100 text-amber-700': record.status === 'pending',
                    'bg-red-100 text-red-700': record.status === 'failed'
                  }"
                >
                  {{ getStatusText(record.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 離線記錄 Modal -->
    <div
      v-if="showOfflineRecords"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center p-4"
      @click.self="showOfflineRecords = false"
    >
      <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full max-w-md max-h-[80vh] flex flex-col">
        <div class="p-4 border-b flex items-center justify-between">
          <h3 class="text-lg font-bold text-gray-800">待同步記錄</h3>
          <button
            @click="showOfflineRecords = false"
            class="p-2 text-gray-400 hover:text-gray-600"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4">
          <!-- 離線記錄列表 -->
          <div v-if="offlineRecords.length === 0" class="text-center py-8 text-gray-500">
            <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
            <p>所有記錄已同步</p>
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="record in offlineRecords"
              :key="record.id"
              class="p-3 bg-gray-50 rounded-lg"
            >
              <div class="font-medium text-gray-800">{{ record.barcode }}</div>
              <div class="text-xs text-gray-500 mt-1">{{ formatTime(record.created_at) }}</div>
            </div>
          </div>
        </div>
        <div class="p-4 border-t">
          <button
            @click="syncOfflineRecords"
            :disabled="isSyncing || !isOnline"
            class="w-full bg-blue-500 text-white py-3 rounded-lg font-semibold hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors touch-manipulation"
          >
            <span v-if="!isSyncing">同步記錄</span>
            <span v-else>同步中...</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 核銷確認 Dialog -->
    <div
      v-if="showVerifyDialog"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
      @click.self="cancelVerify"
    >
      <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
          <!-- 標題 -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg sm:text-xl font-bold text-gray-800">確認核銷資訊</h3>
            <button
              @click="cancelVerify"
              class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>

          <!-- Barcode 顯示 -->
          <div class="mb-4">
            <label class="label-base">Barcode</label>
            <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm font-mono">
              {{ verifyBarcode }}
            </div>
          </div>

          <!-- 車號選擇 -->
          <div class="mb-4">
            <label class="label-base">
              車號 <span class="text-red-500">*</span>
            </label>
            <select
              v-model="selectedVehicleId"
              required
              class="w-full px-3 py-2.5 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">請選擇車號</option>
              <option
                v-for="vehicle in verifyVehicles"
                :key="vehicle.id"
                :value="vehicle.id"
              >
                {{ vehicle.front_plate }}{{ vehicle.rear_plate ? ' / ' + vehicle.rear_plate : '' }} ({{ vehicle.cleaner_name }})
              </option>
            </select>
          </div>

          <!-- 司機名字輸入 -->
          <div class="mb-6">
            <label class="label-base">
              司機名字 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="driverName"
              type="text"
              required
              placeholder="請輸入司機名字"
              autocomplete="off"
              class="w-full px-3 py-2.5 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- 按鈕 -->
          <div class="flex gap-3">
            <button
              @click="cancelVerify"
              class="flex-1 px-4 py-2.5 sm:py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors"
            >
              取消
            </button>
            <button
              @click="confirmVerify"
              :disabled="isVerifying || !selectedVehicleId || !driverName.trim()"
              class="flex-1 px-4 py-2.5 sm:py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-semibold hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95 touch-manipulation"
            >
              <span v-if="!isVerifying" class="flex items-center justify-center">
                <i class="fas fa-check-circle mr-2"></i>
                確認核銷
              </span>
              <span v-else class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                核銷中...
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast 通知 -->
    <Toast />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useVerifierAuth } from '../../composables/verifierPlatform/useAuth.js';
import { useNetworkStatus } from '../../composables/useNetworkStatus.js';
import { useToast } from '../../composables/useToast.js';
import {
  saveOfflineRecord,
  getPendingRecords,
  updateRecordStatus,
  getRecordStats
} from '../../utils/offlineStorageSimple.js';
import verifierVerifyAPI from '../../api/verifierPlatform/verify.js';
import Toast from '../../components/Toast.vue';
import { formatDateTimeShort } from '../../utils/date.js';

const router = useRouter();
const { logout, verifier, verifierName } = useVerifierAuth();
const { isOnline, wasOffline } = useNetworkStatus();
const { success: showSuccessToast, error: showErrorToast } = useToast();

// 表單資料
const barcodeInput = ref('');
const barcodeInputRef = ref(null);
const isVerifying = ref(false);
const isRefreshing = ref(false);
const isSyncing = ref(false);
const showOfflineRecords = ref(false);

// 核銷確認 Dialog
const showVerifyDialog = ref(false);
const verifyBarcode = ref('');
const verifyVehicles = ref([]);
const selectedVehicleId = ref(null);
const driverName = ref('');
const isPreChecking = ref(false);

// 記錄資料
const recentRecords = ref([]);
const offlineRecords = ref([]);
const stats = ref({
  today: 0,
  total: 0
});

// 計算屬性
const pendingCount = computed(() => {
  return offlineRecords.value.length;
});

// 核銷處理（先預檢查，再顯示確認 Dialog）
const handleVerify = async () => {
  if (!barcodeInput.value.trim()) {
    return;
  }

  const barcode = barcodeInput.value.trim();
  isPreChecking.value = true;

  try {
    if (isOnline.value) {
      // 在線模式：先預檢查 barcode
      const preCheckResult = await verifierVerifyAPI.preCheck(barcode);
      
      if (preCheckResult.status && preCheckResult.data) {
        // 預檢查成功，顯示確認 Dialog
        verifyBarcode.value = barcode;
        verifyVehicles.value = preCheckResult.data.vehicles || [];
        selectedVehicleId.value = null;
        driverName.value = '';
        showVerifyDialog.value = true;
      } else {
        // 預檢查失敗，顯示錯誤訊息
        showErrorToast(preCheckResult.message || 'Barcode 驗證失敗');
      }
    } else {
      // 離線模式：儲存到本地（離線模式暫時不支援車號和司機名字）
      const id = saveOfflineRecord({
        barcode,
        verifier_id: verifier.value?.id || null,
        verifier_name: verifier.value?.name || verifier.value?.account || '核銷人員'
      });
      
      showSuccessToast('已儲存到離線記錄，待網路恢復後自動同步');
      addRecentRecord({
        id,
        barcode,
        status: 'pending',
        created_at: new Date().toISOString()
      });
      loadOfflineRecords();
      
      // 清空輸入框並聚焦
      barcodeInput.value = '';
      setTimeout(() => {
        barcodeInputRef.value?.focus();
      }, 100);
    }
  } catch (error) {
    showErrorToast(error.message || '預檢查失敗');
  } finally {
    isPreChecking.value = false;
  }
};

// 確認核銷（從 Dialog 確認後執行）
const confirmVerify = async () => {
  if (!verifyBarcode.value) {
    return;
  }

  // 驗證必填欄位
  if (!selectedVehicleId.value) {
    showErrorToast('請選擇車號');
    return;
  }

  if (!driverName.value.trim()) {
    showErrorToast('請輸入司機名字');
    return;
  }

  isVerifying.value = true;

  try {
    // 調用核銷 API，傳遞 barcode、vehicle_id 和 driver_name
    const result = await verifierVerifyAPI.verify(
      verifyBarcode.value,
      selectedVehicleId.value,
      driverName.value.trim()
    );
    
    if (result.status && result.data) {
      showSuccessToast('核銷成功');
      addRecentRecord({
        barcode: verifyBarcode.value,
        status: 'synced',
        created_at: new Date().toISOString()
      });
      // 更新統計
      await loadStats();
      
      // 關閉 Dialog
      showVerifyDialog.value = false;
      verifyBarcode.value = '';
      verifyVehicles.value = [];
      selectedVehicleId.value = null;
      driverName.value = '';
      
      // 清空輸入框並聚焦
      barcodeInput.value = '';
      setTimeout(() => {
        barcodeInputRef.value?.focus();
      }, 100);
    } else {
      showErrorToast(result.message || '核銷失敗');
    }
  } catch (error) {
    showErrorToast(error.message || '核銷失敗');
  } finally {
    isVerifying.value = false;
  }
};

// 取消核銷 Dialog
const cancelVerify = () => {
  showVerifyDialog.value = false;
  verifyBarcode.value = '';
  verifyVehicles.value = [];
  selectedVehicleId.value = null;
  driverName.value = '';
  
  // 重新聚焦輸入框
  setTimeout(() => {
    barcodeInputRef.value?.focus();
  }, 100);
};

// 掃描條碼
const startScan = () => {
  // TODO: 實作條碼掃描功能
  showErrorToast('條碼掃描功能開發中');
};

// 載入離線記錄
const loadOfflineRecords = () => {
  offlineRecords.value = getPendingRecords();
};

// 同步離線記錄
const syncOfflineRecords = async () => {
  if (!isOnline.value) {
    showErrorToast('網路未連線，無法同步');
    return;
  }

  isSyncing.value = true;
  try {
    const pending = getPendingRecords();
    
    if (pending.length === 0) {
      showSuccessToast('沒有待同步的記錄');
      isSyncing.value = false;
      return;
    }

    // 提取所有 barcode
    const barcodes = pending.map(record => record.barcode);
    
    // 批量核銷
    const result = await verifierVerifyAPI.batchVerify(barcodes);
    
    if (result.status && result.data) {
      const { success, failed, errors } = result.data;
      
      // 更新成功記錄的狀態
      if (success > 0) {
        // 找出成功的 barcode（排除錯誤列表中的）
        const errorBarcodes = errors.map(e => e.barcode);
        const successRecords = pending.filter(r => !errorBarcodes.includes(r.barcode));
        
        successRecords.forEach(record => {
          updateRecordStatus(record.id, 'synced');
        });
      }
      
      // 更新失敗記錄的狀態
      if (failed > 0 && errors.length > 0) {
        errors.forEach(error => {
          const record = pending.find(r => r.barcode === error.barcode);
          if (record) {
            updateRecordStatus(record.id, 'failed');
          }
        });
      }
      
      if (failed === 0) {
        showSuccessToast(`同步完成：成功 ${success} 筆`);
      } else {
        showErrorToast(`同步完成：成功 ${success} 筆，失敗 ${failed} 筆`);
      }
      
      loadOfflineRecords();
      await loadStats();
      refreshRecords();
    } else {
      showErrorToast(result.message || '同步失敗');
    }
  } catch (error) {
    showErrorToast(error.message || '同步失敗');
  } finally {
    isSyncing.value = false;
  }
};

// 載入統計資訊
const loadStats = async () => {
  try {
    if (isOnline.value) {
      const result = await verifierVerifyAPI.getStats();
      if (result.status && result.data) {
        stats.value = {
          today: result.data.today || 0,
          total: result.data.total || 0
        };
      }
    }
  } catch (error) {
    console.error('載入統計失敗:', error);
  }
};

// 重整記錄
const refreshRecords = async () => {
  isRefreshing.value = true;
  try {
    loadOfflineRecords();
    await loadStats();
  } catch (error) {
    showErrorToast('載入失敗');
  } finally {
    isRefreshing.value = false;
  }
};

// 新增最近記錄
const addRecentRecord = (record) => {
  recentRecords.value.unshift(record);
  if (recentRecords.value.length > 20) {
    recentRecords.value = recentRecords.value.slice(0, 20);
  }
};

// 格式化時間
const formatTime = (time) => {
  return formatDateTimeShort(time);
};

// 取得狀態文字
const getStatusText = (status) => {
  const map = {
    synced: '已同步',
    pending: '待同步',
    failed: '失敗'
  };
  return map[status] || status;
};

// 登出
const handleLogout = async () => {
  await logout();
  router.push('/verifier/login');
};

// 監聽網路恢復事件，自動同步
const handleNetworkOnline = () => {
  if (pendingCount.value > 0) {
    showSuccessToast('網路已恢復，正在自動同步離線記錄...');
    // 延遲一下再同步，確保網路穩定
    setTimeout(() => {
      syncOfflineRecords();
    }, 1000);
  }
};

// 初始化
onMounted(async () => {
  loadOfflineRecords();
  await loadStats();
  
  // 自動聚焦輸入框
  setTimeout(() => {
    barcodeInputRef.value?.focus();
  }, 300);
  
  // 監聽網路恢復事件
  window.addEventListener('network-online', handleNetworkOnline);
  
  // 如果網路已恢復且有待同步記錄，提示使用者
  if (isOnline.value && wasOffline.value && pendingCount.value > 0) {
    setTimeout(() => {
      showSuccessToast(`有 ${pendingCount.value} 筆待同步記錄，點擊「待同步」按鈕進行同步`);
    }, 2000);
  }
});

// 清理
onUnmounted(() => {
  window.removeEventListener('network-online', handleNetworkOnline);
});
</script>

<style scoped>
/* 觸控優化 */
.touch-manipulation {
  touch-action: manipulation;
}

/* 手機版優化 */
@media (max-width: 640px) {
  /* 確保輸入框在手機上不會縮放 */
  input[type="text"],
  input[type="password"] {
    font-size: 16px !important;
  }
}
</style>

