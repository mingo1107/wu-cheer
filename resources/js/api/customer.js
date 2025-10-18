import api from './base.js';

class CustomerAPI {
  async getCustomers(filters = {}) {
    return api.get('/customers', filters);
  }

  async createCustomer(payload) {
    return api.post('/customers', payload);
  }

  async updateCustomer(id, payload) {
    return api.put(`/customers/${id}`, payload);
  }

  async deleteCustomer(id) {
    return api.delete(`/customers/${id}`);
  }
}

const customerAPI = new CustomerAPI();
export default customerAPI;
export { CustomerAPI };
