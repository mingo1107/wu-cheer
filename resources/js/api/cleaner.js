import api from './base.js';

class CleanerAPI {
  async getCleaners(filters = {}) {
    return api.get('/cleaners', filters);
  }

  async createCleaner(payload) {
    return api.post('/cleaners', payload);
  }

  async updateCleaner(id, payload) {
    return api.put(`/cleaners/${id}`, payload);
  }

  async deleteCleaner(id) {
    return api.delete(`/cleaners/${id}`);
  }
}

const cleanerAPI = new CleanerAPI();
export default cleanerAPI;
export { CleanerAPI };
