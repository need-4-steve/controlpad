window.sms.vFilter('currency', () => {
  return function (input, floor) {
    if (input == null) {
      return input
    }
    let num = 0
    if (floor) {
      // Clip as a string to prevent precision erors
      let text = input.toString()
      let index = text.indexOf('.')
      if (index > -1) {
        num = parseFloat(text.substring(0, index + 3))
      } else {
        num = parseFloat(text)
      }
    } else {
      num = parseFloat(input)
    }
    if (num || num === 0) {
      if (num < 0) {
        num *= -1
        return '-$' + num.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
      }
      return '$' + num.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }
    // If not a number then return original input
    return input
  }
})
