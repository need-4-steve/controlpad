const Request = require('../resources/requestHandler.js')

module.exports = {
  getPayQuickerRedirectLink () {
    return Request.get('/api/v1/payquicker/invite')
  },
  dashboard: function (params) {
    return Request.get('/api/v1/ewallet/dashboard-report', params)
  },
  salesTaxLedger: function (params) {
    return Request.get('/api/v1/ewallet/tax-ledger/', params)
  },
  payments: function (params) {
    return Request.get('/api/v1/ewallet/payments', params)
  },
  processingFees: function (params) {
    return Request.get('/api/v1/ewallet/processing-fees', params)
  },
  salesTax: function (params) {
    return Request.get('/api/v1/ewallet/sales-tax', params)
  },
  withdraw: function (params) {
    return Request.post('/api/v1/ewallet/withdraw', params)
  },
  ledger: function (params) {
    return Request.get('/api/v1/ewallet/ledger', params)
  },
  transaction: function (params) {
    return Request.get('/api/v1/ewallet/transaction/' + params)
  },
  payTaxesCreditCard: function (params) {
    return Request.get('/api/v1/ewallet/pay-taxes/credit-card', params)
  },
  payTaxesEcheck: function (params) {
    return Request.get('/api/v1/ewallet/pay-taxes/echeck', params)
  },
  payTaxesEwallet: function (params) {
    return Request.get('/api/v1/ewallet/pay-taxes/ewallet', params)
  },
  getUser: function () {
    return Request.get('/api/v1/eWallet/user')
  }
}
