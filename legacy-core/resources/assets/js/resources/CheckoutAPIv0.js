const Request = require('../resources/requestHandler.js')
const config = require('env').apis
const apiUrl = config.orders

module.exports = {
  // Cart
  getCart (params, pid) {
    return Request.get(apiUrl + 'carts/' + pid, params)
  },
  postCart (params) {
    return Request.post(apiUrl + 'carts', params)
  },
  postCartLines (params, pid) {
    return Request.post(apiUrl + 'carts/' + pid + '/lines', params)
  },
  patchCartLines (params, pid) {
    return Request.patch(apiUrl + 'carts/' + pid + '/lines', params)
  },
  patchCartLine (params, pid) {
    return Request.patch(apiUrl + 'cartlines/' + pid, params)
  },
  deleteCartLine (pid) {
    return Request.delete(apiUrl + 'cartlines/' + pid)
  },
  deleteCart (params, pid) {
    return Request.delete(apiUrl + 'carts/' + pid, params)
  },
  applyCartCoupon (params, pid) {
    return Request.post(apiUrl + 'carts/' + pid + '/apply-coupon', params)
  },
  estimateCartShipping (pid) {
    return Request.get(apiUrl + 'carts/' + pid + '/estimate-shipping')
  },
  // Invoice
  getInvoice (token) {
    return Request.get(apiUrl + 'invoices/token/' + token)
  },
  createInvoiceFromCart (params, cartPid) {
    return Request.post(apiUrl + 'carts/' + cartPid + '/create-invoice', params)
  },
  // Process
  get (pid) {
    return Request.get(apiUrl + 'checkouts/' + pid)
  },
  createFromCart (params, cartPid) {
    return Request.post(apiUrl + 'carts/' + cartPid + '/create-checkout', params)
  },
  createFromInvoice (params, invoiceToken) {
    return Request.post(apiUrl + 'invoices/' + invoiceToken + '/create-checkout', params)
  },
  update (params, pid) {
    return Request.patch(apiUrl + 'checkouts/' + pid, params)
  },
  process (params, pid) {
    return Request.post(apiUrl + 'checkouts/' + pid + '/process', params)
  },
  deleteCheckout (pid) {
    return Request.delete(apiUrl + 'checkouts/' + pid)
  },
  // Shipping Rate
  getShippingRate (params) {
    return Request.get(apiUrl + 'shipping-rate-estimate', params)
  },
  getCoupons (params) {
    return Request.get(apiUrl + 'coupons', params)
  },
  getCoupon (id) {
    return Request.get(apiUrl + 'coupons/' + id)
  },
  createCoupon (params) {
    return Request.post(apiUrl + 'coupons', params)
  },
  deleteCoupon (id) {
    return Request.delete(apiUrl + 'coupons/' + id)
  }
}
