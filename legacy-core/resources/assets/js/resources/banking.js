const Request = require('../resources/requestHandler.js')

module.exports = {
  cardUpdate: function (card) {
    return Request.post('/api/v1/user-settings/card/card-token', card)
  },
  bankUpdate: function (bank) {
    return Request.put('/api/v1/bank/update', bank)
  },
  depositVerify: function (deposits) {
    return Request.put('/api/v1/bank/verify', deposits)
  },
  bankInfo: function (id, params) {
    return Request.get('/api/v1/bank/show/' + id, params)
  },
  checkBillingForCardUpdate: function (params) {
    return Request.get('/api/v1/bank/check-billing', params)
  }
}
