window.Vue.use(window.VueMask)
window.Vue.directive('mask', window.VueMask.VueMaskDirective)
window.Vue.use(window.VueResource)
window.Vue.use({
  install: function (Vue) {
    Vue.prototype.$d3 = window.d3
  }
})

window.Vue.use({
  install (Vue) {
    const SERVER_FORMAT = 'YYYY-MM-DDTHH:mm:ss.SSS'
    const CLIENT_FORMAT = 'MM/DD/YYYY h:mm'
    Vue.prototype.$clientDate = (date) => {
      return window.moment.utc(date).local().format(CLIENT_FORMAT)
    }
    Vue.prototype.$serverDate = (date) => {
      return window.moment(date, CLIENT_FORMAT).utc().format(SERVER_FORMAT)
    }
  }
})

window.cpHelpers = {
  getParameterByName: function (name, url) {
    if (!url) url = window.location.href
    name = name.replace(/[[\]]/g, '\\$&')
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)')
    var results = regex.exec(url)
    if (!results) return null
    if (!results[2]) return ''
    return decodeURIComponent(results[2].replace(/\+/g, ' '))
  }
}

// TODO is this really the best way to do this?
window.sms.vUse('CpToastPlugin', require => require('assets/js/custom-plugins/toast/index.js'))
