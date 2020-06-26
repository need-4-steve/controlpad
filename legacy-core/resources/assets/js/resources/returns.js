const Request = require('../resources/requestHandler.js')

module.exports = {
  index: function (params) {
    return Request.get('/api/v1/returns', params)
  },
  return: function (params) {
    return Request.post('/api/v1/returns/request', params)
  },
  returned: function (orderId) {
    return Request.get('/api/v1/returns/return-orders/' + orderId)
  },
  refund: function (params) {
    return Request.post('/api/v1/return/refundRequest', params)
  },
  reason: function () {
    return Request.get('/api/v1/returns/reason')
  },
  history: function (id) {
    return Request.get('/api/v1/returnhistory/' + id)
  },
  returnedQuantity: function (params) {
    return Request.get('/api/v1/returns/quantity', params)
  },
  returnedShow: function (id) {
    return Request.get('/api/v1/returns/show/' + id)
  },
  updateStatus: function (id, params) {
    return Request.patch('/api/v1/returns/update/' + id, params)
  },
  returnStatuses: function () {
    return Request.get('/api/v1/return-statuses/all')
  }
}
