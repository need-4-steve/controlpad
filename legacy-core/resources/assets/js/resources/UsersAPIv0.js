const Request = require('../resources/requestHandler.js')
const config = require('env').apis
const apiUrl = config.users
module.exports = {
  index (params) {
    return Request.get(apiUrl + 'users', params)
  },
  get (params, pid) {
    return Request.get(apiUrl + 'users/' + pid, params)
  },
  getById (params, id) {
    return Request.get(apiUrl + 'users/id/' + id, params)
  },
  create (params) {
    return Request.post(apiUrl + 'users', params)
  },
  getCardToken (pid) {
    return Request.get(apiUrl + 'users/' + pid + '/card-token')
  }
}
