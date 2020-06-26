const Request = require('../resources/requestHandler.js')

module.exports = {
  getAllSettings: function (params) {
    return Request.get('/api/v1/settings')
  },
  getReturnPolicy: function () {
    return Request.get('/api/v1/pages/return-policy')
  },
  getGeneralStoreSettings: function () {
    return Request.get('/api/v1/settings/general-store')
  },
  getInventorySettings: function () {
    return Request.get('/api/v1/settings/inventory')
  },
  getRepSettings: function () {
    return Request.get('/api/v1/settings/rep')
  },
  getStoreSettings: function () {
    return Request.get('/api/v1/store-settings')
  },
  getTaxSettings: function () {
    return Request.get('/api/v1/settings/taxes')
  },
  update: function (params) {
    return Request.post('/api/v1/settings/update', params)
  },
  saveCustomPage: function (params) {
    return Request.post('/api/v1/pages/create', params)
  },
  saveRequireCustomPage: function (params) {
    return Request.post('/api/v1/pages/create-revised', params)
  },
  customPages: function () {
    return Request.get('/api/v1/pages')
  },
  getCompanyTerms: function () {
    return Request.get('/api/v1/pages/terms')
  },
  getRepTerms: function () {
    return Request.get('/api/v1/pages/rep-terms')
  },
  getCustomPage: function (slug) {
    return Request.get('/api/v1/pages/' + slug)
  },
  getRegistrationSettings: function () {
    return Request.get('/api/v1/settings/registration')
  },
  saveStoreSetting: function (params) {
    return Request.post('/api/v1/store-settings/update', params)
  },
  saveCategoryCaption: function (params) {
    return Request.post('/api/v1/store-settings/category-header', params)
  },
  getUserStoreSettings: function (userId) {
    return Request.get('/api/v1/store-settings/user/' + userId)
  },
  getShippingSettings: function () {
    return Request.get('/api/v1/settings/shipping')
  },
  getCommissionSettings: function (params) {
    return Request.get('api/v1/settings/commission-engine')
  },
  getSettingCategory: function (category) {
    return Request.get('api/v1/settings/category/' + category)
  },
  getBlacklist: function () {
    return Request.get('api/v1/settings/blacklist')
  },
  updateBlacklist: function (params) {
    return Request.post('api/v1/settings/updateBlacklist', params)
  },
  getCheckoutSetting: function () {
    return Request.get('/api/v1/checkout/setting')
  },
  saveCheckoutSetting: function (params) {
    return Request.post('/api/v1/checkout/setting', params)
  },
  customEmails: function () {
    return Request.get('/api/v1/email')
  },
  getCustomEmailBySlug (slug) {
    return Request.get('/api/v1/emails/byslug/' + slug)
  },
  saveCustomEmail: function (title, params) {
    return Request.post('/api/v1/email/update/' + title, params)
  },
  showcontent: function (title, params) {
    return Request.get('/api/v1/email/show/' + title, params)
  },
  updateVariantClaimNumber: function () {
    return Request.post('/api/v1/variants/claim-number')
  },
  getEventSettings: function () {
    return Request.get('/api/v1/settings/events')
  }
}
