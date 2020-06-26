window.sms.vFilter('shortDate', (require) => {
  return function (date) {
    const moment = require('moment')
    return moment.utc(date).local().format('ll')
  }
})
