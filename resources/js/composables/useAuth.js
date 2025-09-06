/**
 * 認證相關的 Vue 3 Composable
 */
import { ref, computed, readonly } from 'vue';
import { useRouter } from 'vue-router';
import userAPI from '../api/user.js';

// 全域狀態
const user = ref(null);
const isLoading = ref(false);
const error = ref(null);

// 初始化時從 localStorage 載入使用者資料
const initializeAuth = () => {
    const savedUser = localStorage.getItem('user');
    const savedToken = localStorage.getItem('token');
    
    if (savedUser && savedToken) {
        try {
            user.value = JSON.parse(savedUser);
        } catch (err) {
            console.error('解析使用者資料失敗:', err);
            localStorage.removeItem('user');
            localStorage.removeItem('token');
        }
    } else if (savedUser && !savedToken) {
        // 如果只有使用者資料但沒有 token，清除資料
        localStorage.removeItem('user');
        user.value = null;
    }
};

// 立即初始化
initializeAuth();

export function useAuth() {
    const router = useRouter();
    
    /**
     * 登入
     */
    const login = async (email, password) => {
        isLoading.value = true;
        error.value = null;
        
        try {
            const response = await userAPI.login(email, password);
            
            if (response.status) {
                user.value = response.data.user;
                // 儲存使用者資料和 JWT token 到 localStorage
                localStorage.setItem('user', JSON.stringify(response.data.user));
                localStorage.setItem('token', response.data.token);
                // 登入成功後使用 Vue Router 重導向到儀表板
                router.push('/dashboard');
                return { success: true, data: response.data };
            } else {
                error.value = response.message;
                return { success: false, message: response.message };
            }
        } catch (err) {
            error.value = err.message || '登入失敗';
            return { success: false, message: error.value };
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * 登出
     */
    const logout = async () => {
        isLoading.value = true;
        error.value = null;
        
        try {
            // 嘗試呼叫後端登出 API
            try {
                await userAPI.logout();
            } catch (err) {
                // 即使後端登出失敗，也要清除前端資料
                console.warn('後端登出失敗，但繼續清除前端資料:', err.message);
            }
            
            // 清除前端狀態
            user.value = null;
            localStorage.removeItem('user');
            localStorage.removeItem('token');
            
            // 使用 Vue Router 重導向到登入頁面
            router.push('/login');
            
            return { success: true, message: '登出成功' };
        } catch (err) {
            error.value = err.message || '登出失敗';
            return { success: false, message: error.value };
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * 取得目前使用者資訊
     */
    const getCurrentUser = async () => {
        isLoading.value = true;
        error.value = null;
        
        try {
            const response = await userAPI.getCurrentUser();
            
            if (response.status) {
                user.value = response.data.user;
                return { success: true, data: response.data };
            } else {
                error.value = response.message;
                return { success: false, message: response.message };
            }
        } catch (err) {
            error.value = err.message || '取得使用者資訊失敗';
            return { success: false, message: error.value };
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * 更新使用者資料
     */
    const updateProfile = async (data) => {
        isLoading.value = true;
        error.value = null;
        
        try {
            const response = await userAPI.updateProfile(data);
            
            if (response.status) {
                user.value = response.data.user;
                return { success: true, data: response.data };
            } else {
                error.value = response.message;
                return { success: false, message: response.message };
            }
        } catch (err) {
            error.value = err.message || '更新失敗';
            return { success: false, message: error.value };
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * 變更密碼
     */
    const changePassword = async (currentPassword, newPassword) => {
        isLoading.value = true;
        error.value = null;
        
        try {
            const response = await userAPI.changePassword(currentPassword, newPassword);
            
            if (response.status) {
                return { success: true, data: response.data };
            } else {
                error.value = response.message;
                return { success: false, message: response.message };
            }
        } catch (err) {
            error.value = err.message || '密碼變更失敗';
            return { success: false, message: error.value };
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * 清除錯誤
     */
    const clearError = () => {
        error.value = null;
    };

    // 計算屬性
    const isAuthenticated = computed(() => !!user.value);
    const userName = computed(() => user.value?.name || '');
    const userEmail = computed(() => user.value?.email || '');

    return {
        // 狀態
        user: readonly(user),
        isLoading: readonly(isLoading),
        error: readonly(error),
        
        // 計算屬性
        isAuthenticated,
        userName,
        userEmail,
        
        // 方法
        login,
        logout,
        getCurrentUser,
        updateProfile,
        changePassword,
        clearError
    };
}
