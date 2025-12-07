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
  },
  // 取得土質類型列表
  async soilTypes() {
    return await api.get('/common/soil-types')
  },
  // 取得米數類型列表
  async meterTypes() {
    return await api.get('/common/meter-types')
  },
}
