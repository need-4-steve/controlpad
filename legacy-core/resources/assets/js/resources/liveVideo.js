const Request = require('../resources/requestHandler.js')

module.exports = {
  getUserInventory: function () {
    return Request.get('/api/v1/inventory/rep')
  },
  checkForLiveVideo: function (service) {
    return Request.get('/api/v2/live-videos/' + service + '/check')
  },
  commitVideo: function (service, params) {
    return Request.post('/api/v2/live-videos/' + service, params)
  },
  commitInventory: function (service, payload) {
    return Request.post('/api/v2/live-videos/' + service + '/inventory', payload)
  },
  showLiveVideos: function (service, id) {
    return Request.get('/api/v2/live-videos/' + service + '/video/' + id)
  },
  endLiveSession: function (service) {
    return Request.get('/api/v2/live-videos/' + service + '/end-session')
  },
  deleteVideo: function (service, id) {
    return Request.get('/api/v2/live-videos/' + service + '/delete/' + id)
  },
  getUserVideoInventory: function (params) {
    return Request.get('/api/v2/live-videos/AllVideoInventory', params)
  }
}
