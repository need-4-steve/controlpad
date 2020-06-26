window.sms.vFilter('cpStandardDate', (require) => {
  return function (date, time = false, timezone = true) {
    if (date === null)
      return null

    var moment = require('moment-timezone')
    if (!timezone) {
      if (time) {
        return moment.utc(date).format('MM/DD/YYYY hh:mm a z')
      } else {
        return moment.utc(date).format('MM/DD/YYYY')
      }
    }
    date = moment.utc(date).local()
    if (time) {
      return date.format('MM/DD/YYYY hh:mm a z')
    } else {
      return date.format('MM/DD/YYYY')
    }
  }
})
