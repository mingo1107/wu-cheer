import api from './base.js';

class EarthDataAPI {
  async getDefaults() { return api.get('/earth-data/0'); }
  async list(params = {}) { return api.get('/earth-data', params); }
  async get(id) { return api.get(`/earth-data/${id}`); }
  async details(id) { return api.get(`/earth-data/${id}/details`); }
  async create(payload) { return api.post('/earth-data', payload); }
  async update(id, payload) { return api.put(`/earth-data/${id}`, payload); }
  async delete(id) { return api.delete(`/earth-data/${id}`); }
  async adjustDetails(id, payload) { return api.post(`/earth-data/${id}/details/adjust`, payload); }
}

const earthDataAPI = new EarthDataAPI();
export default earthDataAPI;
export { EarthDataAPI };
