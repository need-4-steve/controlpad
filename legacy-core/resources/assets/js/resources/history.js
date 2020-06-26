const Request = require('../resources/requestHandler.js')

module.exports = {
  index: function (params) {
    return Request.get('/api/v1/history', params)
  }
}
