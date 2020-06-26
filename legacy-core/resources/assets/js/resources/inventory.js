const Request = require('../resources/requestHandler.js')

module.exports = {
  index: function (params) {
    return Request.get('/api/v1/inventory', params)
  },
  savePrice: function (params) {
    return Request.post('/api/v1/inventory/save-price', params)
  },
  saveQuantity: function (params) {
    return Request.post('/api/v1/inventory/save-quantity', params)
  },
  updateQuanity: function (params) {
    return Request.post('/api/v1/inventory/save-quantity', params)
  },
  toggleDisable: function (id) {
    return Request.get('/api/v1/inventory/toggle/' + id)
  },
  getFulfilledByCorp: function (params) {
    return Request.get('/api/v1/inventory/fulfilled-by-corporate', params)
  },
  fulfilledByCorpPriceUpdate: function (params) {
    return Request.post('/api/v1/price/update-premium', params)
  },
  relistfulfilledByCorp: function (id) {
    return Request.post('/api/v1/inventory/relist', id)
  },
  updateExpirationDate: function (params) {
    return Request.post('/api/v1/inventory/expiration', params)
  },
  getAllLiveVideoProduct: function (params) {
    return Request.get('/api/v2/live-video-products', params)
  },
  createPersonalProduct: function (params) {
    return Request.post('/api/v2/live-video/create-product', params)
  },
  deletePersonalProduct: function (id) {
    return Request.get('/api/v2/live-videos/personal-product/delete/' + id)
  },
  getPersonalProduct: function (id) {
    return Request.get('/api/v2/live-videos/personal-product/' + id)
  }
}
