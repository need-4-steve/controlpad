window.sms.addModule('querystring', (require, exports, module) => {
  const parse = () => {
    const query = {}
    window.location.search.substr(1).split('&').forEach((pair) => {
      let [key, value] = pair.split('=')
      key = decodeURIComponent(key)
      value = decodeURIComponent(value)
      if (query[key]) {
        query[key] = [query[key]]
        query[key].push(value)
      } else {
        query[key] = value
      }
    })
    return query
  }

  module.exports = {
    get (key) {
      return parse()[key]
    }
  }
})
