module.exports = {
  install (Vue) {
    // retrieve settings from rendered page
    Vue.cpNewSettings = JSON.parse(document.querySelector('#new-global-settings').getAttribute('content'))
    // method to allow global access to a setting by key
    Vue.prototype.$getGlobal = key => Vue.cpNewSettings[key] || { show: false, value: '' }
  }
}
