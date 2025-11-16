/**
 * 離線資料儲存工具（IndexedDB 版本）
 * 使用 IndexedDB 儲存離線狀態下的核銷資料
 * 
 * 注意：如果資料量不大（< 5MB），建議使用 localStorage 版本（offlineStorageSimple.js）
 * 此版本適合需要大量資料儲存或複雜查詢的場景
 */

const DB_NAME = 'verifier-offline-db';
const DB_VERSION = 1;
const STORE_NAME = 'verify-records';

let db = null;

/**
 * 開啟 IndexedDB 資料庫
 */
async function openDB() {
    if (db) {
        return db;
    }

    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = () => {
            reject(new Error('無法開啟 IndexedDB'));
        };

        request.onsuccess = () => {
            db = request.result;
            resolve(db);
        };

        request.onupgradeneeded = (event) => {
            const database = event.target.result;
            
            // 建立物件儲存區（如果不存在）
            if (!database.objectStoreNames.contains(STORE_NAME)) {
                const objectStore = database.createObjectStore(STORE_NAME, {
                    keyPath: 'id',
                    autoIncrement: true
                });
                
                // 建立索引
                objectStore.createIndex('barcode', 'barcode', { unique: false });
                objectStore.createIndex('status', 'status', { unique: false });
                objectStore.createIndex('created_at', 'created_at', { unique: false });
            }
        };
    });
}

/**
 * 儲存核銷記錄到離線資料庫
 * @param {Object} record - 核銷記錄物件
 * @returns {Promise<number>} 記錄 ID
 */
export async function saveOfflineRecord(record) {
    const database = await openDB();
    
    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readwrite');
        const objectStore = transaction.objectStore(STORE_NAME);
        
        const data = {
            ...record,
            created_at: new Date().toISOString(),
            status: 'pending' // pending, synced, failed
        };
        
        const request = objectStore.add(data);
        
        request.onsuccess = () => {
            resolve(request.result);
        };
        
        request.onerror = () => {
            reject(new Error('儲存離線記錄失敗'));
        };
    });
}

/**
 * 取得所有待同步的記錄
 * @returns {Promise<Array>} 待同步記錄陣列
 */
export async function getPendingRecords() {
    const database = await openDB();
    
    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readonly');
        const objectStore = transaction.objectStore(STORE_NAME);
        const index = objectStore.index('status');
        const request = index.getAll('pending');
        
        request.onsuccess = () => {
            resolve(request.result || []);
        };
        
        request.onerror = () => {
            reject(new Error('取得待同步記錄失敗'));
        };
    });
}

/**
 * 取得所有記錄（包含已同步）
 * @returns {Promise<Array>} 所有記錄陣列
 */
export async function getAllRecords() {
    const database = await openDB();
    
    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readonly');
        const objectStore = transaction.objectStore(STORE_NAME);
        const request = objectStore.getAll();
        
        request.onsuccess = () => {
            resolve(request.result || []);
        };
        
        request.onerror = () => {
            reject(new Error('取得所有記錄失敗'));
        };
    });
}

/**
 * 更新記錄狀態
 * @param {number} id - 記錄 ID
 * @param {string} status - 新狀態 (pending, synced, failed)
 * @returns {Promise<void>}
 */
export async function updateRecordStatus(id, status) {
    const database = await openDB();
    
    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readwrite');
        const objectStore = transaction.objectStore(STORE_NAME);
        const request = objectStore.get(id);
        
        request.onsuccess = () => {
            const record = request.result;
            if (record) {
                record.status = status;
                record.updated_at = new Date().toISOString();
                
                const updateRequest = objectStore.put(record);
                updateRequest.onsuccess = () => resolve();
                updateRequest.onerror = () => reject(new Error('更新記錄狀態失敗'));
            } else {
                reject(new Error('記錄不存在'));
            }
        };
        
        request.onerror = () => {
            reject(new Error('取得記錄失敗'));
        };
    });
}

/**
 * 刪除記錄
 * @param {number} id - 記錄 ID
 * @returns {Promise<void>}
 */
export async function deleteRecord(id) {
    const database = await openDB();
    
    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readwrite');
        const objectStore = transaction.objectStore(STORE_NAME);
        const request = objectStore.delete(id);
        
        request.onsuccess = () => {
            resolve();
        };
        
        request.onerror = () => {
            reject(new Error('刪除記錄失敗'));
        };
    });
}

/**
 * 清除所有已同步的記錄
 * @returns {Promise<number>} 清除的記錄數量
 */
export async function clearSyncedRecords() {
    const database = await openDB();
    
    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readwrite');
        const objectStore = transaction.objectStore(STORE_NAME);
        const index = objectStore.index('status');
        const request = index.getAll('synced');
        
        request.onsuccess = () => {
            const records = request.result || [];
            let deletedCount = 0;
            
            if (records.length === 0) {
                resolve(0);
                return;
            }
            
            records.forEach((record) => {
                const deleteRequest = objectStore.delete(record.id);
                deleteRequest.onsuccess = () => {
                    deletedCount++;
                    if (deletedCount === records.length) {
                        resolve(deletedCount);
                    }
                };
                deleteRequest.onerror = () => {
                    reject(new Error('清除記錄失敗'));
                };
            });
        };
        
        request.onerror = () => {
            reject(new Error('取得已同步記錄失敗'));
        };
    });
}

/**
 * 取得記錄統計資訊
 * @returns {Promise<Object>} 統計資訊
 */
export async function getRecordStats() {
    const allRecords = await getAllRecords();
    
    return {
        total: allRecords.length,
        pending: allRecords.filter(r => r.status === 'pending').length,
        synced: allRecords.filter(r => r.status === 'synced').length,
        failed: allRecords.filter(r => r.status === 'failed').length
    };
}

