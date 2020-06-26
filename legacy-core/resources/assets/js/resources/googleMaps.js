const Request = require('../resources/googleMapsRequestHandler.js')

const gmapApiKey = 'AIzaSyA5myQbIe_OrEoNzYXUhX46Ly3qNKj6d-8'

module.exports = {
  getAddressFromGeocode (lat, lng) {
    return Request.post('https://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&key=' + gmapApiKey)
  },
  getGeocodeFromZipCode (zipcode) {
    return Request.post('https://maps.googleapis.com/maps/api/geocode/json?address=' + zipcode + '&key=' + gmapApiKey)
  }
}
