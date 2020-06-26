const Request = require('../resources/requestHandler.js')

module.exports = {
  all: function (params) {
    return Request.get('/api/v1/orders', params)
  },
  byFulfillmentStatus: function (params) {
    return Request.get('/api/v1/orders/all', params)
  },
  show: function (receiptId) {
    return Request.get('/api/v1/orders/show/' + receiptId)
  },
  rep: function (params) {
    return Request.get('/api/v1/orders/by-rep', params)
  },
  updateStatus: function (params) {
    return Request.post('/api/v1/orders/update-status', params)
  },
  getStatus: function () {
    return Request.get('/api/v1/order-status')
  },
  createStandardOrder (params) {
    return Request.post('/api/v1/orders/create', params)
  },
  createCustomOrder (params) {
    return Request.post('/api/v1/orders/create-custom', params)
  },
  transferInventory (receiptId) {
    return Request.post('/api/v1/orders/transfer-inventory/' + receiptId)
  }
}
