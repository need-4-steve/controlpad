const Request = require('../resources/requestHandler.js')

module.exports = {
  salesVolume: function () {
    return Request.get('/api/v1/dashboard/sales-volume')
  },
  mcommVolume: function() {
    return Request.get('/api/v2/mcomm/kpi/')
  }
}
