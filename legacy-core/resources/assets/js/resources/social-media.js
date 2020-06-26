const Request = require('../resources/requestHandler.js')

module.exports = {
  connectFacebook: function () {
    return Request.get('/api/v2/oauth/associate/facebook')
  },
  disconnectFacebook: function () {
    return Request.get('/api/v2/oauth/disconnect/facebook')
  }

}
