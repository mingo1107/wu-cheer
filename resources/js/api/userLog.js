/**
 * 使用者操作記錄相關 API 服務
 */
import api from './base.js';

class UserLogAPI {
    /**
     * 取得使用者操作記錄列表
     * @param {object} filters - 篩選條件
     */
    async getLogs(filters = {}) {
        return api.get('/user-logs', filters);
    }
}

// 建立實例並匯出
const userLogAPI = new UserLogAPI();

export default userLogAPI;
export { UserLogAPI };

