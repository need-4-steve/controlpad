const Request = require('../resources/requestHandler.js')

module.exports = {
  subscriptionPlanCreate: function (params) {
    return Request.post('/api/v1/subscriptions/create', params)
  },
  planIndex: function () {
    return Request.get('/api/v1/subscriptions/all-subscriptions')
  },
  getSubscriptionOnJoin () {
    return Request.get('/api/v1/register/plans')
  },
  deleteSubscriptions: function (id) {
    return Request.delete('/api/v1/subscriptions/' + id)
  },
  updateSubscription: function (params) {
    return Request.put('/api/v1/subscriptions/edit/' + params.id, params)
  },
  planPrice: function (id) {
    return Request.get('/api/v1/subscriptions/show-plan/' + id)
  },
  getUserIndex: function (params) {
    return Request.get('/api/v1/subscriptions/user-subscriptions', params)
  },
  getCustReportIndex:function (params) {
    return Request.get('/api/v1/subscriptions/user-subscriptions', params)
  },
  postAutoRenewalUpdate: function (params) {
    return Request.post('/api/v1/subscriptions/update-auto-renew', params)
  },
  updateUserEndsAt: function (params) {
    return Request.post('/api/v1/subscriptions/update-ends-at', params)
  },
  transactionReport: function (params) {
    return Request.get('/api/v1/subscriptions/transactions', params)
  },
  getSubReceipt: function (id, params) {
    return Request.get('/api/v1/subscriptions/receipt/' + id, params)
  },
  paySubcription: function (user) {
    return Request.post('/api/v1/subscriptions/renewable-pay', user)
  },
  subscriptionRenewAmount: function (userId) {
    return Request.get('/api/v1/subscriptions/renew-amount/' + userId)
  },
  getTax: function (params) {
    return Request.post('/api/v1/subscriptions/tax', params)
  },
  getTaxAdmin: function (params) {
    return Request.post('/api/v1/subscriptions/taxAdmin', params)
  },
  subscriptionReceipts: function (params) {
    return Request.get('/api/v1/subscriptions/all-receipt', params)
  }
}
