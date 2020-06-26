<template lang="html">
  <span>
    <label v-if="label">{{ label }} <small v-if="subtext && !setting">{{subtext}}</small><cp-tooltip v-if="tooltip" :options="{content: tooltip}"></cp-tooltip></label>
    <input
      :class="{'cp-input-error': this.error, 'error': this.error, 'disabled': this.disabled}"
      @input="updateValue($event.target.value)"
      ref="input"
      :type="type"
      :value="value"
      @blur="blurMethod()"
      :readonly="readOnly"
      :disabled="disabled"
      :placeholder="placeholder">
      <span :class="{ 'cp-validation-errors': error }" v-if="error">{{ error[0] }}</span>

  </span>
</template>

<script>
module.exports = {
  props: {
    value: {},
    type: {},
    placeholder: {},
    error: {
      default () {
        return false
      }
    },
    label: {},
    disabled: {
      type: Boolean,
      default () {
        return false
      }
    },
    readOnly: {
      default () {
        return false
      }
    },
    blurMethod: {
      type: Function,
      default () {
        // do nothing by default
      }
    },
    setting: {
      default () {
        return true
      }
    },
    tooltip: {
      type: String,
      required: false
    },
    subtext: {
      type: String,
      required: false
    }
  },
  methods: {
    updateValue (value) {
      return this.$emit('input', value)
    }
  }
}
</script>

<style lang="scss">
</style>
