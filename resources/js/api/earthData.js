import api from './base.js';

class EarthDataAPI {
  async getDefaults() {
    return api.get('/earth-data/0');
  }
  async list(params = {}) {
    return api.get('/earth-data', params);
  }
  async bulkUpsert(rows = []) {
    return api.post('/earth-data/bulk-upsert', { rows });
  }
  async bulkDelete(ids = []) {
    return api.post('/earth-data/bulk-delete', { ids });
  }
}

const earthDataAPI = new EarthDataAPI();
export default earthDataAPI;
export { EarthDataAPI };
