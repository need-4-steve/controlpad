const Request = require('../resources/requestHandler.js')
const config = require('env').apis
const apiUrl = config.taxes
module.exports = {
  getQuote (params) {
    return Request.post(apiUrl + 'tax-invoices/', params)
  }
}
