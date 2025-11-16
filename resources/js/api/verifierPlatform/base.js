/**
 * Verifier Platform API 呼叫工具 - Vue 3 版本
 * 基於 BaseAPI，使用 verifier_token 和 verifier_user 作為儲存 key
 */
import { BaseAPI } from '../base.js';

// 建立 Verifier Platform API 實例，使用 verifier_token 和 verifier_user
const verifierApi = new BaseAPI({
    baseURL: '/api',
    tokenKey: 'verifier_token',
    userKey: 'verifier_user'
});

export default verifierApi;
export { verifierApi };

