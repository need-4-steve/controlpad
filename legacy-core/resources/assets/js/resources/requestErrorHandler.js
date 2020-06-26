module.exports = function (error) {
  var message
  switch (error.status) {
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
      window.Vue.toast('The request could not be processed. Check your input.', { error: true, dismiss: false })
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
