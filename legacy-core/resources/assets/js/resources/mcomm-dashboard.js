const Request = require('../resources/requestHandler.js')

module.exports = {
  mcommVolume: function () {
    return Request.get('/api/v2/kpi');
  } 
}
