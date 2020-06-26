<template lang="html">
  <div class="cp-pagination-wrapper">
    <button class="pages-button" @click="previousPage()" :disabled="pagination.current_page <= 1">Prev</button>
    <div class="pages-array">
      <button class="pages-button" :class="{ active: page == pagination.current_page }" v-for="page in array" @click="goToPage(page)">{{page}}</button>
    </div>
    <button class="pages-button" @click="nextPage()" :disabled="pagination.current_page >= pagination.last_page">Next</button>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      disablePrevious: false,
      disableNext: false
    }
  },
  props: {
    pagination: {
      type: Object,
      required: true
    },
    callback: {
      type: Function,
      required: true
    },
    options: {
      type: Object
    },
    size: {
      type: String
    }
  },
  computed: {
    array: function array () {
      if (this.pagination.last_page <= 0) {
        return []
      }
      var from = this.pagination.current_page - this.config.offset
      if (from < 1) {
        from = 1
      }
      var to = from + (this.config.offset * 2)
      if (to >= this.pagination.last_page) {
        to = this.pagination.last_page
      }
      var arr = []
      while (from <= to) {
        arr.push(from)
        from += 1
      }
      return arr
    },
    config: function config () {
      return Object.assign({
        offset: 3
      }, this.options)
    }
  },
  methods: {
    nextPage () {
      if (this.pagination.current_page >= this.pagination.last_page) {
        return
      }
      this.pagination.current_page = this.pagination.current_page + 1
      this.callback()
    },
    previousPage () {
      if (this.pagination.current_page <= 1) {
        return
      }
      this.pagination.current_page = this.pagination.current_page - 1
      this.callback()
    },
    goToPage (page) {
      if (page > this.pagination.last_page || page < 1) {
        return
      }
      this.pagination.current_page = page
      this.callback()
    }
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.cp-pagination-wrapper {
  text-align: center;
  margin: 10px auto;
  .pages-button {
    background: white;
    border: 1px solid $cp-main;
    color: $cp-main;
    padding: 4px 10px;
    margin: 0px 3px;
    border-radius: 3px;
    font-size: $cp-standardFontSize;
    &:disabled {
      background: lighten($cp-main, 40%);
      border-color: lighten($cp-main, 40%);
    }
    &:hover:enabled {
      background: $cp-main;
      color: white;
    }
    &.active {
      background: $cp-main;
      color: white;
    }
  }
  .pages-array {
    display: inline;
  }
}
</style>
