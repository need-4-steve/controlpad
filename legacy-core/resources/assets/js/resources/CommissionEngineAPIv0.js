const Request = require('../resources/requestHandler.js')
const config = require('env').apis
const ceUrl = config.commission
module.exports = {
  runCommand (headers) {
    let params = {}
    headers.sort = `orderdir=${headers.orderdir}&offset=${headers.offset}&limit=${headers.limit}&orderby=${headers.orderby}`
    request = Request.get(ceUrl, params, {headers})
    request.then(function (response) {
      if (response.errors) {
        throw response.errors
      }
      if (response.errormessage) {
        throw {detail: response.errormessage}
      }
    }).catch(function (error) {
      if (error.status != "400") {
        window.Vue.toast(error.detail, { error: true })
      }
    })
    return request
  }
}
