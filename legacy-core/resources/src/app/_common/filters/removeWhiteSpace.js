window.sms.vFilter('removeWhiteSpace', () => {
  return {
    read: function (value) {
      return value.replace(/\s/g, '')
    },
    write: function (value) {
      return value.replace(/\s/g, '')
    }
  }
})
