/**
 * Base API 呼叫工具
 * 基於 axios 的統一 API 請求處理
 */
class BaseAPI {
    constructor() {
        this.baseURL = '/api';
        this.instance = axios.create({
            baseURL: this.baseURL,
            timeout: 10000,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        });

        this.setupInterceptors();
    }

    /**
     * 設定 axios 攔截器
     */
    setupInterceptors() {
        // 請求攔截器
        this.instance.interceptors.request.use(
            (config) => {
                // 添加 CSRF token（如果需要）
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    config.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
                }

                // 添加 loading 狀態
                this.showLoading();

                return config;
            },
            (error) => {
                this.hideLoading();
                return Promise.reject(error);
            }
        );

        // 回應攔截器
        this.instance.interceptors.response.use(
            (response) => {
                this.hideLoading();
                return response;
            },
            (error) => {
                this.hideLoading();
                this.handleError(error);
                return Promise.reject(error);
            }
        );
    }

    /**
     * 顯示載入狀態
     */
    showLoading() {
        // 可以在這裡添加全域 loading 顯示邏輯
        console.log('API 請求開始...');
    }

    /**
     * 隱藏載入狀態
     */
    hideLoading() {
        // 可以在這裡添加全域 loading 隱藏邏輯
        console.log('API 請求完成');
    }

    /**
     * 處理錯誤
     */
    handleError(error) {
        console.error('API 錯誤:', error);

        if (error.response) {
            const { status, data } = error.response;
            
            switch (status) {
                case 401:
                    console.log('未授權，需要重新登入');
                    this.redirectToLogin();
                    break;
                case 403:
                    console.log('權限不足');
                    this.showAlert('權限不足，無法執行此操作', 'error');
                    break;
                case 404:
                    console.log('API 端點不存在');
                    this.showAlert('請求的資源不存在', 'error');
                    break;
                case 422:
                    console.log('驗證錯誤:', data);
                    this.showValidationErrors(data);
                    break;
                case 500:
                    console.log('伺服器錯誤');
                    this.showAlert('伺服器錯誤，請稍後再試', 'error');
                    break;
                default:
                    this.showAlert('請求失敗，請稍後再試', 'error');
            }
        } else if (error.request) {
            console.log('網路錯誤');
            this.showAlert('網路連線錯誤，請檢查網路狀態', 'error');
        } else {
            console.log('請求設定錯誤');
            this.showAlert('請求設定錯誤', 'error');
        }
    }

    /**
     * 重導向到登入頁面
     */
    redirectToLogin() {
        // 可以在這裡添加重導向邏輯
        console.log('重導向到登入頁面');
        // window.location.href = '/login';
    }

    /**
     * 顯示警告訊息
     */
    showAlert(message, type = 'error') {
        // 嘗試使用 Toast 通知系統
        if (window.showToastNotification) {
            window.showToastNotification(message, type);
        } else {
            // 如果 Toast 系統未初始化，使用 console 記錄
            console.error('Toast 通知:', message);
            // 作為後備方案，仍然使用 alert（但應該避免）
            // alert(message);
        }
    }

    /**
     * 顯示驗證錯誤
     */
    showValidationErrors(data) {
        if (data && data.errors) {
            const errorMessages = Object.values(data.errors).flat();
            const message = errorMessages.join('、'); // 使用中文頓號分隔
            this.showAlert('驗證錯誤：' + message, 'error');
        } else if (data && data.message) {
            this.showAlert('驗證錯誤：' + data.message, 'error');
        }
    }

    /**
     * 執行 AJAX 請求
     * @param {string} method - HTTP 方法 (get, post, put, delete)
     * @param {string} url - API 端點
     * @param {object} data - 請求資料
     * @param {function} callback - 成功回調函數
     * @param {object} options - 額外選項
     */
    async doAjaxRequest(method, url, data = null, callback = null, options = {}) {
        try {
            let response;

            switch (method.toLowerCase()) {
                case 'get':
                    response = await this.instance.get(url, { 
                        params: data,
                        ...options 
                    });
                    break;
                case 'post':
                    response = await this.instance.post(url, data, options);
                    break;
                case 'put':
                    response = await this.instance.put(url, data, options);
                    break;
                case 'delete':
                    response = await this.instance.delete(url, { 
                        data: data,
                        ...options 
                    });
                    break;
                default:
                    throw new Error(`不支援的 HTTP 方法: ${method}`);
            }

            // 執行成功回調
            if (callback && typeof callback === 'function') {
                callback(response.data);
            }

            return response.data;

        } catch (error) {
            // 錯誤處理已在攔截器中處理
            throw error;
        }
    }

    /**
     * GET 請求
     */
    async get(url, data = null, callback = null, options = {}) {
        return this.doAjaxRequest('get', url, data, callback, options);
    }

    /**
     * POST 請求
     */
    async post(url, data = null, callback = null, options = {}) {
        return this.doAjaxRequest('post', url, data, callback, options);
    }

    /**
     * PUT 請求
     */
    async put(url, data = null, callback = null, options = {}) {
        return this.doAjaxRequest('put', url, data, callback, options);
    }

    /**
     * DELETE 請求
     */
    async delete(url, data = null, callback = null, options = {}) {
        return this.doAjaxRequest('delete', url, data, callback, options);
    }

    /**
     * 下載檔案
     */
    async download(url, data = null, filename = null) {
        try {
            const response = await this.instance.post(url, data, {
                responseType: 'blob'
            });

            // 建立下載連結
            const blob = new Blob([response.data]);
            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = filename || 'download';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(downloadUrl);

        } catch (error) {
            this.handleError(error);
        }
    }
}

// 建立全域實例
window.api = new BaseAPI();

// 為了向後相容，保留原有的 doAjaxRequest 方法
window.doAjaxRequest = function(method, url, data, callback, options = {}) {
    return window.api.doAjaxRequest(method, url, data, callback, options);
};

// 匯出類別（如果使用模組系統）
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BaseAPI;
}
