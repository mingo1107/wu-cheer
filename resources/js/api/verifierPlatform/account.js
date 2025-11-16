/**
 * 核銷人員認證 API
 */
import verifierApi from './base.js';

class VerifierAccountAPI {
    /**
     * 核銷人員登入
     * @param {string} account - 帳號
     * @param {string} password - 密碼
     * @returns {Promise}
     */
    async login(account, password) {
        return await verifierApi.post('/verifier-platform/account/login', {
            account,
            password
        });
    }

    /**
     * 核銷人員登出
     * @returns {Promise}
     */
    async logout() {
        return await verifierApi.post('/verifier-platform/account/logout');
    }

    /**
     * 取得目前核銷人員資訊
     * @returns {Promise}
     */
    async getCurrentVerifier() {
        return await verifierApi.get('/verifier-platform/account/me');
    }
}

export default new VerifierAccountAPI();

