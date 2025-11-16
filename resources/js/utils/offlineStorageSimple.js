/**
 * 離線資料儲存工具（localStorage 版本）
 * 使用 localStorage 儲存離線狀態下的核銷資料
 * 更簡單、更輕量，適合資料量不大的場景
 */

const STORAGE_KEY = 'verifier_offline_records';

/**
 * 取得所有記錄
 * @returns {Array} 記錄陣列
 */
export function getAllRecords() {
    try {
        const data = localStorage.getItem(STORAGE_KEY);
        return data ? JSON.parse(data) : [];
    } catch (error) {
        console.error('讀取離線記錄失敗:', error);
        return [];
    }
}

/**
 * 儲存核銷記錄到離線儲存
 * @param {Object} record - 核銷記錄物件
 * @returns {number} 記錄 ID（時間戳）
 */
export function saveOfflineRecord(record) {
    try {
        const records = getAllRecords();
        const id = Date.now();
        
        const newRecord = {
            id,
            ...record,
            created_at: new Date().toISOString(),
            status: 'pending' // pending, synced, failed
        };
        
        records.push(newRecord);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(records));
        
        return id;
    } catch (error) {
        console.error('儲存離線記錄失敗:', error);
        throw error;
    }
}

/**
 * 取得所有待同步的記錄
 * @returns {Array} 待同步記錄陣列
 */
export function getPendingRecords() {
    const allRecords = getAllRecords();
    return allRecords.filter(record => record.status === 'pending');
}

/**
 * 更新記錄狀態
 * @param {number} id - 記錄 ID
 * @param {string} status - 新狀態 (pending, synced, failed)
 * @returns {boolean} 是否成功
 */
export function updateRecordStatus(id, status) {
    try {
        const records = getAllRecords();
        const index = records.findIndex(r => r.id === id);
        
        if (index === -1) {
            return false;
        }
        
        records[index].status = status;
        records[index].updated_at = new Date().toISOString();
        localStorage.setItem(STORAGE_KEY, JSON.stringify(records));
        
        return true;
    } catch (error) {
        console.error('更新記錄狀態失敗:', error);
        return false;
    }
}

/**
 * 刪除記錄
 * @param {number} id - 記錄 ID
 * @returns {boolean} 是否成功
 */
export function deleteRecord(id) {
    try {
        const records = getAllRecords();
        const filtered = records.filter(r => r.id !== id);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(filtered));
        return true;
    } catch (error) {
        console.error('刪除記錄失敗:', error);
        return false;
    }
}

/**
 * 清除所有已同步的記錄
 * @returns {number} 清除的記錄數量
 */
export function clearSyncedRecords() {
    try {
        const records = getAllRecords();
        const pending = records.filter(r => r.status !== 'synced');
        localStorage.setItem(STORAGE_KEY, JSON.stringify(pending));
        return records.length - pending.length;
    } catch (error) {
        console.error('清除已同步記錄失敗:', error);
        return 0;
    }
}

/**
 * 取得記錄統計資訊
 * @returns {Object} 統計資訊
 */
export function getRecordStats() {
    const allRecords = getAllRecords();
    
    return {
        total: allRecords.length,
        pending: allRecords.filter(r => r.status === 'pending').length,
        synced: allRecords.filter(r => r.status === 'synced').length,
        failed: allRecords.filter(r => r.status === 'failed').length
    };
}

/**
 * 清除所有記錄
 * @returns {void}
 */
export function clearAllRecords() {
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch (error) {
        console.error('清除所有記錄失敗:', error);
    }
}

/**
 * 檢查儲存空間
 * @returns {Object} 儲存空間資訊
 */
export function getStorageInfo() {
    try {
        const data = localStorage.getItem(STORAGE_KEY) || '';
        const size = new Blob([data]).size;
        const sizeKB = (size / 1024).toFixed(2);
        
        // localStorage 通常限制約 5-10MB
        const limit = 5 * 1024 * 1024; // 5MB
        const usage = (size / limit * 100).toFixed(2);
        
        return {
            size,
            sizeKB: `${sizeKB} KB`,
            usage: `${usage}%`,
            limit: '5MB (約)'
        };
    } catch (error) {
        console.error('取得儲存空間資訊失敗:', error);
        return {
            size: 0,
            sizeKB: '0 KB',
            usage: '0%',
            limit: '5MB (約)'
        };
    }
}

