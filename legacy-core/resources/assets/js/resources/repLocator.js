const Request = require('../resources/requestHandler.js')

const gmapApiKey = 'AIzaSyA5myQbIe_OrEoNzYXUhX46Ly3qNKj6d-8'

module.exports = {
  getRepsByLocation: function (searchLocation) {
    return Request.post('/api/v1/rep-locator/nearby-reps', searchLocation)
  },
  reverseGeocode: function (latitude, longitude) {
    return Request.get('/api/v1/rep-locator/geocoord/' + latitude + '/' + longitude)
  },
  searchProducts: function (productId, searchLocation) {
    return Request.post('/api/v1/rep-locator/searchProduct/' + productId, searchLocation)
  },
  // following are for the new rep locator
  getAddressFromGeocode (lat, lng) {
    return Request.post('https://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&key=' + gmapApiKey)
  },
  searchReps (params) {
    return Request.get('/api/v1/locator/rep/search', params)
  }
}
