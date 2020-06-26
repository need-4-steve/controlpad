if (!window.Vue) {
  window.Vue = require('vue')
}
const VueResource = require('vue-resource')
const Toast = require('../custom-plugins/toast/index.js')
const ErrorHandler = require('../resources/requestErrorHandler.js')

window.Vue.use(Toast)
window.Vue.use(VueResource)

var standardOptions = {
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
  }
}

module.exports = {
  get: function (endpoint, params) {
    return window.Vue.http.get(endpoint, { params: params }, standardOptions)
        .then((response) => {
          return response.data
        }, (error) => {
          return this.handleError(error)
        })
  },
  post: function (endpoint, params) {
    return window.Vue.http.post(endpoint, params, standardOptions)
        .then((response) => {
          return response.data
        }, (error) => {
          return this.handleError(error)
        })
  },
  put: function (endpoint, params) {
    return window.Vue.http.put(endpoint, params, standardOptions)
        .then((response) => {
          return response.data
        }, (error) => {
          return this.handleError(error)
        })
  },
  patch: function (endpoint, params) {
    return window.Vue.http.patch(endpoint, params, standardOptions)
        .then((response) => {
          return response.data
        }, (error) => {
          return this.handleError(error)
        })
  },
  delete: function (endpoint, params) {
    return window.Vue.http.delete(endpoint, params, standardOptions)
        .then((response) => {
          return response.data
        }, (error) => {
          return this.handleError(error)
        })
  },
  handleError: ErrorHandler
}
