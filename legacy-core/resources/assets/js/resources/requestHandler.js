const Auth = require('auth')
const Pid = require('./pid.js')

module.exports = {
  get (url, params, options) {
    return request(Object.assign({}, options, { method: 'get', url, params }))
  },
  post (url, body, options) {
    return request(Object.assign({}, options, { method: 'post', url, body }))
  },
  put (url, body) {
    return request({ method: 'put', url, body })
  },
  patch (url, body) {
    return request({ method: 'patch', url, body })
  },
  delete (url, params) {
    return request({ method: 'delete', url, params })
  }
}

const request = (opts) => {
  if (!opts || typeof opts !== 'object') throw new Error(`Invalid request object!`)
  let defaultHeaders = Object.assign({}, {
    'X-Cp-Request-Id': Pid.create()
  }, Auth.getAuthHeaders())
  opts.headers = Object.assign({}, opts.headers, defaultHeaders)
  return window.Vue
    .http(opts)
    .then(res => res.data)
    .catch(err => handleError(err))
}

const handleError = (error) => {
  var message
  switch (error.status) {
    case 401:
      Auth.logout().then(() => {
        window.VueInstance.$router.push(`/auth?return=${window.location.pathname}`)
      })
      return {
        error: true,
        code: error.status,
        message: 'Please login.'
      }
    case 500:
      message = ['500 ERROR: Please contact support.']
      window.Vue.toast('500 ERROR: Please contact support.', { error: true })
      break
    case 404:
      message = ['The requested data was not found.']
      break
    case 400:
      message = error.body
      window.Vue.toast(error.body, { error: true })
      break
    case 422:
      message = error.body
      if (typeof message === 'string') {
        window.Vue.toast(message, { error: true, dismiss: false })
      } else if (typeof message === 'object') {
        window.Vue.toast(message[Object.keys(message)[0]], { error: true, dismiss: false })
      } else {
        window.Vue.toast('Check your input and try again.', { error: true, dismiss: false })
      }
      break
    default:
      message = error.body
  }
  return {
    error: true,
    code: error.status,
    message: message
  }
}
