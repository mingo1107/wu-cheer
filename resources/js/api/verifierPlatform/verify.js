/**
 * 核銷作業 API
 */
import verifierApi from './base.js';

class VerifierVerifyAPI {
    /**
     * 預檢查 barcode（驗證可用性並返回車號列表）
     * @param {string} barcode - Barcode
     * @returns {Promise}
     */
    async preCheck(barcode) {
        return await verifierApi.post('/verifier-platform/verify/pre-check', {
            barcode
        });
    }

    /**
     * 單筆核銷
     * @param {string} barcode - Barcode
     * @param {number|null} vehicleId - 車輛 ID
     * @param {string|null} driverName - 司機名字
     * @returns {Promise}
     */
    async verify(barcode, vehicleId = null, driverName = null) {
        return await verifierApi.post('/verifier-platform/verify', {
            barcode,
            vehicle_id: vehicleId,
            driver_name: driverName
        });
    }

    /**
     * 批量核銷
     * @param {Array<string>} barcodes - Barcode 陣列
     * @returns {Promise}
     */
    async batchVerify(barcodes) {
        return await verifierApi.post('/verifier-platform/verify/batch', {
            barcodes
        });
    }

    /**
     * 取得核銷統計
     * @returns {Promise}
     */
    async getStats() {
        return await verifierApi.get('/verifier-platform/verify/stats');
    }
}

export default new VerifierVerifyAPI();

