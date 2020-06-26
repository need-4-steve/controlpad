<template lang="html">
    <div class="cp-search-box">
        <input type="text"
        class="cp-input-standard"
        name="new_tag"
        :placeholder="placeholder"
        v-model="search_term"
        @keyup="setSearchTerm(false)">
        <button class="search-button" type="button" @click="setSearchTerm(true)">
            <i class="mdi mdi-magnify"></i>
        </button>
    </div>
</template>
<script>
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      search_term: ''
    }
  },
  props: {
    placeholder: {
      type: String,
      default () {
        return 'Search'
      }
    },
    searchTerm: {
      type: String
    }
  },
  methods: {
    setSearchTerm: _.debounce(function (clicked) {
      let term = this.search_term
      this.$emit('search-term', term, clicked)
    }, 900)
  }
}
</script>
<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .cp-search-box {
        input {
          width: 200px;
        }
        .search-button {
                background: transparent !important;
                box-shadow: none;
                font-size: 18px;
                width: 34px;
                padding: 4.5px;
                border: none !important;
                &.sm-case {
                    font-size: 14px;
                    margin-top: 0;
                }
        }
    }
</style>
