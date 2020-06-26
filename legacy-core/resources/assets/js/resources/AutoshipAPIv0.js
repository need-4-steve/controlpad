const Request = require('../resources/requestHandler.js')
const config = require('env').apis

const apiUrl = config.autoship

module.exports = {
  getPlan (params, id) {
    return Request.get(apiUrl + 'plans/' + id, params)
  },
  getPlans (params) {
    return Request.get(apiUrl + 'plans', params)
  },
  createPlan (params) {
    return Request.post(apiUrl + 'plans', params)
  },
  updatePlan (params, id) {
    return Request.patch(apiUrl + 'plans/' + id, params)
  },
  deletePlan (id) {
    return Request.delete(apiUrl + 'plans/' + id)
  },
  getSubscriptions (params) {
    return Request.get(apiUrl + 'subscriptions', params)
  },
  getSubscription (id) {
    return Request.get(apiUrl + 'subscriptions/' + id)
  },
  createSubscription (params) {
    return Request.post(apiUrl + 'subscriptions', params)
  },
  deleteSubscription (id) {
    return Request.delete(apiUrl + 'subscriptions/' + id)
  },
  disableSubscription (id) {
    return Request.get(apiUrl + 'subscriptions/' + id + '/disable')
  },
  processSubscription (id) {
    return Request.post(apiUrl + 'subscriptions/process/' + id)
  }
}
