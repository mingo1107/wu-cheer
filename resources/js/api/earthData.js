import api from './base.js';

class EarthDataAPI {
  async getDefaults() { return api.get('/earth-data/0'); }
  async list(params = {}) { return api.get('/earth-data', params); }
  async get(id) { return api.get(`/earth-data/${id}`); }
  async details(id, params = {}) { return api.get(`/earth-data/${id}/details`, params); }
  async usageStats(id) { return api.get(`/earth-data/${id}/usage/stats`); }
  async create(payload) { return api.post('/earth-data', payload); }
  async update(id, payload) { return api.put(`/earth-data/${id}`, payload); }
  async delete(id) { return api.delete(`/earth-data/${id}`); }
  async adjustDetails(id, payload) { return api.post(`/earth-data/${id}/details/adjust`, payload); }
  async updateDetailStatus(id, detailId, status) { return api.put(`/earth-data/${id}/details/${detailId}/status`, { status }); }
  async recycleDetails(id, count) { return api.post(`/earth-data/${id}/details/recycle`, { count }); }
  async batchUpdateStatus(id, detailIds, status) { return api.post(`/earth-data/${id}/details/batch-update-status`, { detail_ids: detailIds, status }); }
  async batchUpdateDates(id, detailIds, useStartDate, useEndDate) { return api.post(`/earth-data/${id}/details/batch-update-dates`, { detail_ids: detailIds, use_start_date: useStartDate, use_end_date: useEndDate }); }
}

const earthDataAPI = new EarthDataAPI();
export default earthDataAPI;
export { EarthDataAPI };
