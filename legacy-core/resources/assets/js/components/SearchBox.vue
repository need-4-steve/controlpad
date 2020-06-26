<template lang="html">
    <div class="search-box">
        <input type="text"
        class="form-control ng-pristine ng-valid"
        name="new_tag"
        :placeholder="placeholder"
        v-model="search_term"
        @keyup="search() | debounce 500">
        <button class="search-button" type="button" @click="search()">
            <i class="mdi mdi-magnify"></i>
        </button>
    </div>
</template>
<script>
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
    }
  },
  methods: {
    search: function () {
      this.$dispatch('search_term', this.search_term)
    }
  }
}
</script>
<style lang="sass">
    @import "resources/assets/sass/var.scss";

    .search-box {
        float: right;
        width: auto;
        top: 8px;
        right: 5px;
        margin-bottom: 10px;
        input {
            float: left;
            width: 200px;
            display: inline;
            border: none;
            padding: 10px;
            border-bottom: solid 1px $cp-lightGrey;
            border-radius: 0 !important;
            box-shadow: none;
            -webkit-box-shadow: none;
            font-size: 16px;
        }
        input:focus {
            box-shadow: none;
            -webkit-box-shadow: none;
            border-bottom: solid 1px $cp-main;
        }
        ::-webkit-input-placeholder {
           color: $cp-lightGrey;
           text-align: right;
        }

        :-moz-placeholder {
            color: $cp-lightGrey;
           text-align: right;
        }

        ::-moz-placeholder {
            color: $cp-lightGrey;
           text-align: right;
        }

        :-ms-input-placeholder {
            color: $cp-lightGrey;
           text-align: right;
        }
        .search-button {
                float: left;
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
