<template>
  <div class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50">
    <!-- 背景裝飾 -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-amber-200 rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-orange-200 rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
    </div>

    <!-- Header -->
    <header class="relative bg-white/90 backdrop-blur-sm shadow-lg border-b border-white/20 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center h-14">
          <!-- Logo 和系統名稱 -->
          <div class="flex items-center">
            <div class="flex-shrink-0 flex items-center">
              <div class="w-8 h-8 bg-gradient-to-r from-amber-500 to-orange-600 rounded-lg flex items-center justify-center mr-2 shadow-lg">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
              </div>
              <div>
                <h1 class="text-lg font-bold text-gray-800">土方石清運管理系統</h1>
                <p class="text-xs text-gray-600">伍齊資源有限公司</p>
              </div>
            </div>
          </div>

          <!-- 導航選單 -->
          <nav class="hidden md:flex space-x-4 ml-6">
            <router-link
              to="/dashboard"
              class="text-gray-700 hover:text-amber-600 px-2 py-1.5 rounded-md text-sm font-medium transition-colors duration-200"
              :class="{ 'text-amber-600 bg-amber-50': $route.path === '/dashboard' }"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
              </svg>
              儀表板
            </router-link>

            <!-- 後台管理 下拉選單 -->
            <div class="relative z-50" @mouseenter="showAdminMenu" @mouseleave="hideAdminMenu">
              <button class="text-gray-700 hover:text-amber-600 px-2 py-1.5 rounded-md text-sm font-medium transition-colors duration-200 flex items-center" :class="{ 'text-amber-600 bg-amber-50': isAdminActive }">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                後台管理
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
              </button>
              <div v-if="adminMenuOpen" class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1" @mouseenter="showAdminMenu" @mouseleave="hideAdminMenu">
                <router-link to="/user" @click="adminMenuOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600">
                  <i class="fas fa-user mr-3"></i> 使用者管理
                </router-link>
                <div class="flex items-center px-4 py-2 text-sm text-gray-400 cursor-not-allowed">
                  <i class="fas fa-clipboard-list mr-3"></i> 使用者操作紀錄（開發中）
                </div>
              </div>
            </div>

            <!-- 資料設定 下拉選單 -->
            <div class="relative z-50" @mouseenter="showDataMenu" @mouseleave="hideDataMenu">
              <button class="text-gray-700 hover:text-amber-600 px-2 py-1.5 rounded-md text-sm font-medium transition-colors duration-200 flex items-center" :class="{ 'text-amber-600 bg-amber-50': isDataActive }">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M3 3h14a1 1 0 011 1v12a1 1 0 01-1 1H3a1 1 0 01-1-1V4a1 1 0 011-1z"/>
                </svg>
                資料設定
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
              </button>
              <div v-if="dataMenuOpen" class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1" @mouseenter="showDataMenu" @mouseleave="hideDataMenu">
                <router-link to="/customer" @click="dataMenuOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600">
                  <i class="fas fa-building mr-3"></i> 客戶管理
                </router-link>
                <router-link to="/cleaner" @click="dataMenuOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600">
                  <i class="fas fa-truck mr-3"></i> 清運業者管理
                </router-link>
                <router-link to="/announcement" @click="dataMenuOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600">
                  <i class="fas fa-bullhorn mr-3"></i> 公告管理
                </router-link>
              </div>
            </div>
            
            <!-- 土單系統下拉選單 -->
            <div class="relative z-50" @mouseenter="showEarthSystemMenu" @mouseleave="hideEarthSystemMenu">
              <button class="text-gray-700 hover:text-amber-600 px-2 py-1.5 rounded-md text-sm font-medium transition-colors duration-200 flex items-center" :class="{ 'text-amber-600 bg-amber-50': isEarthSystemActive }">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                土單系統
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
              </button>
              
              <!-- 下拉選單 -->
              <div v-if="earthSystemMenuOpen" class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-[9999]" @mouseenter="showEarthSystemMenu" @mouseleave="hideEarthSystemMenu">
                <router-link
                  to="/case"
                  @click="earthSystemMenuOpen = false"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors duration-200"
                >
                  <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"/>
                  </svg>
                  案件管理
                </router-link>
                <router-link
                  to="/earth-data"
                  @click="earthSystemMenuOpen = false"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors duration-200"
                >
                  <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                  </svg>
                  土單資料管理
                </router-link>
                <router-link
                  to="/earth-recycle"
                  @click="earthSystemMenuOpen = false"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors duration-200"
                >
                  <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                  </svg>
                  土單回收作業
                </router-link>
                <router-link
                  to="/earth-statistics"
                  @click="earthSystemMenuOpen = false"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors duration-200"
                >
                  <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                  </svg>
                  土單使用統計表
                </router-link>
              </div>
            </div>
          </nav>

          <!-- 使用者選單 -->
          <div class="flex items-center ml-auto">
            <div class="relative z-50">
              <!-- 使用者頭像按鈕 -->
              <button
                @click="userMenuOpen = !userMenuOpen"
                class="flex items-center space-x-1 text-gray-700 hover:text-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 rounded-lg p-1.5 transition-colors duration-200"
              >
                <div class="w-7 h-7 bg-gradient-to-r from-amber-500 to-orange-600 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <span class="hidden md:block text-sm font-medium">{{ user?.name || '使用者' }}</span>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
              </button>

              <!-- 下拉選單 -->
              <div
                v-if="userMenuOpen"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-[9999]"
              >
                <!-- 個人資料 -->
                <router-link
                  to="/profile"
                  @click="userMenuOpen = false"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors duration-200"
                >
                  <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>
                  個人資料
                </router-link>

                <!-- 修改密碼 -->
                <button
                  @click="handleChangePassword"
                  class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors duration-200"
                >
                  <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                  </svg>
                  修改密碼
                </button>

                <!-- 分隔線 -->
                <div class="border-t border-gray-100 my-1"></div>

                <!-- 登出 -->
                <button
                  @click="handleLogout"
                  :disabled="isLoading"
                  class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                >
                  <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                  </svg>
                  登出
                </button>
              </div>
            </div>
          </div>

          <!-- 手機版選單按鈕 -->
          <div class="md:hidden">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="text-gray-700 hover:text-amber-600 p-2 rounded-md"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- 手機版選單 -->
        <div v-if="mobileMenuOpen" class="md:hidden">
          <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white/80 backdrop-blur-sm rounded-lg mt-2 shadow-lg">
            <router-link
              to="/dashboard"
              @click="mobileMenuOpen = false"
              class="text-gray-700 hover:text-amber-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
              :class="{ 'text-amber-600 bg-amber-50': $route.path === '/dashboard' }"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
              </svg>
              儀表板
            </router-link>
            
            <router-link
              to="/user"
              @click="mobileMenuOpen = false"
              class="text-gray-700 hover:text-amber-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
              :class="{ 'text-amber-600 bg-amber-50': $route.path === '/user' }"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
              使用者管理
            </router-link>
            
            <router-link
              to="/customer"
              @click="mobileMenuOpen = false"
              class="text-gray-700 hover:text-amber-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
              :class="{ 'text-amber-600 bg-amber-50': $route.path === '/customer' }"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
              客戶資料
            </router-link>
            
            <router-link
              to="/announcement"
              @click="mobileMenuOpen = false"
              class="text-gray-700 hover:text-amber-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
              :class="{ 'text-amber-600 bg-amber-50': $route.path === '/announcement' }"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
              公告欄
            </router-link>

            <!-- 後台管理 手機版 -->
            <div class="border-t border-gray-200 my-2"></div>
            <div class="text-sm font-medium text-gray-500 px-3 py-2">後台管理</div>
            <router-link to="/user" @click="mobileMenuOpen = false" class="text-gray-700 hover:text-amber-600 block px-6 py-2 rounded-md text-base font-medium transition-colors duration-200" :class="{ 'text-amber-600 bg-amber-50': $route.path === '/user' }">
              使用者管理
            </router-link>
            <div class="px-6 py-2 text-base text-gray-400">使用者操作紀錄（開發中）</div>

            <!-- 資料設定 手機版 -->
            <div class="border-t border-gray-200 my-2"></div>
            <div class="text-sm font-medium text-gray-500 px-3 py-2">資料設定</div>
            <router-link to="/customer" @click="mobileMenuOpen = false" class="text-gray-700 hover:text-amber-600 block px-6 py-2 rounded-md text-base font-medium transition-colors duration-200" :class="{ 'text-amber-600 bg-amber-50': $route.path === '/customer' }">
              客戶管理
            </router-link>
            <div class="px-6 py-2 text-base text-gray-400">清運業者管理（開發中）</div>
            <router-link to="/announcement" @click="mobileMenuOpen = false" class="text-gray-700 hover:text-amber-600 block px-6 py-2 rounded-md text-base font-medium transition-colors duration-200" :class="{ 'text-amber-600 bg-amber-50': $route.path === '/announcement' }">
              公告管理
            </router-link>

            <!-- 土單系統手機版 -->
            <div class="border-t border-gray-200 my-2"></div>
            <div class="text-sm font-medium text-gray-500 px-3 py-2">土單系統</div>
            
            <router-link
              to="/case"
              @click="mobileMenuOpen = false"
              class="text-gray-700 hover:text-amber-600 block px-6 py-2 rounded-md text-base font-medium transition-colors duration-200"
              :class="{ 'text-amber-600 bg-amber-50': $route.path === '/case' }"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"/>
              </svg>
              案件管理
            </router-link>
            
            <router-link
              to="/earth-data"
              @click="mobileMenuOpen = false"
              class="text-gray-700 hover:text-amber-600 block px-6 py-2 rounded-md text-base font-medium transition-colors duration-200"
              :class="{ 'text-amber-600 bg-amber-50': $route.path === '/earth-data' }"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
              </svg>
              土單資料管理
            </router-link>
            
            <router-link
              to="/earth-recycle"
              @click="mobileMenuOpen = false"
              class="text-gray-700 hover:text-amber-600 block px-6 py-2 rounded-md text-base font-medium transition-colors duration-200"
              :class="{ 'text-amber-600 bg-amber-50': $route.path === '/earth-recycle' }"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
              </svg>
              土單回收作業
            </router-link>
            
            <router-link
              to="/earth-statistics"
              @click="mobileMenuOpen = false"
              class="text-gray-700 hover:text-amber-600 block px-6 py-2 rounded-md text-base font-medium transition-colors duration-200"
              :class="{ 'text-amber-600 bg-amber-50': $route.path === '/earth-statistics' }"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
              </svg>
              土單使用統計表
            </router-link>

            <!-- 分隔線 -->
            <div class="border-t border-gray-200 my-2"></div>

            <!-- 修改密碼 -->
            <button
              @click="handleChangePassword"
              class="w-full text-left text-gray-700 hover:text-amber-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
              </svg>
              修改密碼
            </button>

            <!-- 登出 -->
            <button
              @click="handleLogout"
              :disabled="isLoading"
              class="w-full text-left text-red-600 hover:text-red-700 disabled:opacity-50 disabled:cursor-not-allowed block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
              </svg>
              登出
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="relative">
      <router-view />
    </main>
    
    <!-- Toast 通知 -->
    <Toast />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth.js';
import Toast from './Toast.vue';

const router = useRouter();
const { user, logout, isLoading } = useAuth();

// 響應式資料
const mobileMenuOpen = ref(false);
const userMenuOpen = ref(false);
const adminMenuOpen = ref(false);
const dataMenuOpen = ref(false);
const earthSystemMenuOpen = ref(false);
let adminMenuTimeout = null;
let dataMenuTimeout = null;
let earthSystemMenuTimeout = null;

// 計算土單系統是否為活躍狀態
const isEarthSystemActive = computed(() => {
  const earthSystemPaths = ['/case', '/earth-data', '/earth-recycle', '/earth-statistics'];
  return earthSystemPaths.includes(router.currentRoute.value.path);
});

const isAdminActive = computed(() => {
  const adminPaths = ['/user', '/user-logs'];
  return adminPaths.includes(router.currentRoute.value.path);
});

const isDataActive = computed(() => {
  const dataPaths = ['/customer', '/cleaner', '/announcement'];
  return dataPaths.includes(router.currentRoute.value.path);
});

// 方法
const handleLogout = async () => {
  try {
    await logout();
    userMenuOpen.value = false;
    // logout 函數內部已經會重導向到登入頁，所以這裡不需要額外的 router.push
  } catch (error) {
    console.error('登出失敗:', error);
    // 即使登出失敗，也要關閉選單
    userMenuOpen.value = false;
  }
};

const handleChangePassword = () => {
  userMenuOpen.value = false;
  // TODO: 實作修改密碼功能
  alert('修改密碼功能待開發');
};

// 土單系統選單控制
const showAdminMenu = () => {
  if (adminMenuTimeout) { clearTimeout(adminMenuTimeout); adminMenuTimeout = null; }
  adminMenuOpen.value = true;
};
const hideAdminMenu = () => {
  adminMenuTimeout = setTimeout(() => { adminMenuOpen.value = false; }, 150);
};

const showDataMenu = () => {
  if (dataMenuTimeout) { clearTimeout(dataMenuTimeout); dataMenuTimeout = null; }
  dataMenuOpen.value = true;
};
const hideDataMenu = () => {
  dataMenuTimeout = setTimeout(() => { dataMenuOpen.value = false; }, 150);
};

const showEarthSystemMenu = () => {
  if (earthSystemMenuTimeout) {
    clearTimeout(earthSystemMenuTimeout);
    earthSystemMenuTimeout = null;
  }
  earthSystemMenuOpen.value = true;
};

const hideEarthSystemMenu = () => {
  earthSystemMenuTimeout = setTimeout(() => {
    earthSystemMenuOpen.value = false;
  }, 150); // 150ms 延遲，讓使用者有時間移動到子選單
};

// 點擊外部關閉選單
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    userMenuOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});
</script>
