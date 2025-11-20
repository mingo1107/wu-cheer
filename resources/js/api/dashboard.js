import api from './base'

export default {
  /**
   * 取得儀表板統計資料
   * @returns {Promise}
   */
  async getStats() {
    return await api.get('/dashboard/stats')
  }
}

