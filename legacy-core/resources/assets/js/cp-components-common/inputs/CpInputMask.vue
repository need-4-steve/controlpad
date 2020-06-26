<template lang="html">
  <span>
    <label v-if="label">{{ label }}<cp-tooltip v-if="tooltip" :options="{content: tooltip}"></cp-tooltip></label>
    <input
      :class="{ 'cp-input-error': error, 'error': error }"
      type="text"
      :value="value"
      :disabled="setDisabled"
      v-mask="mask"
      :placeholder="placeholder"
      @input="updateValue($event.target.value)">
      <span :class="{ 'cp-validation-errors': error }" v-if="error">{{ error[0] }}</span>
  </span>
</template>

<script>
require('lodash')

module.exports = {
  data () {
    return {
      maskinput: '',
      inputvalue: {}
    }
  },
  methods: {
    updateValue (value) {
      return this.$emit('input', value)
    }
  },
  props: {
    value: {},
    type: {},
    placeholder: {},
    error: {},
    label: {},
    setDisabled: {},
    mask: {
      type: String,
      default () {
        return '###-##-####'
      }
    },
    tooltip: {
      type: String,
      required: false
    }
  }
}
</script>

<style lang="scss">
</style>
