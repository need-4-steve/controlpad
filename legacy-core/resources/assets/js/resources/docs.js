/**
 * This doesn't do much now, but we can add more as we go
 *
 * ServiceDocumenterFactory
 *
 * Example:
 *
 * const doc = ServiceDocumenterFactory('myServiceName')
 *
 * module.exports = {
 *    myNewFunc () {
 *      ...
 *    },
 *    myOldFunc () {
 *      doc.deprecate('myOldFunc').inFavorOf('myNewFunc')
 *      ...
 *    }
 * }
 */

const ServiceDocumenterFactory = (service) => {
  return {
    deprecate (oldFunc) {
      return {
        inFavorOf (newFunc) {
          console.warn(`DEPRECATION WARNING: The '${oldFunc}' method in the '${service}' service has been deprecated. Please use '${newFunc}' instead.`)
        }
      }
    }
  }
}
module.exports = {
  ServiceDocumenterFactory
}
