const Request = require('../resources/requestHandler.js')

module.exports = {
  pay: function (params) {
    return Request.post('/api/v1/custom-order/pay', params)
  }
}
