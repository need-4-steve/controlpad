const Request = require('../resources/requestHandler.js')

module.exports = {
  getIndex: function () {
    return Request.get('/api/v1/order-status')
  },
  create: function (params) {
    return Request.post('/api/v1/order-status', params)
  },
  update: function (id, params) {
    return Request.patch('/api/v1/order-status/' + id, params)
  },
  delete: function (id) {
    return Request.delete('/api/v1/order-status/' + id)
  }
}
