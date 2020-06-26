const Request = require('../resources/requestHandler.js')
// apiUrl will need to point to live eventually
const config = require('env').apis
const apiUrl = config.orders

module.exports = {
  get (params, id) {
    return Request.get(apiUrl + 'orders/' + id, params)
  },
  getByReceiptId (params, id) {
    return Request.get(apiUrl + 'orders/by-receipt-id/' + id, params)
  },
  getOrders (params) {
    return Request.get(apiUrl + 'orders', params)
  },
  updateStatuses (params) {
    return Request.patch(apiUrl + 'orders', params)
  },
  updateStatus (params, id) {
    return Request.patch(apiUrl + 'orders/' + id, params)
  },
  updateShippingAddress (params, id) {
    return Request.put(apiUrl + 'orders/' + id + '/shipping-address', params)
  },
  acceptInventory (id) {
    return Request.get(apiUrl + 'orders/' + id + '/accept-inventory')
  }
}
