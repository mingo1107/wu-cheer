/**
 * 核銷人員認證相關的 Vue 3 Composable
 */
import { ref, computed, readonly } from 'vue';
import { useRouter } from 'vue-router';
import verifierAccountAPI from '../../api/verifierPlatform/account.js';

// 全域狀態
const verifier = ref(null);
const isLoading = ref(false);
const error = ref(null);

// 初始化時從 localStorage 載入核銷人員資料
const initializeAuth = () => {
    const savedVerifier = localStorage.getItem('verifier_user');
    const savedToken = localStorage.getItem('verifier_token');
    
    if (savedVerifier && savedToken) {
        try {
            verifier.value = JSON.parse(savedVerifier);
        } catch (err) {
            console.error('解析核銷人員資料失敗:', err);
            localStorage.removeItem('verifier_user');
            localStorage.removeItem('verifier_token');
        }
    } else if (savedVerifier && !savedToken) {
        // 如果只有核銷人員資料但沒有 token，清除資料
        localStorage.removeItem('verifier_user');
        verifier.value = null;
    }
};

// 立即初始化
initializeAuth();

export function useVerifierAuth() {
    const router = useRouter();
    
    /**
     * 登入
     */
    const login = async (account, password) => {
        isLoading.value = true;
        error.value = null;
        
        try {
            const response = await verifierAccountAPI.login(account, password);
            
            if (response.status) {
                verifier.value = response.data.verifier;
                // 儲存核銷人員資料和 JWT token 到 localStorage
                localStorage.setItem('verifier_user', JSON.stringify(response.data.verifier));
                localStorage.setItem('verifier_token', response.data.token);
                // 登入成功後使用 Vue Router 重導向到核銷平台首頁
                router.push('/verifier/dashboard');
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
                await verifierAccountAPI.logout();
            } catch (err) {
                // 即使後端登出失敗，也要清除前端資料
                console.warn('後端登出失敗，但繼續清除前端資料:', err.message);
            }
            
            // 清除前端狀態
            verifier.value = null;
            localStorage.removeItem('verifier_user');
            localStorage.removeItem('verifier_token');
            
            // 使用 Vue Router 重導向到登入頁面
            router.push('/verifier/login');
            
            return { success: true, message: '登出成功' };
        } catch (err) {
            error.value = err.message || '登出失敗';
            return { success: false, message: error.value };
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * 取得目前核銷人員資訊
     */
    const getCurrentVerifier = async () => {
        isLoading.value = true;
        error.value = null;
        
        try {
            const response = await verifierAccountAPI.getCurrentVerifier();
            
            if (response.status) {
                verifier.value = response.data.verifier;
                return { success: true, data: response.data };
            } else {
                error.value = response.message;
                return { success: false, message: response.message };
            }
        } catch (err) {
            error.value = err.message || '取得核銷人員資訊失敗';
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
    const isAuthenticated = computed(() => !!verifier.value);
    const verifierName = computed(() => verifier.value?.name || '');
    const verifierAccount = computed(() => verifier.value?.account || '');

    return {
        // 狀態
        verifier: readonly(verifier),
        isLoading: readonly(isLoading),
        error: readonly(error),
        
        // 計算屬性
        isAuthenticated,
        verifierName,
        verifierAccount,
        
        // 方法
        login,
        logout,
        getCurrentVerifier,
        clearError
    };
}

