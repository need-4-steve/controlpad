const Request = require('../resources/requestHandler.js')

module.exports = {
  show: function (params) {
    return Request.get('/api/v1/address/show', params)
  },
  create: function (params) {
    return Request.post('/api/v1/address/create-or-update', params)
  }
}
