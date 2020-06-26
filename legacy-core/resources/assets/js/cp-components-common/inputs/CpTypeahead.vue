<template lang="html">
  <section class="cp-typeahead">
    <cp-input
      :label="label"
      type="text"
      :blur-method="clearDropdown"
      v-model="searchTerm"></cp-input>
      <ul v-show="options.length > 0">
        <li v-for="option in options" @click="onOptionSelected(option)">{{ option[nameValue['name']] }}</li>
      </ul>
  </section>
</template>

<script>
module.exports = {
  data () {
    return {
      searchTerm: ''
    }
  },
  props: {
    options: {
      type: Array,
      required: true
    },
    nameValue: {
      type: Object,
      default () {
        return {
          name: 'name',
          value: 'value'
        }
      }
    },
    label: {},
    searchFunction: {
      type: Function,
      required: true
    },
    clearDropdown: {
      type: Function,
      require: true
    }
  },
  methods: {
    onOptionSelected (option) {
      this.$emit('input', option)
      this.$emit('options-cleared', [])
      this.searchTerm = ''
    }
  },
  watch: {
    searchTerm (value) {
      if (value && value !== '') {
        this.searchFunction(value)
      }
    }
  },
  components: {
    CpInput: require('../inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
.cp-typeahead {
  position: relative;
  display: inline-block;
  width: 100%;
  input {

  }
  ul {
    display: block;
    width: 100%;
    position: absolute;
    background-color: #f9f9f9;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    padding: 0px;
    z-index: 1;
    list-style: none;
    max-height: 400px;
    overflow-x: scroll;
    -moz-transition: all .25s ease-in-out;
    -webkit-transition: all .25s ease-in-out;
  }
  &:hover ul {
    display: block;
  }
  li {
    padding: 6px 16px;
    cursor: pointer;
    &:hover {
      background: lightgray;
    }
  }
}
</style>
