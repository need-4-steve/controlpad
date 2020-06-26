const Request = require('../resources/requestHandler.js')

module.exports = {
  index: function (params) {
    return Request.get('/api/v1/announcements', params)
  },
  create: function (params) {
    return Request.post('/api/v1/announcements', params)
  },
  update: function (id, params) {
    return Request.patch('/api/v1/announcements/' + id, params)
  },
  delete: function (id) {
    return Request.delete('/api/v1/announcements/' + id)
  }
}
