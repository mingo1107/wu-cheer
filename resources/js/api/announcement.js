import api from './base.js';

class AnnouncementAPI {
  async list(params = {}) {
    return api.get('/announcements', params);
  }

  async create(payload) {
    return api.post('/announcements', payload);
  }

  async update(id, payload) {
    return api.put(`/announcements/${id}`, payload);
  }

  async remove(id) {
    return api.delete(`/announcements/${id}`);
  }

  async get(id) {
    return api.get(`/announcements/${id}`);
  }
}

const announcementAPI = new AnnouncementAPI();
export default announcementAPI;
export { AnnouncementAPI };
