/**
 * Base API 呼叫工具 - Vue 3 版本
 * 基於 axios 的統一 API 請求處理
 */
import axios from 'axios';

class BaseAPI {
    constructor(options = {}) {
        this.baseURL = options.baseURL || '/api';
        this.tokenKey = options.tokenKey || 'token'; // 預設使用 'token'
        this.userKey = options.userKey || 'user'; // 預設使用 'user'
        
        this.instance = axios.create({
            baseURL: this.baseURL,
            timeout: 10000,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        });

        // 路由跳轉回調函數
        this.redirectCallback = null;

        this.setupInterceptors();
    }

    /**
     * 設定路由跳轉回調函數
     * @param {Function} callback - 路由跳轉回調函數
     */
    setRedirectCallback(callback) {
        this.redirectCallback = callback;
    }

    /**
     * 設定 axios 攔截器
     */
    setupInterceptors() {
        // 請求攔截器
        this.instance.interceptors.request.use(
            (config) => {
                // 添加 JWT token（使用配置的 token key）
                const token = localStorage.getItem(this.tokenKey);
                if (token) {
                    config.headers['Authorization'] = `Bearer ${token}`;
                }

                return config;
            },
            (error) => {
                return Promise.reject(error);
            }
        );

        // 回應攔截器
        this.instance.interceptors.response.use(
            (response) => {
                return response;
            },
            (error) => {
                this.handleError(error);
                return Promise.reject(error);
            }
        );
    }

    /**
     * 處理錯誤
     */
    handleError(error) {
        console.error('API 錯誤:', error);

        if (error.response) {
            const { status, data } = error.response;
            console.log('API 錯誤狀態:', status, '資料:', data);
            console.log('請求 URL:', error.config?.url);
            console.log('請求方法:', error.config?.method);
            console.log('請求 headers:', error.config?.headers);
            
            switch (status) {
                case 401:
                    console.log('未授權，需要重新登入');
                    console.log(`localStorage ${this.userKey}:`, localStorage.getItem(this.userKey));
                    // 清除本地儲存的使用者資料和 token（使用配置的 key）
                    localStorage.removeItem(this.userKey);
                    localStorage.removeItem(this.tokenKey);
                    // 使用回調函數進行路由跳轉
                    if (this.redirectCallback) {
                        // 根據 token key 決定跳轉路徑
                        const loginPath = this.tokenKey === 'verifier_token' ? '/verifier/login' : '/login';
                        this.redirectCallback(loginPath);
                    }
                    break;
                case 403:
                    console.log('權限不足');
                    break;
                case 404:
                    console.log('API 端點不存在');
                    break;
                case 422:
                    console.log('驗證錯誤:', data);
                    break;
                case 500:
                    console.log('伺服器錯誤');
                    break;
                default:
                    console.log('請求失敗');
            }
        } else if (error.request) {
            console.log('網路錯誤');
        } else {
            console.log('請求設定錯誤');
        }
    }

    /**
     * 重導向到登入頁面
     */
    redirectToLogin() {
        console.log('重導向到登入頁面');
        // 清除本地儲存的使用者資料和 token（使用配置的 key）
        localStorage.removeItem(this.userKey);
        localStorage.removeItem(this.tokenKey);
        // 使用回調函數進行路由跳轉
        if (this.redirectCallback) {
            // 根據 token key 決定登入頁面路徑
            const loginPath = this.tokenKey === 'verifier_token' ? '/verifier/login' : '/login';
            this.redirectCallback(loginPath);
        }
    }

    /**
     * 執行 AJAX 請求
     */
    async doAjaxRequest(method, url, data = null, options = {}) {
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

            return response.data;

        } catch (error) {
            throw error;
        }
    }

    /**
     * GET 請求
     */
    async get(url, data = null, options = {}) {
        return this.doAjaxRequest('get', url, data, options);
    }

    /**
     * POST 請求
     */
    async post(url, data = null, options = {}) {
        return this.doAjaxRequest('post', url, data, options);
    }

    /**
     * PUT 請求
     */
    async put(url, data = null, options = {}) {
        return this.doAjaxRequest('put', url, data, options);
    }

    /**
     * DELETE 請求
     */
    async delete(url, data = null, options = {}) {
        return this.doAjaxRequest('delete', url, data, options);
    }
}

// 建立實例並匯出
const api = new BaseAPI();

export default api;
export { BaseAPI };