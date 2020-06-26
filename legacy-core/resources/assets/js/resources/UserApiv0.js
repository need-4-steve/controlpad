const Request = require('../resources/requestHandler.js')
const config = require('env').apis
const apiUrl = config.users

module.exports = {
  get (pid, params) {
    return Request.get(apiUrl + 'users/' + pid, params)
  },
  getByEmail (email) {
    return Request.get(apiUrl + 'users/email/' + email)
  },
  getByIdAndEmail (id, email) {
    return Request.get(apiUrl + 'user-by-id-email', {id: id, email: email})
  },
  search (params) {
    return Request.get(apiUrl + 'customers/', params)
  },
  getCardToken (pid) {
    return Request.get(apiUrl + 'users/' + pid + '/card-token')
  },
  saveSettings (pid, params) {
    return Request.patch(apiUrl + 'settings/' + pid, params)
  },
  getSetting (pid, settingKey) {
    return Request.get(apiUrl + 'settings/' + pid + '/' + settingKey)
  }
}
