const Request = require('../resources/requestHandler.js')
const config = require('env').apis
const apiUrl = config.webhooks
const eventMap = {
  'order-created': 'Order Created',
  'order-fulfilled': 'Order Fulfilled',
  'order-cancelled': 'Order Cancelled'
}

module.exports = {
  search(params) {
    return Request.get(apiUrl + 'webhooks', params)
  },
  get (id) {
    return Request.get(apiUrl + 'webhooks/' + id)
  },
  create (webhook) {
    return Request.post(apiUrl + 'webhooks', webhook)
  },
  update (webhook) {
    return Request.patch(apiUrl + 'webhooks/' + webhook.id, webhook)
  },
  delete (id) {
    return Request.delete(apiUrl + 'webhooks/' + id)
  },
  getEventMap () {
    return eventMap;
  }
}
