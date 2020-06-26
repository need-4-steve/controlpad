const Request = require('../resources/requestHandler.js')
const config = require('env').apis
const ceUrl = config.mcomm
module.exports = {
  mcommDownline: function(userId) {
    return Request.get('/api/v2/mcomm/genealogy/'+userId)
  },
  mcommVolume: function(userId, period){
    return Request.get('/api/v2/mcomm/kpi/'+userId+'/'+period)
  },
  salesVolume: function(userId,period){
    var request = Request.get('/api/v2/mcomm/kpi/'+userId)
    return request
  },
  mcommMembers: function(userId){
    return Request.get('/api/v2/mcomm/members/'+userId)
  },
  activeHistory: function(userId){
    return Request.get('/api/v2/mcomm/activePeriods/')
  },
  getCurrentPeriod: function(){
    return Request.get('/api/v2/mcomm/currentPeriod')
  }
}

  