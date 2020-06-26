const Request = require('../resources/requestHandler.js')

module.exports = {
  pay: function (params) {
    return Request.post('/api/v1/custom-order/pay', params)
  },
  calculateTaxes: function (params) {
    return Request.post('/api/v1/custom-order/tax', params)
  },
  subscriptionAmount: function (userId) {
    return Request.get('/api/v1/subscriptions/renew-amount/' + userId)
  },
  // REGISTRATION RELATED
  subscriptionPayment (params) {
    return Request.post('/api/v1/register', params)
  },
  subscriptionPayNow () {
    return Request.post('/api/v1/subscriptions/renew-subscription')
  },
  validateSplashAccount (params) {
    return Request.post('/api/v1/register/validate-splash', params)
  },
  createPaymentProcessingAccount (params) {
    return Request.post('/api/v1/payment-account', params)
  },
  checkPaymentProcessingAccount (id, params) {
    return Request.post('/api/v1/payment-account/user/' + id, params)
  }

}
