<template lang="html">
  <div class="cp-form-standard cp-rep-select-typehead">
    <label v-if="label">{{ label }}</label>
    <cp-typeahead
      @input="setSelectedRep"
      :options="reps"
      :clear-dropdown="clearDropdown"
      :name-value="{ name: 'full_name', value: 'id'}"
      @options-cleared="function (val) {reps = val}"
      :search-function="searchReps"></cp-typeahead>

  </div>
</template>

<script>
const _ = require('lodash')
const Users = require('../../resources/users.js')

module.exports = {
  props: {
    label: {
      type: String,
      required: false,
      default () {
        return null
      }
    }
  },
  data () {
    return {
      reps: []
    }
  },
  methods: {
    setSelectedRep: function (user) {
      this.$emit('rep-selected', user)
    },
    clearDropdown: _.debounce(function () {
      this.reps = []
    }, 500),
    searchReps: _.debounce(function (searchTerm) {
      this.reps = []
      Users.searchReps({'searchTerm': searchTerm})
        .then((response) => {
          if (response.error) {
            this.reps = []
            return false
          }
          // format response for typehead
          for (var i = 0; i < response.length; i++) {
            response[i].full_name = response[i].first_name + ' ' + response[i].last_name + ' (' + response[i].id + ')'
          }
          this.reps = response
        })
    }, 170),
  },
  components: {
    CpTypeahead: require('../../cp-components-common/inputs/CpTypeahead.vue')
  }
}
</script>

<style lang="scss">
.cp-rep-select-typehead{
  ul{
    margin: 0;
  }
}
</style>
