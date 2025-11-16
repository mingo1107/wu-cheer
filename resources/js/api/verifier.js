/**
 * 核銷人員相關 API 服務
 */
import api from './base.js';

class VerifierAPI {
    /**
     * 取得核銷人員列表
     * @param {object} filters - 篩選條件
     */
    async getVerifiers(filters = {}) {
        return api.get('/verifiers', filters);
    }

    /**
     * 建立新核銷人員
     * @param {object} verifierData - 核銷人員資料
     */
    async createVerifier(verifierData) {
        return api.post('/verifiers', verifierData);
    }

    /**
     * 取得特定核銷人員資訊
     * @param {number} id - 核銷人員 ID
     */
    async getVerifier(id) {
        return api.get(`/verifiers/${id}`);
    }

    /**
     * 更新核銷人員
     * @param {number} id - 核銷人員 ID
     * @param {object} verifierData - 核銷人員資料
     */
    async updateVerifier(id, verifierData) {
        return api.put(`/verifiers/${id}`, verifierData);
    }

    /**
     * 刪除核銷人員
     * @param {number} id - 核銷人員 ID
     */
    async deleteVerifier(id) {
        return api.delete(`/verifiers/${id}`);
    }

    /**
     * 取得活躍核銷人員列表
     */
    async getActiveVerifiers() {
        return api.get('/verifiers/active/list');
    }

    /**
     * 取得核銷人員統計資料
     */
    async getVerifierStats() {
        return api.get('/verifiers/stats/overview');
    }
}

// 建立實例並匯出
const verifierAPI = new VerifierAPI();

export default verifierAPI;
export { VerifierAPI };

