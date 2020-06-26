const Request = require('../resources/requestHandler.js')
const config = require('env').apis

const apiUrl = config.events

module.exports = {
  getEvent (params, id) {
    return Request.get(apiUrl + 'events/' + id, params)
  },
  getEvents (params) {
    return Request.get(apiUrl + 'events', params)
  },
  createEvent (params) {
    return Request.post(apiUrl + 'events', params)
  },
  updateEvent (params, id) {
    return Request.patch(apiUrl + 'events/' + id, params)
  },
  deleteEvents (params, id) {
    return Request.delete(apiUrl + 'events/' + id, params)
  }
}
