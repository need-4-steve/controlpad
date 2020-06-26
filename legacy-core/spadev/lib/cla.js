
module.exports = {
  bool (...keys) {
    for (let i = 0; i < keys.length; i++) {
      if (process.argv.includes(keys[i])) return true
    }
    return false
  },
  str (...keys) {
    for (let i = 0; i < keys.length; i++) {
      const index = process.argv.indexOf(keys[i])
      if (index !== -1) {
        return ('' + process.argv[index + 1])
      }
    }
    return undefined
  }
}
