/**
 * 使用者相關 API 服務 - Vue 3 版本
 */
import api from './base.js';

class UserAPI {
    /**
     * 使用者登入
     * @param {string} email - 電子郵件
     * @param {string} password - 密碼
     */
    async login(email, password) {
        return api.post('/account/login', { email, password });
    }

    /**
     * 使用者登出
     */
    async logout() {
        return api.post('/account/logout');
    }

    /**
     * 取得目前使用者資訊
     */
    async getCurrentUser() {
        return api.get('/account/me');
    }

    /**
     * 更新使用者資料
     * @param {object} userData - 使用者資料
     */
    async updateProfile(userData) {
        return api.put('/account/me', userData);
    }

    /**
     * 變更密碼
     * @param {string} currentPassword - 目前密碼
     * @param {string} newPassword - 新密碼
     */
    async changePassword(currentPassword, newPassword) {
        return api.post('/account/change-password', {
            current_password: currentPassword,
            new_password: newPassword
        });
    }

    /**
     * 取得使用者列表
     * @param {object} filters - 篩選條件
     */
    async getUsers(filters = {}) {
        return api.get('/users', filters);
    }

    /**
     * 建立新使用者
     * @param {object} userData - 使用者資料
     */
    async createUser(userData) {
        return api.post('/users', userData);
    }

    /**
     * 取得特定使用者資訊
     * @param {number} id - 使用者 ID
     */
    async getUser(id) {
        return api.get(`/users/${id}`);
    }

    /**
     * 更新使用者
     * @param {number} id - 使用者 ID
     * @param {object} userData - 使用者資料
     */
    async updateUser(id, userData) {
        return api.put(`/users/${id}`, userData);
    }

    /**
     * 刪除使用者
     * @param {number} id - 使用者 ID
     */
    async deleteUser(id) {
        return api.delete(`/users/${id}`);
    }
}

// 建立實例並匯出
const userAPI = new UserAPI();

export default userAPI;
export { UserAPI };
