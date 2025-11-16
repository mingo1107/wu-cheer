import { ref, onMounted, onUnmounted } from 'vue';

/**
 * 網路狀態檢測 Composable
 * 用於檢測網路連線狀態，並提供離線/在線事件監聽
 */
export function useNetworkStatus() {
    const isOnline = ref(navigator.onLine);
    const wasOffline = ref(false);

    const updateOnlineStatus = () => {
        isOnline.value = navigator.onLine;
        
        if (!isOnline.value) {
            wasOffline.value = true;
        }
    };

    const handleOnline = () => {
        isOnline.value = true;
        // 觸發自定義事件，讓組件知道網路已恢復
        window.dispatchEvent(new CustomEvent('network-online'));
    };

    const handleOffline = () => {
        isOnline.value = false;
        wasOffline.value = true;
    };

    onMounted(() => {
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);
        // 初始狀態
        updateOnlineStatus();
    });

    onUnmounted(() => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
    });

    return {
        isOnline,
        wasOffline
    };
}

