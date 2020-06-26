const Request = require('../resources/requestHandler.js')

module.exports = {
  gitlabReleaseNotes (params) {
    return Request.get('/api/v1/release-notes', params)
  }
}
