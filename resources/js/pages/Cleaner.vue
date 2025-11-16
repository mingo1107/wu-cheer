<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-white/20">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">清運業者管理</h2>
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
                    <span class="text-sm font-medium text-gray-500">清運業者管理</span>
                  </div>
                </li>
              </ol>
            </nav>
          </div>
          <div class="flex gap-3 mt-4 sm:mt-0">
            <button @click="openCreate" class="bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
              <i class="fas fa-plus mr-2"></i>新增清運業者
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div class="mb-6">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <div class="relative">
                <input v-model="filters.search" @input="debouncedSearch" type="text" placeholder="搜尋（名稱、聯絡人、電話、統一編號）" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-gray-400"></i>
                </div>
              </div>
            </div>
            <div class="flex gap-2">
              <select v-model="filters.status" @change="load" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200">
                <option value="">全部狀態</option>
                <option value="active">活躍</option>
                <option value="inactive">停用</option>
              </select>
              <select v-model="filters.sort_by" @change="load" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200">
                <option value="created_at">建立時間</option>
                <option value="cleaner_name">名稱</option>
                <option value="contact_person">聯絡人</option>
                <option value="updated_at">更新時間</option>
              </select>
              <select v-model="filters.sort_order" @change="load" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200">
                <option value="desc">降序</option>
                <option value="asc">升序</option>
              </select>
            </div>
          </div>
        </div>

        <!-- List -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gradient-to-r from-amber-50 to-orange-50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名稱</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">聯絡人</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">電話</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">統一編號</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">車輛數量</th>
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
                <tr v-else-if="items.length === 0">
                  <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-truck text-4xl mb-4 text-gray-300"></i>
                    <div class="text-lg font-medium">沒有資料</div>
                    <div class="text-sm">請嘗試調整搜尋條件或新增資料</div>
                  </td>
                </tr>
                <tr v-else v-for="it in items" :key="it.id" class="hover:bg-gray-50">
                  <td class="td-base font-medium">{{ it.cleaner_name }}</td>
                  <td class="td-base">{{ it.contact_person }}</td>
                  <td class="td-base">{{ it.phone }}</td>
                  <td class="td-base">{{ it.tax_id || '-' }}</td>
                  <td class="td-base">
                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      <i class="fas fa-car mr-1"></i>
                      {{ it.vehicles_count || 0 }} 台
                    </span>
                  </td>
                  <td class="td-base">
                    <span :class="it.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ it.status === 'active' ? '活躍' : '停用' }}
                    </span>
                  </td>
                  <td class="td-base text-gray-500">{{ formatDate(it.created_at) }}</td>
                  <td class="td-base font-medium">
                    <div class="action-buttons">
                      <button @click="openVehicleManage(it)" class="action-btn action-btn--vehicle" title="管理車輛">
                        <i class="fas fa-car action-icon"></i>
                      </button>
                      <button @click="openEdit(it)" class="action-btn action-btn--edit" title="編輯">
                        <i class="fas fa-edit action-icon"></i>
                      </button>
                      <button @click="openDelete(it)" class="action-btn action-btn--delete" title="刪除">
                        <i class="fas fa-trash action-icon"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="pagination && pagination.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  顯示第 <span class="font-medium">{{ pagination.from || 0 }}</span> 到 <span class="font-medium">{{ pagination.to || 0 }}</span> 筆，共 <span class="font-medium">{{ pagination.total }}</span> 筆
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

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">{{ isEditing ? '編輯清運業者' : '新增清運業者' }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="label-base">名稱 *</label>
                <input v-model="form.cleaner_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': errors.cleaner_name }" />
                <p v-if="errors.cleaner_name" class="text-red-500 text-xs mt-1">{{ errors.cleaner_name }}</p>
              </div>
              <div>
                <label class="label-base">聯絡人 *</label>
                <input v-model="form.contact_person" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': errors.contact_person }" />
                <p v-if="errors.contact_person" class="text-red-500 text-xs mt-1">{{ errors.contact_person }}</p>
              </div>
              <div>
                <label class="label-base">聯絡電話 *</label>
                <input v-model="form.phone" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': errors.phone }" />
                <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</p>
              </div>
              <div>
                <label class="label-base">統一編號（非必填）</label>
                <input v-model="form.tax_id" maxlength="8" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': errors.tax_id }" />
                <p v-if="errors.tax_id" class="text-red-500 text-xs mt-1">{{ errors.tax_id }}</p>
              </div>
              <div class="md:col-span-2">
                <label class="label-base">狀態</label>
                <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                  <option value="active">活躍</option>
                  <option value="inactive">停用</option>
                </select>
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
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

    <!-- Delete Modal -->
    <div v-if="showDelete" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">確認刪除</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">您確定要刪除清運業者「<strong>{{ toDelete?.cleaner_name }}</strong>」嗎？此操作無法復原。</p>
          </div>
          <div class="flex justify-center space-x-3 pt-4">
            <button @click="closeDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">取消</button>
            <button @click="confirmDelete" :disabled="deleting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
              <i v-if="deleting" class="fas fa-spinner fa-spin mr-2"></i>
              刪除
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Vehicle Management Dialog -->
    <div v-if="showVehicleDialog" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto" style="min-height: 300px;">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              <i class="fas fa-car mr-2"></i>
              管理車輛 - {{ currentCleaner?.cleaner_name }}
            </h3>
          </div>

        <!-- Vehicle List -->
        <div class="mb-6">
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">車頭車號</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">車尾車號</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">狀態</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">備註</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="vehiclesLoading" class="animate-pulse">
                  <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-xl mb-2"></i>
                    <div>載入中...</div>
                  </td>
                </tr>
                <tr v-else-if="vehicles.length === 0">
                  <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    <i class="fas fa-car text-3xl mb-2 text-gray-300"></i>
                    <div class="text-sm">尚無車輛資料</div>
                  </td>
                </tr>
                <tr v-else v-for="v in vehicles" :key="v.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ v.front_plate }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900">{{ v.rear_plate || '-' }}</td>
                  <td class="px-4 py-3 text-sm">
                    <span :class="v.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ v.status === 'active' ? '活躍' : '停用' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500">{{ v.notes || '-' }}</td>
                  <td class="px-4 py-3 text-sm font-medium">
                    <div class="flex space-x-4">
                      <button @click="openVehicleEdit(v)" class="p-2 rounded text-amber-600 hover:text-amber-900 hover:bg-amber-50 transition-colors duration-200" title="編輯">
                        <i class="fas fa-edit text-lg"></i>
                      </button>
                      <button @click="openVehicleDelete(v)" class="p-2 rounded text-red-600 hover:text-red-900 hover:bg-red-50 transition-colors duration-200" title="刪除">
                        <i class="fas fa-trash text-lg"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Add Vehicle Button and Close Button -->
        <div class="flex justify-end gap-3 mb-4">
          <button @click="openVehicleCreate" class="bg-gradient-to-r from-green-500 to-green-600 text-white py-2 px-4 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
            <i class="fas fa-plus mr-2"></i>新增車輛
          </button>
          <button @click="closeVehicleDialog" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200">
            <i class="fas fa-times mr-1"></i>
            關閉
          </button>
        </div>
        </div>
      </div>
    </div>

    <!-- Vehicle Create/Edit Modal -->
    <div v-if="showVehicleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">{{ isVehicleEditing ? '編輯車輛' : '新增車輛' }}</h3>
            <button @click="closeVehicleModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <form @submit.prevent="submitVehicle" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="label-base">車頭車號 *</label>
                <input v-model="vehicleForm.front_plate" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': vehicleErrors.front_plate }" />
                <p v-if="vehicleErrors.front_plate" class="text-red-500 text-xs mt-1">{{ vehicleErrors.front_plate }}</p>
              </div>
              <div>
                <label class="label-base">車尾車號</label>
                <input v-model="vehicleForm.rear_plate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': vehicleErrors.rear_plate }" />
                <p v-if="vehicleErrors.rear_plate" class="text-red-500 text-xs mt-1">{{ vehicleErrors.rear_plate }}</p>
              </div>
              <div class="md:col-span-2">
                <label class="label-base">狀態</label>
                <select v-model="vehicleForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                  <option value="active">活躍</option>
                  <option value="inactive">停用</option>
                </select>
              </div>
              <div class="md:col-span-2">
                <label class="label-base">備註</label>
                <textarea v-model="vehicleForm.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="closeVehicleModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">取消</button>
              <button type="submit" :disabled="vehicleSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 border border-transparent rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <i v-if="vehicleSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
                {{ isVehicleEditing ? '更新' : '建立' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Vehicle Delete Modal -->
    <div v-if="showVehicleDelete" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">確認刪除</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">您確定要刪除車輛「<strong>{{ toDeleteVehicle?.front_plate }}</strong>」嗎？此操作無法復原。</p>
          </div>
          <div class="flex justify-center space-x-3 pt-4">
            <button @click="closeVehicleDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">取消</button>
            <button @click="confirmVehicleDelete" :disabled="vehicleDeleting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
              <i v-if="vehicleDeleting" class="fas fa-spinner fa-spin mr-2"></i>
              刪除
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import cleanerAPI from '../api/cleaner.js'

const items = ref([])
const loading = ref(false)
const pagination = ref(null)
const filters = reactive({ search: '', status: '', sort_by: 'created_at', sort_order: 'desc', per_page: 15 })

const showModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const errors = ref({})

const showDelete = ref(false)
const deleting = ref(false)
const toDelete = ref(null)

const form = reactive({ id: null, cleaner_name: '', tax_id: '', contact_person: '', phone: '', status: 'active' })

// 車輛管理相關
const showVehicleDialog = ref(false)
const showVehicleModal = ref(false)
const showVehicleDelete = ref(false)
const currentCleaner = ref(null)
const vehicles = ref([])
const vehiclesLoading = ref(false)
const isVehicleEditing = ref(false)
const vehicleSubmitting = ref(false)
const vehicleDeleting = ref(false)
const toDeleteVehicle = ref(null)
const vehicleErrors = ref({})

const vehicleForm = reactive({
  id: null,
  cleaner_id: null,
  front_plate: '',
  rear_plate: '',
  status: 'active',
  notes: ''
})

let searchTimeout = null
const debouncedSearch = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => load(), 500) }

const visiblePages = computed(() => {
  if (!pagination.value) return []
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const pages = []
  if (last <= 7) { for (let i = 1; i <= last; i++) pages.push(i) }
  else if (current <= 4) { for (let i = 1; i <= 5; i++) pages.push(i); pages.push('...'); pages.push(last) }
  else if (current >= last - 3) { pages.push(1); pages.push('...'); for (let i = last - 4; i <= last; i++) pages.push(i) }
  else { pages.push(1); pages.push('...'); for (let i = current - 1; i <= current + 1; i++) pages.push(i); pages.push('...'); pages.push(last) }
  return pages
})

const load = async (page = 1) => {
  try {
    loading.value = true
    const resp = await cleanerAPI.getCleaners({ ...filters, page })
    if (resp.status) {
      items.value = Array.isArray(resp.data?.data) ? resp.data.data : []
      pagination.value = {
        current_page: resp.data.current_page,
        last_page: resp.data.last_page,
        per_page: resp.data.per_page,
        total: resp.data.total,
        from: resp.data.from,
        to: resp.data.to
      }
    } else {
      throw new Error(resp.message || '載入失敗')
    }
  } catch (e) {
    console.error('載入錯誤:', e)
    alert(e.message || '載入失敗')
  } finally {
    loading.value = false
  }
}

const goToPage = (page) => { if (page >= 1 && page <= pagination.value.last_page) load(page) }

const openCreate = () => { isEditing.value = false; resetForm(); showModal.value = true }
const openEdit = (it) => {
  isEditing.value = true
  form.id = it.id
  form.cleaner_name = it.cleaner_name
  form.tax_id = it.tax_id || ''
  form.contact_person = it.contact_person
  form.phone = it.phone
  form.status = it.status
  
  errors.value = {}
  showModal.value = true
}

const closeModal = () => { 
  showModal.value = false; 
  resetForm(); 
  errors.value = {} 
}

const resetForm = () => { 
  form.id=null; 
  form.cleaner_name=''; 
  form.tax_id=''; 
  form.contact_person=''; 
  form.phone=''; 
  form.status='active' 
}

const submit = async () => {
  try {
    submitting.value = true
    errors.value = {}
    let resp
    if (isEditing.value) { resp = await cleanerAPI.updateCleaner(form.id, form) }
    else { resp = await cleanerAPI.createCleaner(form) }

    if (resp.status) {
      closeModal(); load()
    } else {
      if (resp.errors) { errors.value = resp.errors } else { throw new Error(resp.message || '操作失敗') }
    }
  } catch (e) {
    console.error('提交錯誤:', e)
    alert(e.message || '操作失敗')
  } finally {
    submitting.value = false
  }
}

const openDelete = (it) => { toDelete.value = it; showDelete.value = true }
const closeDelete = () => { showDelete.value = false; toDelete.value = null }
const confirmDelete = async () => {
  try {
    deleting.value = true
    const resp = await cleanerAPI.deleteCleaner(toDelete.value.id)
    if (resp.status) { closeDelete(); load() } else { throw new Error(resp.message || '刪除失敗') }
  } catch (e) {
    console.error('刪除錯誤:', e)
    alert(e.message || '刪除失敗')
  } finally {
    deleting.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-TW', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

// 車輛管理方法
const openVehicleManage = async (cleaner) => {
  currentCleaner.value = cleaner
  showVehicleDialog.value = true
  await loadVehicles(cleaner.id)
}

const closeVehicleDialog = () => {
  showVehicleDialog.value = false
  currentCleaner.value = null
  vehicles.value = []
}

const loadVehicles = async (cleanerId) => {
  try {
    vehiclesLoading.value = true
    const resp = await cleanerAPI.getVehicles(cleanerId)
    if (resp.status) {
      vehicles.value = Array.isArray(resp.data) ? resp.data : []
    } else {
      throw new Error(resp.message || '載入車輛失敗')
    }
  } catch (e) {
    console.error('載入車輛錯誤:', e)
    alert(e.message || '載入車輛失敗')
  } finally {
    vehiclesLoading.value = false
  }
}

const openVehicleCreate = () => {
  isVehicleEditing.value = false
  resetVehicleForm()
  showVehicleModal.value = true
}

const openVehicleEdit = (vehicle) => {
  isVehicleEditing.value = true
  vehicleForm.id = vehicle.id
  vehicleForm.cleaner_id = vehicle.cleaner_id
  vehicleForm.front_plate = vehicle.front_plate
  vehicleForm.rear_plate = vehicle.rear_plate || ''
  vehicleForm.status = vehicle.status
  vehicleForm.notes = vehicle.notes || ''
  vehicleErrors.value = {}
  showVehicleModal.value = true
}

const closeVehicleModal = () => {
  showVehicleModal.value = false
  resetVehicleForm()
  vehicleErrors.value = {}
}

const resetVehicleForm = () => {
  vehicleForm.id = null
  vehicleForm.cleaner_id = currentCleaner.value?.id || null
  vehicleForm.front_plate = ''
  vehicleForm.rear_plate = ''
  vehicleForm.status = 'active'
  vehicleForm.notes = ''
}

const submitVehicle = async () => {
  try {
    vehicleSubmitting.value = true
    vehicleErrors.value = {}
    
    if (!currentCleaner.value) {
      throw new Error('清運業者資訊不存在')
    }

    let resp
    if (isVehicleEditing.value) {
      resp = await cleanerAPI.updateVehicle(currentCleaner.value.id, vehicleForm.id, vehicleForm)
    } else {
      resp = await cleanerAPI.createVehicle(currentCleaner.value.id, vehicleForm)
    }

    if (resp.status) {
      closeVehicleModal()
      await loadVehicles(currentCleaner.value.id)
      // 重新載入清運業者列表以更新車輛數量
      load()
    } else {
      if (resp.errors) {
        vehicleErrors.value = resp.errors
      } else {
        throw new Error(resp.message || '操作失敗')
      }
    }
  } catch (e) {
    console.error('提交車輛錯誤:', e)
    alert(e.message || '操作失敗')
  } finally {
    vehicleSubmitting.value = false
  }
}

const openVehicleDelete = (vehicle) => {
  toDeleteVehicle.value = vehicle
  showVehicleDelete.value = true
}

const closeVehicleDelete = () => {
  showVehicleDelete.value = false
  toDeleteVehicle.value = null
}

const confirmVehicleDelete = async () => {
  try {
    vehicleDeleting.value = true
    if (!currentCleaner.value || !toDeleteVehicle.value) {
      throw new Error('資料不存在')
    }

    const resp = await cleanerAPI.deleteVehicle(currentCleaner.value.id, toDeleteVehicle.value.id)
    if (resp.status) {
      closeVehicleDelete()
      await loadVehicles(currentCleaner.value.id)
      // 重新載入清運業者列表以更新車輛數量
      load()
    } else {
      throw new Error(resp.message || '刪除失敗')
    }
  } catch (e) {
    console.error('刪除車輛錯誤:', e)
    alert(e.message || '刪除失敗')
  } finally {
    vehicleDeleting.value = false
  }
}

onMounted(() => { load() })
</script>

<style scoped>
/* 所有通用樣式已移至 resources/css/app.css */
</style>
