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
                  <th class="th-base min-w-[100px]">工程起迄</th>
                  <th class="th-base min-w-[110px]">開立張數</th>
                  <th class="th-base min-w-[120px]">客戶</th>
                  <th class="th-base min-w-[110px]">清運業者</th>
                  <th class="th-base min-w-[120px]">載運土質</th>
                  <th class="th-base min-w-[110px]">載運米數</th>
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
                  <td class="td-base truncate max-w-[20rem]" :title="item.project_name" >
                    <a v-if="item.closure_status !== 'closed'" href="return false;" @click.prevent="openEditModal(item)" class="text-blue-600 hover:text-blue-800 cursor-pointer">{{ item.project_name }}</a>
                    <span v-else class="text-gray-500">{{ item.project_name }}</span>
                  </td>
                  <td class="td-base">{{ item.flow_control_no }}</td>
                  <td class="td-base">{{ formatDate(item.issue_date) }}</td>
                  <td class="td-base">{{ formatDate(item.valid_date_from) }}<br>{{ formatDate(item.valid_date_to) }}</td>
                  <td class="td-base">
                    <div class="flex items-center gap-2">
                      <span>{{ item.issue_count ?? 0 }}</span>
                      <button @click="openAdjustModal(item)" class="btn-adjust" title="調整張數" :disabled="item.closure_status === 'closed'">調整</button>
                    </div>
                    <div v-if="item.voided_count || item.recycled_count" class="text-xs text-gray-500 mt-1">
                      <span v-if="item.voided_count">作廢：{{ item.voided_count }}</span>
                      <span v-if="item.voided_count && item.recycled_count" class="mx-1">/</span>
                      <span v-if="item.recycled_count">回收：{{ item.recycled_count }}</span>
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
                  <td class="td-base">
                    <div class="flex items-center gap-1 flex-wrap">
                      <template v-if="item.carry_soil_type && item.carry_soil_type.length > 0">
                        <span v-for="(code, idx) in item.carry_soil_type" :key="idx" class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-green-50 text-green-700 border border-green-200">
                          {{ getSoilTypeName(code) }}
                        </span>
                      </template>
                      <span v-else class="text-gray-400">-</span>
                    </div>
                  </td>
                  <td class="td-base">{{ item.carry_qty }}</td>
                  <td class="td-base truncate max-w-[20rem]" :title="item.status_desc">
                    {{ item.status_desc || '-' }}
                  </td>
                  <td class="td-base truncate max-w-[20rem]" :title="item.remark_desc">
                    {{ item.remark_desc || '-' }}
                  </td>
                  <td class="td-base">{{ item.created_by_name }}</td>
                  <td class="td-base">{{ item.updated_by_name }}</td>
                  <td class="td-base">{{ item.sys_serial_no }}</td>
                  <td class="td-base">
                    <span v-if="item.closure_status === 'closed'" class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 border border-red-300">
                      已結案
                    </span>
                    <span v-else class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 border border-green-300">
                      進行中
                    </span>
                  </td>
                  <td class="td-base font-medium">
                    <div class="action-buttons">
                      <button @click="openEditModal(item)" class="action-btn action-btn--edit" title="編輯" :disabled="item.closure_status === 'closed'">
                        <i class="fas fa-edit action-icon"></i>
                      </button>
                      <button @click="openDeleteModal(item)" class="action-btn action-btn--delete" title="刪除" :disabled="item.closure_status === 'closed'">
                        <i class="fas fa-trash action-icon"></i>
                      </button>
                      <button @click="openPrint(item)" class="action-btn action-btn--print" title="列印未列印" :disabled="item.closure_status === 'closed'">
                        <i class="fas fa-print action-icon"></i>
                      </button>
                      <button v-if="item.closure_status !== 'closed'" @click="openCloseModal(item)" class="action-btn action-btn--close" title="結案">
                        <i class="fas fa-check-circle action-icon"></i>
                      </button>
                      <button v-else @click="viewClosureCertificate(item)" class="action-btn action-btn--view" title="查看結案證明">
                        <i class="fas fa-file-image action-icon"></i>
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
                  <label class="label-base">工程期限（起）</label>
                  <input v-model="form.valid_date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                </div>
                <div>
                  <label class="label-base">工程期限（迄）</label>
                  <input v-model="form.valid_date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                </div>
                <div class="md:col-span-2">
                  <label class="label-base">客戶</label>
                  <select v-model.number="form.customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" :class="{ 'border-red-500': errors.customer_id }">
                    <option :value="0">請選擇</option>
                    <option v-for="c in customerOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                  <p v-if="errors.customer_id" class="text-red-500 text-xs mt-1">{{ errors.customer_id }}</p>
                </div>
                <div class="md:col-span-2">
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
                <div class="md:col-span-2">
                  <label class="label-base">載運土質（可多選）</label>
                  <Multiselect
                    v-model="form.carry_soil_type"
                    :options="soilTypeOptions"
                    mode="tags"
                    :close-on-select="false"
                    :searchable="true"
                    :clear-on-select="false"
                    :preserve-search="true"
                    placeholder="請選擇載運土質"
                    label="display"
                    valueProp="code"
                    trackBy="code"
                    :maxHeight="200"
                  />
                  <p class="text-xs text-gray-500 mt-1">已選擇 {{ form.carry_soil_type?.length || 0 }} 種土質</p>
                </div>
                <div class="md:col-span-2">
                  <label class="label-base">載運米數</label>
                  <input v-model.number="form.carry_qty" type="number" min="0" step="0.01" @focus="$event.target.select()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
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
      <div class="relative mx-auto px-6 py-5 border w-96 shadow-lg rounded-md bg-white" style="width: 480px;">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">調整開立張數</h3>
          <button @click="closeAdjustModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="space-y-3 max-h-[calc(100vh-300px)] overflow-y-auto">
          <!-- 統計資訊 -->
          <div class="text-sm text-gray-700 bg-gray-50 p-3 rounded-md border border-gray-200">
            <div v-if="loadingAdjustTotals" class="text-gray-500 flex items-center gap-2">
              <i class="fas fa-spinner fa-spin"></i>
              讀取統計中...
            </div>
            <template v-else>
              <div class="flex justify-between gap-4">
                <span>總張數：<span class="font-semibold">{{ adjustTotals?.total ?? 0 }} 張</span></span>
                <span>總米數：<span class="font-semibold">{{ adjustTotals?.total_meters ?? 0 }} 米</span></span>
                <span>已印：<span class="font-semibold">{{ adjustTotals?.verified ?? 0 }}</span></span>
                <span>未印：<span class="font-semibold">{{ adjustTotals?.pending ?? 0 }}</span></span>
              </div>
            </template>
          </div>

          <!-- 米數張數輸入 -->
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-900">增加張數</label>
            <div v-for="meterType in meterTypeOptions" :key="meterType.value" class="flex items-center gap-2">
              <span class="w-12">{{ meterType.label }}</span>
              <input
                type="number"
                min="0"
                v-model.number="adjustByMeters[meterType.field]"
                @focus="$event.target.select()"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
              <span class="w-6 text-gray-500">張</span>
            </div>
          </div>

          <!-- 總米數計算 -->
          <div class="bg-amber-50 p-3 rounded-md border border-amber-200">
            <div class="text-sm font-semibold text-amber-900">
              合計：<span>{{ totalCalculatedMeters }}</span> / {{ adjustTarget?.carry_qty || 0 }} 米
            </div>
            <p v-if="totalCalculatedMeters > (adjustTarget?.carry_qty || 0)" class="text-red-600 text-xs mt-2">
              ❌ 超過限制
            </p>
          </div>

          <!-- 使用期限 -->
          <div class="grid grid-cols-2 gap-3 border-t pt-3">
            <div class="relative">
              <input
                type="date"
                v-model="adjustUseStartDate"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent peer" />
              <label class="absolute left-3 -top-5 text-xs font-medium text-gray-600 bg-white px-1">使用起始日期</label>
            </div>

            <div class="relative">
              <input
                type="date"
                v-model="adjustUseEndDate"
                :min="adjustUseStartDate"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent peer" />
              <label class="absolute left-3 -top-5 text-xs font-medium text-gray-600 bg-white px-1">使用結束日期</label>
            </div>
          </div>
        </div>

        <div class="mt-4 flex justify-end gap-3 border-t pt-4">
          <button @click="closeAdjustModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">取消</button>
          <button @click="submitAdjust" :disabled="submittingAdjust || totalCalculatedMeters > (adjustTarget?.carry_qty || 0)" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 border border-transparent rounded-md hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed">
            <i v-if="submittingAdjust" class="fas fa-spinner fa-spin mr-2"></i>
            確認
          </button>
        </div>
      </div>
    </div>

    <!-- Close Project Modal -->
    <div v-if="showCloseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">確認結案</h3>
            <button @click="closeCloseModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500 mb-4">
              您確定要結案工程「{{ itemToClose?.project_name }}」嗎？<br>
              結案後將無法再進行編輯、刪除及列印操作，<br>
              上傳的照片將嵌入結案證明 PDF 文件中。
            </p>

            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  結案現場照片 <span class="text-red-500">*</span>
                </label>
                <input
                  type="file"
                  ref="certificateInput"
                  @change="handleCertificateChange"
                  accept="image/*"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100"
                />
                <p class="text-xs text-gray-500 mt-1">請上傳結案現場照片（最大5MB），將嵌入證明書中</p>
                <p v-if="closureErrors.certificate" class="text-xs text-red-500 mt-1">{{ closureErrors.certificate }}</p>
              </div>

              <div v-if="certificatePreview" class="border rounded-md p-2">
                <img :src="certificatePreview" alt="預覽" class="max-w-full h-auto max-h-48 mx-auto" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  結案備註
                </label>
                <textarea
                  v-model="closureRemark"
                  rows="3"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  placeholder="請輸入結案相關說明（選填）"
                ></textarea>
              </div>
            </div>
          </div>
          <div class="flex justify-center space-x-3 pt-4">
            <button @click="closeCloseModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">取消</button>
            <button @click="confirmClose" :disabled="closing || !closureCertificate" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
              <i v-if="closing" class="fas fa-spinner fa-spin mr-2"></i>
              確認結案
            </button>
          </div>
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
import { usePagination } from '@/composables/usePagination'
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

// close project modal state
const showCloseModal = ref(false)
const itemToClose = ref(null)
const closureCertificate = ref(null)
const certificatePreview = ref(null)
const closureRemark = ref('')
const closing = ref(false)
const closureErrors = ref({})
const certificateInput = ref(null)

// print modal state
const showPrintModal = ref(false)
const printTarget = ref(null)
const printCount = ref(1)
const printTotals = ref({ total: 0, total_meters: 0, verified: 0, pending: 0 })
const loadingPrintTotals = ref(false)

// adjust count modal state
const showAdjustModal = ref(false)
const adjustTarget = ref(null)
const adjustAction = ref('add')
const adjustUseStartDate = ref('')
const adjustUseEndDate = ref('')
const adjustByMeters = reactive({})
const submittingAdjust = ref(false)
const adjustTotals = ref({ total: 0, total_meters: 0, verified: 0, pending: 0 })
const loadingAdjustTotals = ref(false)

// 計算總米數
const totalCalculatedMeters = computed(() => {
  return meterTypeOptions.value.reduce((total, meterType) => {
    const count = adjustByMeters[meterType.field] || 0
    return total + (count * meterType.value)
  }, 0)
})

// options for selects
const cleanerOptions = ref([])
const customerOptions = ref([])
const soilTypeOptions = ref([])
const meterTypeOptions = ref([])

// 載入客戶選項
const loadCustomerOptions = async () => {
  try {
    const resp = await commonAPI.customers()
    console.log('客戶 API 回應:', resp)
    if (resp.status) {
      customerOptions.value = Array.isArray(resp.data) ? resp.data : []
      console.log('客戶選項:', customerOptions.value)
    }
  } catch (e) {
    console.error('載入客戶選項錯誤:', e)
  }
}

// 載入清運業者選項
const loadCleanerOptions = async () => {
  try {
    const resp = await commonAPI.cleaners()
    console.log('清運業者 API 回應:', resp)
    if (resp.status) {
      cleanerOptions.value = Array.isArray(resp.data) ? resp.data : []
      console.log('清運業者選項:', cleanerOptions.value)
    }
  } catch (e) {
    console.error('載入清運業者選項錯誤:', e)
  }
}

// 載入土質類型選項
const loadSoilTypeOptions = async () => {
  try {
    const resp = await commonAPI.soilTypes()
    console.log('土質類型 API 回應:', resp)
    if (resp.status) {
      soilTypeOptions.value = Array.isArray(resp.data) ? resp.data : []
      console.log('土質類型選項:', soilTypeOptions.value)
    }
  } catch (e) {
    console.error('載入土質類型選項錯誤:', e)
  }
}

// 載入米數類型選項
const loadMeterTypeOptions = async () => {
  try {
    const resp = await commonAPI.meterTypes()
    console.log('米數類型 API 回應:', resp)
    if (resp.status) {
      meterTypeOptions.value = Array.isArray(resp.data) ? resp.data : []
      // 初始化 adjustByMeters 的欄位
      meterTypeOptions.value.forEach(meterType => {
        adjustByMeters[meterType.field] = 0
      })
      console.log('米數類型選項:', meterTypeOptions.value)
    }
  } catch (e) {
    console.error('載入米數類型選項錯誤:', e)
  }
}

// inline edit state
const editing = reactive({})
const editingField = reactive({})

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
  carry_soil_type: [],
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

// 使用共用的分頁邏輯
const { visiblePages, goToPage } = usePagination(pagination, loadEarthData)

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  // ensure options are ready
  if (cleanerOptions.value.length === 0) loadCleanerOptions()
  if (customerOptions.value.length === 0) loadCustomerOptions()
  if (soilTypeOptions.value.length === 0) loadSoilTypeOptions()
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
  // 處理載運土質（可能是陣列或字串）
  if (Array.isArray(item.carry_soil_type)) {
    form.carry_soil_type = item.carry_soil_type
  } else if (item.carry_soil_type) {
    form.carry_soil_type = [item.carry_soil_type]
  } else {
    form.carry_soil_type = []
  }
  form.status_desc = item.status_desc || ''
  form.remark_desc = item.remark_desc || ''
  form.sys_serial_no = item.sys_serial_no || ''
  form.status = item.status || 'active'
  // ensure options are ready
  if (cleanerOptions.value.length === 0) loadCleanerOptions()
  if (customerOptions.value.length === 0) loadCustomerOptions()
  if (soilTypeOptions.value.length === 0) loadSoilTypeOptions()
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

const openCloseModal = (item) => {
  itemToClose.value = item
  closureCertificate.value = null
  certificatePreview.value = null
  closureRemark.value = ''
  closureErrors.value = {}
  showCloseModal.value = true
}

const closeCloseModal = () => {
  showCloseModal.value = false
  itemToClose.value = null
  closureCertificate.value = null
  certificatePreview.value = null
  closureRemark.value = ''
  closureErrors.value = {}
}

const handleCertificateChange = (event) => {
  const file = event.target.files[0]
  closureErrors.value = {}

  if (!file) {
    closureCertificate.value = null
    certificatePreview.value = null
    return
  }

  // 驗證檔案類型
  if (!file.type.startsWith('image/')) {
    closureErrors.value.certificate = '請選擇圖片檔案'
    closureCertificate.value = null
    certificatePreview.value = null
    return
  }

  // 驗證檔案大小（5MB）
  if (file.size > 5 * 1024 * 1024) {
    closureErrors.value.certificate = '圖片大小不可超過 5MB'
    closureCertificate.value = null
    certificatePreview.value = null
    return
  }

  closureCertificate.value = file

  // 產生預覽
  const reader = new FileReader()
  reader.onload = (e) => {
    certificatePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const confirmClose = async () => {
  if (!itemToClose.value) return
  if (!closureCertificate.value) {
    closureErrors.value.certificate = '請上傳結案照片'
    return
  }

  try {
    closing.value = true

    const formData = new FormData()
    formData.append('certificate', closureCertificate.value)
    formData.append('closure_remark', closureRemark.value)

    const resp = await earthDataAPI.close(itemToClose.value.id, formData)

    if (resp.status) {
      showToast('工程結案成功，結案證明已產生', 'success')
      closeCloseModal()
      loadEarthData()
    } else {
      throw new Error(resp.message || '結案失敗')
    }
  } catch (error) {
    console.error('結案錯誤:', error)
    showToast(error.message || '結案失敗', 'error')
  } finally {
    closing.value = false
  }
}

const viewClosureCertificate = (item) => {
  if (item.id) {
    // 開啟結案證明 HTML 頁面（可在瀏覽器中列印為 PDF）
    window.open(`/print/earth-data/${item.id}/closure-certificate`, '_blank')
  } else {
    showToast('無結案證明', 'warning')
  }
}

const openAdjustModal = async (item) => {
  adjustTarget.value = item
  adjustAction.value = 'add'
  // 重置所有米數輸入
  meterTypeOptions.value.forEach(meterType => {
    adjustByMeters[meterType.field] = 0
  })
  adjustTotals.value = { total: 0, total_meters: 0, verified: 0, pending: 0 }
  showAdjustModal.value = true
  loadingAdjustTotals.value = true
  try {
    const resp = await earthDataAPI.usageStats(item.id)
    console.log('調整對話框統計 API 回應:', resp)
    if (resp.status) {
      console.log('統計資料:', resp.data?.totals)
      adjustTotals.value = resp.data?.totals || { total: 0, total_meters: 0, verified: 0, pending: 0 }
      console.log('adjustTotals.value 設定為:', adjustTotals.value)
    }
  } catch (e) {
    console.error('載入統計失敗:', e)
  } finally {
    loadingAdjustTotals.value = false
  }
}

const closeAdjustModal = () => {
  showAdjustModal.value = false
  adjustTarget.value = null
  // 重置所有米數輸入
  meterTypeOptions.value.forEach(meterType => {
    adjustByMeters[meterType.field] = 0
  })
}

const submitAdjust = async () => {
  if (!adjustTarget.value) return
  try {
    submittingAdjust.value = true

    // 驗證米數
    const totalMeters = totalCalculatedMeters.value
    if (adjustAction.value === 'add' && totalMeters <= 0) {
      showToast('請輸入至少一個米數的張數', 'error')
      return
    }
    if (totalMeters > (adjustTarget.value.carry_qty || 0)) {
      showToast(`總米數不可超過案件載運米數限制（${adjustTarget.value.carry_qty}米）`, 'error')
      return
    }

    // 動態生成 meters payload
    const meters = {}
    meterTypeOptions.value.forEach(meterType => {
      meters[meterType.field] = adjustByMeters[meterType.field] || 0
    })

    const payload = {
      action: adjustAction.value,
      meters: meters
    }

    const resp = await earthDataAPI.adjustDetails(adjustTarget.value.id, payload)
    if (resp.status) {
      // update current row issue_count
      const newCount = resp.data?.issue_count
      if (typeof newCount === 'number') {
        adjustTarget.value.issue_count = newCount
      } else {
        // fallback reload
        await loadEarthData(pagination.value?.current_page || 1)
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
  printTotals.value = { total: 0, total_meters: 0, verified: 0, pending: 0 }
  showPrintModal.value = true
  loadingPrintTotals.value = true
  try {
    const resp = await earthDataAPI.usageStats(item.id)
    console.log('列印對話框統計 API 回應:', resp)
    if (resp.status) {
      const totals = resp.data?.totals || {}
      console.log('統計資料:', totals)
      const t = Number(totals.total ?? 0)
      const tm = Number(totals.total_meters ?? 0)
      const v = Number(totals.verified ?? 0)
      const p = Math.max(0, t - v)
      printTotals.value = { total: t, total_meters: tm, verified: v, pending: p }
      console.log('printTotals.value 設定為:', printTotals.value)
      // clamp default
      if (printCount.value > p) printCount.value = p || 1
    } else {
      showToast(resp.message || '取得統計失敗', 'error')
    }
  } catch (e) {
    console.error('載入統計失敗:', e)
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
  form.carry_soil_type = []
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

// 取得土質名稱
const getSoilTypeName = (code) => {
  const type = soilTypeOptions.value.find(t => t.code === code)
  return type ? type.code : code
}

watch([sortBy, sortOrder], () => { loadEarthData() })

onMounted(() => {
  loadEarthData()
  loadCleanerOptions()
  loadCustomerOptions()
  loadSoilTypeOptions()
  loadMeterTypeOptions()
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
