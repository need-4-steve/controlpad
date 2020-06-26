const Request = require('../resources/requestHandler.js')

module.exports = {
  index: function (params) {
    return Request.get('/api/v1/sales', params)
  },
  getSalesByOrderType: function (orderType, params) {
    return Request.get('/api/v1/report/sales/' + orderType, params)
  },
  repToCustomer: function (params) {
    return Request.get('/api/v1/sales/reps', params)
  },
  getCorpIndex: function (params) {
    return Request.get('/api/v1/report/sales/corp', params)
  },
  getCustIndex: function (params) {
    return Request.get('/api/v1/report/sales/cust', params)
  },
  getRepIndex: function (params) {
    return Request.get('/api/v1/report/sales/rep', params)
  },
  getRepTransferIndex: function (params) {
    return Request.get('/api/v1/report/sales/rep-transfer', params)
  },
  getFBCIndex: function (params) {
    return Request.get('/api/v1/report/sales/fbc', params)
  },
  getFBCByRep: function (id, params) {
    return Request.get('/api/v1/report/sales/fbc/' + id, params)
  },
  getCorpTotal: function (params) {
    return Request.get('/api/v1/report/sales/corp/total', params)
  },
  getFBCTotals: function (params) {
    return Request.get('/api/v1/report/sales/fbc/total', params)
  },
  getRepTotals: function (params) {
    return Request.get('/api/v1/report/sales/rep/total', params)
  },
  getRepTransferTotals: function (params) {
    return Request.get('/api/v1/report/sales/rep-transfer/total', params)
  },
  getSalesTaxTotals: function (params) {
    return Request.get('/api/v1/report/tax/total', params)
  },
  getSalesTaxOwedTotals: function (params) {
    return Request.get('/api/v1/report/tax/owedByUser', params)
  },
  getRep: function (id, params) {
    return Request.get('/api/v1/report/sales/rep/' + id, params)
  },
  getAffiliateIndex: function (params) {
    return Request.get('/api/v1/report/sales/affiliate', params)
  },
  getAffiliateTotals: function (params) {
    return Request.get('/api/v1/report/sales/affiliate/total', params)
  },
  getAffiliate: function (id, params) {
    return Request.get('/api/v1/report/sales/affiliate_user/' + id, params)
  },
  isAffiliate: function () {
    return Request.get('/api/v1/report/isAffiliate')
  }

}
