const Request = require('../resources/requestHandler.js')

module.exports = {
  rateRanges: function () {
    return Request.get('/api/v1/shipping-rate')
  },
  wholesaleRateRanges: function () {
    return Request.get('/api/v1/shipping-rate-wholesale')
  },
  createRateRanges: function (params) {
    return Request.post('/api/v1/shipping-rate/create', params)
  },
  getShippingCostByAuth: function (totalCost) {
    return Request.get('/api/v1/shipping-rate/shipping-cost-by-auth', totalCost)
  },
  shippoRates: function (params) {
    return Request.post('/api/v1/shipping/rates', params)
  },
  label: function (params) {
    return Request.post('/api/v1/shipping', params)
  },
  shippingCost: function (priceTotal) {
    return Request.get('/api/v1/shipping-rate/shipping-cost', priceTotal)
  },
  customOrderShippingCost: function (params) {
    return Request.get('/api/v1/custom-shipping-rate', params)
  }
}
