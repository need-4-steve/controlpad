const Request = require('../resources/requestHandler.js')

module.exports = {
  accounts: function (params) {
    return Request.get('/api/v1/direct-deposit/account-index', params)
  },
  accountShow: function (params) {
    return Request.get('/api/v1/direct-deposit/details', params)
  },
  batches: function (params) {
    return Request.get('/api/v1/direct-deposit/batch-index', params)
  },
  batchIdDetails: function (id) {
    return Request.get('/api/v1/direct-deposit/batch-id/' + id)
  },
  batchDetails: function (params) {
    return Request.get('/api/v1/direct-deposit/detail', params)
  },
  downloadNacha: function (id) {
    return Request.get('/api/v1/direct-deposit/download/' + id)
  },
  markPaid: function (id) {
    return Request.post('/api/v1/direct-deposit/batch-submit/' + id)
  },
  paymentList: function (params) {
    return Request.get('/api/v1/payment/paymentLists', params)
  },
  submitPayment: function (id) {
    return Request.get('/api/v1/payment/submit/' + id)
  },
  paymentBatchId: function (id, params) {
    return Request.get('/api/v1/payment/details/' + id, params)
  },
  getValidations: function (params) {
    return Request.get('/api/v1/direct-deposit/validations', params)
  }
}
