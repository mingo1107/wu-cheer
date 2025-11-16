<template>
  <div id="app">
    <!-- 網路狀態提示 -->
    <transition name="slide-down">
      <div
        v-if="!isOnline"
        class="fixed top-0 left-0 right-0 bg-amber-500 text-white px-4 py-3 text-center z-50 shadow-md flex items-center justify-center"
      >
        <i class="fas fa-wifi-slash mr-2"></i>
        <span>目前處於離線模式，部分功能可能無法使用。資料將暫存在本地，待網路恢復後自動同步。</span>
      </div>
    </transition>
    <transition name="slide-down">
      <div
        v-if="isOnline && wasOffline"
        class="fixed top-0 left-0 right-0 bg-green-500 text-white px-4 py-3 text-center z-50 shadow-md flex items-center justify-center"
      >
        <i class="fas fa-wifi mr-2"></i>
        <span>網路連線已恢復，正在同步離線資料...</span>
      </div>
    </transition>
    
    <!-- 路由視圖容器 -->
    <router-view />
  </div>
</template>

<script setup>
import { useNetworkStatus } from '../composables/useNetworkStatus.js';

// 網路狀態檢測
const { isOnline, wasOffline } = useNetworkStatus();
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
</style>