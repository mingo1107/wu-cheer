import api from './base'

export default {
  async cleaners() {
    return await api.get('/common/cleaners')
  },
  async customers() {
    return await api.get('/common/customers')
  }
}
