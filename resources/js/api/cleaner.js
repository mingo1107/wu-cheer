import api from './base.js';

class CleanerAPI {
  async getCleaner(id) {
    return api.get(`/cleaners/${id}`);
  }
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

  // 車輛管理
  async getVehicles(cleanerId) {
    return api.get(`/cleaners/${cleanerId}/vehicles`);
  }

  async createVehicle(cleanerId, payload) {
    return api.post(`/cleaners/${cleanerId}/vehicles`, payload);
  }

  async updateVehicle(cleanerId, vehicleId, payload) {
    return api.put(`/cleaners/${cleanerId}/vehicles/${vehicleId}`, payload);
  }

  async deleteVehicle(cleanerId, vehicleId) {
    return api.delete(`/cleaners/${cleanerId}/vehicles/${vehicleId}`);
  }
}

const cleanerAPI = new CleanerAPI();
export default cleanerAPI;
export { CleanerAPI };
