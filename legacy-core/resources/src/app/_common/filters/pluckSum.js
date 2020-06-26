window.sms.vFilter('pluckSum', () => {
  return function (list, key) {
    return list.reduce(function (total, item) {
      return total + item[key]
    }, 0)
  }
})
