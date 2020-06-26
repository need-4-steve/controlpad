<template lang="html">
  <section class="rep-locator-wrapper">
    <div class="main-locator-content">
      <div class="rep-locator-header">
        <h1>Find a {{ $getGlobal('title_rep').value }}</h1>
        <br>
      </div>
      <div class="rep-search-by-toggle">
        <button
        class="col"
        :class="{ active: steps.get('zip') }"
        @click="steps.skipTo('zip'), searchTerm = ''">ZIP Code</button>
        <button
        class="col"
        :class="{ active: steps.get('name') }"
        @click="steps.skipTo('name'), searchTerm = ''">Name</button>
      </div>
      <form class="cp-form-standard rep-search-form" @submit.prevent>
        <div class="col-auto">
          <cp-input
          v-show="steps.get('zip')"
          type="text"
          placeholder="Search by ZIP code"
          @keyup.enter="searchReps({ zip: searchTerm })"
          v-model="searchTerm"></cp-input>
          <cp-input
          v-show="steps.get('name')"
          type="text"
          placeholder="Search by name"
          @keyup.enter="searchReps({ name: searchTerm })"
          v-model="searchTerm"></cp-input>
        </div>
        <button class="cp-button-standard search-button" v-if="steps.get('zip')" @click="searchReps({ zip: searchTerm })" :disabled="loading">Search</button>
        <button class="cp-button-standard search-button" v-if="steps.get('name')" @click="searchReps({ name: searchTerm })" :disabled="loading">Search</button>
      </form>
      <div class="align-center">
        <div class="no-results cp-panel-standard" v-if="locatorMessage && !loading">
          <span>We could not find any matching {{$getGlobal('title_rep').value}}. Please try one of these random stores or try searching again: </span>
        </div>
        <div class="no-results" v-if="noResults && !loading">
          <span>No results</span>
        </div>
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
      </div>
      <table class="rep-search-results" v-show="!showMap">
        <tr v-for="rep in reps" :key="rep.public_id" @click="sendToStore(rep.domain)">
          <td>{{ rep.first_name }} {{ rep.last_name}}
            <br>
            <small>Online store: <a href="#">{{ rep.domain }}</a></small>
          </td>
          <td v-if="rep.distance || rep.distance === 0">{{ rep.distance.toFixed(1) }} miles</td>
          <td v-else></td>
        </tr>
      </table>
      <div class="google-map-section" v-if="$getGlobal('rep_locator_map_view').show">
        <gmap-map
        v-if="showMap && !loading"
        :center="center"
        :zoom="7"
        map-type-id="roadmap"
        style="width: 95%; height: 375px; margin: 0 auto;">
        <gmap-marker
        v-for="(mark, key) in markersWithGeocodes"
        :key="mark.public_id"
        :position="{ lat: mark.latitude, lng: mark.longitude }"
        :clickable="true"
        :draggable="false"
        :label="mark.first_name + ' ' + mark.last_name"
        :title="mark.first_name + ' ' + mark.last_name"
        @click="sendToStore(mark.domain)"></gmap-marker>
      </gmap-map>
    </div>
    </div>
    <div class="rep-search-buttons cp-panel-standard" v-if="$getGlobal('rep_locator_map_view').show">
      <button class="cp-button-standard" @click="showMap = !showMap" v-show="!showMap">MAP</button>
      <button class="cp-button-standard" @click="showMap = !showMap" v-show="showMap">LIST</button>
    </div>
  </section>
</template>

<script>
// require('marker-clusterer-plus')
const _ = require('lodash')

const Steps = require('../../libraries/step.js')
const Locator = require('../../resources/repLocator.js')
const GoogleMaps = require('../../resources/googleMaps.js')

var VueGoogleMaps = require('../../cp-components-common/temp-dependencies/vue-google-maps.js')
window.Vue.use(VueGoogleMaps, {
  load: {
    key: 'AIzaSyA5myQbIe_OrEoNzYXUhX46Ly3qNKj6d-8'
  }
})

module.exports = {
  data () {
    return {
      reps: [],
      loading: false,
      noResults: true,
      steps: Steps,
      browserLocation: { lng: null, lat: null },
      pagination: {},
      showMap: false,
      searchTerm: '',
      locatorMessage: false,
      center: {
        lat: 42,
        lng: -111.6946
      },
      markers: [{
        position: {
          lat: 10.0,
          lng: 10.0
        }
      }, {
        position: {
          lat: 40.326108,
          lng: -111.6811111
        }
      }]
    }
  },
  computed:{
      markersWithGeocodes: function() {
          return this.markers.filter(function(user) {
              return user.latitude !== null && user.longitude !== null;
          })
      }
  },
  mounted () {
    this.getBrowserLocation()
    let steps = {
      name: false,
      zip: true
    }
    this.steps.init(steps, 300)
  },
  methods: {
    sendToStore (url) {
      window.location = url
    },
    getZipCode (geocode) {
      var indexofstring
      var zip
      GoogleMaps.getAddressFromGeocode(geocode.lat, geocode.lng)
        .then((response) => {
          if (response.error) {
            return
          } else if (response.results && response.results.length > 0) {
            for (var i = 0; i < response.results.length; i++) {
              indexofstring = response.results[i].formatted_address.search(/\d{5}/)
              if (indexofstring !== -1) {
                zip = response.results[i].formatted_address.slice(indexofstring, indexofstring + 5)
                this.searchReps({ zip: zip })
                break
              }
            }
          }
        })
      return zip
    },
    getBrowserLocation () {
      // check for user's location
      if ('geolocation' in navigator && window.location.protocol === 'https:') {
        this.loading = true
        navigator.geolocation.getCurrentPosition((pos) => {
          this.browserLocation.lng = pos.coords.longitude
          this.browserLocation.lat = pos.coords.latitude
          this.center = this.browserLocation
          this.getZipCode(this.browserLocation)
        }, (error) => {
          this.loading = false;
          this.$toast('Service could not obtain your current location. Try entering a zipcode.', {dismiss: false})
          this.searchReps()
        })
      } else {
        this.searchReps()
      }
    },
    searchReps: _.debounce(function (request) {
      this.locatorMessage = false
      this.loading = true
      this.reps = []
      this.markers = []
      if (request) {
        var zip = request.zip
      }
      Locator.searchReps(request)
        .then((response) => {
          this.loading = false
          if (response.error) {
            this.$toast(response.message, { error: true })
            return
          }
          if (zip) {
            this.getGeocodeForCenter(zip)
          }
          this.handle200Response(response)
        })
    }, 300),
    getGeocodeForCenter (zipcode) {
      GoogleMaps.getGeocodeFromZipCode(zipcode)
        .then((response) => {
          if (response.status === "OK" && response.results.length > 0) {
            this.center.lat = response.results[0].geometry.location.lat
            this.center.lng = response.results[0].geometry.location.lng
          }
        })
    },
    handle200Response (response) {
      // check for what type of results we were able to receive
      this.noResults = false
      if (response.distance_results && response.distance_results.length > 0) {
        this.reps = response.distance_results
        this.markers = response.distance_results
      } else if (response.random_results && response.random_results.length > 0) {
        this.locatorMessage = true
        this.reps = response.random_results
        this.markers = response.random_results
      } else {
        this.noResults = true
      }
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.rep-locator-wrapper {
  .no-results {
    width: 95%;
    margin: 10px auto;
    margin-top: 20px;
    font-size: 14px;
  }
  .main-locator-content {
    max-width: 600px;
    margin: 0 auto;
  }
  button {
    font-size: 14px !important;
  }
  input {
    font-size: 14px !important;
  }
  .rep-locator-header {
    text-align: center;
  }
  .rep-search-form {
    width: 95%;
    margin: 0 auto;
    display: flex;

    .search-button {
      margin: 5px;
      padding: 8px;
      width: 100px;
      flex: initial;
    }
    .col {
      flex: initial;
    }
    .col-auto {
      flex: auto;
    }
  }
  .rep-search-results {
    width: 95%;
    margin: 0 auto;
    border-collapse: collapse;
    td {
      padding: 10px;
      text-align: left;
    }
    tr {
      cursor: pointer;
      border-bottom: 1px solid $cp-lightGrey;
      &:hover {
        background-color: $cp-lighterGrey;
      }
    }
  }
  .rep-search-buttons {
    text-align: center;
    position: fixed;
    overflow: hidden;
    bottom: 0;
    width: 100%;
    z-index: 100;
    button {
      display: block;
      margin: 0 auto;
    }
  }
  .rep-search-by-toggle {
    display: flex;
    width: 95%;
    margin: 0 auto;
    padding: 2px;
    button.col {
      border-radius: 2px;
      flex: 1;
      color: $cp-main;
      padding: 10px;
      background-color: $cp-lighterGrey;
      border: none;
      cursor: pointer;
      &:hover {
        background-color: darken($cp-lighterGrey, 20%);
        color: $cp-main;
      }
      &.active {
        background-color: $cp-main;
        color: white;
        cursor: initial;
      }
    }
  }
}
</style>
