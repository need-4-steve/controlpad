const Rollbar = require('rollbar')

module.exports = {
  install: function(Vue) {
    Vue.rollbar = new Rollbar({
      accessToken: 'f9c6b720fb964fa683052b97b0c4f761',
      captureUncaught: false,
      captureUnhandledRejections: false,
      enabled: true,
      source_map_enabled: false,
      environment: 'production',
      payload: {
        client: {
          javascript: {
            code_version: 'unknown'
          }
        }
      }
    });
    Vue.prototype.$rollbar = Vue.rollbar
  }
}
