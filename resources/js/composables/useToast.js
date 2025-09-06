/**
 * Toast 通知 Composable
 */
import { ref, readonly } from 'vue';

// 全域狀態
const toasts = ref([]);
let toastId = 0;

export function useToast() {
    /**
     * 顯示 toast 通知
     * @param {string} message - 訊息內容
     * @param {string} type - 類型: 'success', 'error', 'warning', 'info'
     * @param {number} duration - 顯示時間（毫秒），0 表示不自動關閉
     */
    const showToast = (message, type = 'info', duration = 3000) => {
        const id = ++toastId;
        const toast = {
            id,
            message,
            type,
            duration,
            visible: true
        };
        
        toasts.value.push(toast);
        
        // 自動關閉
        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }
        
        return id;
    };

    /**
     * 移除 toast
     * @param {number} id - toast ID
     */
    const removeToast = (id) => {
        const index = toasts.value.findIndex(toast => toast.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    /**
     * 清除所有 toast
     */
    const clearAllToasts = () => {
        toasts.value = [];
    };

    // 便利方法
    const success = (message, duration = 3000) => showToast(message, 'success', duration);
    const error = (message, duration = 5000) => showToast(message, 'error', duration);
    const warning = (message, duration = 4000) => showToast(message, 'warning', duration);
    const info = (message, duration = 3000) => showToast(message, 'info', duration);

    return {
        toasts: readonly(toasts),
        showToast,
        removeToast,
        clearAllToasts,
        success,
        error,
        warning,
        info
    };
}
