const Request = require('../resources/requestHandler.js')

module.exports = {
  index (params) {
    return Request.get('/api/v1/custom-links', params)
  },
  create: function (params) {
    return Request.post('/api/v1/custom-links', params)
  },
  remove (id) {
    return Request.delete('/api/v1/custom-links/' + id)
  }
}
