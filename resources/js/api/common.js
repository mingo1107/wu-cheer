import api from './base'

export default {
  async cleaners() {
    return await api.get('/common/cleaners')
  },
  async customers() {
    return await api.get('/common/customers')
  },
  async earthDataDatalist(params = {}) {
    return await api.get('/common/earth-data/datalist', params)
  },
  async earthDataDetailStatusList() {
    return await api.get('/common/earth-data-detail/status-list')
  }
}
