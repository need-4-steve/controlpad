const Request = require('../resources/requestHandler.js')

module.exports = {
  index: function (params) {
    return Request.get('/api/v1/user', params)
  },
  names: function (params) {
    return Request.get('/api/v1/user/names', params)
  },
  searchUsers (params) {
    return Request.get('/api/v1/user/search', params)
  },
  searchReps (params) {
    return Request.get('/api/v1/users/search/reps', params)
  },
  searchSponsors (params) {
    return Request.get('/api/v1/users/search/sponsors', params)
  },
  roles: function () {
    return Request.get('/api/v1/roles')
  },
  create: function (params) {
    return Request.post('/api/v1/user/create', params)
  },
  update: function (user) {
    return Request.put('/api/v1/user/update/' + user.id, user)
  },
  userAccount: function (id) {
    return Request.get('/api/v1/my-account/' + id)
  },
  userSettings: function (id) {
    return Request.get('/api/v1/user-settings/' + id)
  },
  updateUserSettings: function (settings) {
    return Request.put('/api/v1/user-settings/update', settings)
  },
  userCardInfo: function () {
    return Request.get('/api/v1/user-settings/card/show')
  },
  editJoinDate: function (params) {
    return Request.post('/api/v1/user/edit-join-date', params)
  },
  // check availability of domain name (public id)
  checkPublicId (publicId) {
    return Request.get('/api/v1/register/check-public-id/' + publicId)
  },
  adminCreatableRoles () {
    return Request.get('/api/v1/roles/admin-creatable')
  },
  /**
  * get terms  and rep url for my settings/my account page
  */
  getTerms (params, id) {
    return Request.get('/api/v1/user-settings/terms/' + id, params)
  },
  /**
  * validates a new user for registration purposes (does not register or create a user)
  */
  validateNewUser (params) {
    return Request.post('/api/v1/register/validate-user', params)
  },
  validateBasicCustomerInfo (params) {
    return Request.post('/api/v1/orders/validate-basic-customer-info', params)
  },
  softDeleteUser: function (id) {
    return Request.post('/api/v1/user/delete', id)
  },
  downloadUserCsv: function (params) {
    return Request.post('/api/v1/user/csv', params)
  },
  userCompanyInfo: function () {
    return Request.get('/api/v1/user/company')
  },
  createOrUpdateCompanyInfo: function (params) {
    return Request.post('/api/v1/user/createUpdateCompany', params)
  },
  acceptTermsAndConditions: function () {
    return Request.get('api/v1/user/acceptTerms')
  },
  getAuthUser () {
    return Request.get('/api/v1/user/auth')
  },
  isAffiliate () {
    return Request.get('/api/v1/user/isAffiliate')
  },
  getRegistrationToken (token) {
    return Request.get('/api/v1/user-token/' + token)
  }
}
