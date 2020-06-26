const Request = require('../resources/requestHandler.js')

module.exports = {
  index: function (params) {
    return Request.get('/api/v1/invoice/', params)
  },
  indexPdf: function (params) {
    return Request.get('/api/v1/invoices-pdf/', params)
  },
  getOrderByID: function (token) {
    return Request.get('/api/v1/invoice/' + token)
  },
  getPdfInvoiceByUid: function (uid) {
    return Request.get('/api/v1/invoice-pdf/' + uid)
  },
  updateInvoiceStatus: function (params) {
    return Request.post('/api/v1/invoice/update-invoice-status', params)
  }
}
