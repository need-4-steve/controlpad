<template lang="html">
  <span class="toggle-wrapper">
    <label v-if="label">{{ label }}<cp-tooltip v-if="tooltip" :options="{ content: tooltip}"></cp-tooltip></label>
    <input
    class="toggle-switch"
    type="checkbox"
    :checked="value"
    :disabled="setDisabled"
    @change="updateValue($event.target.checked)">
    <span :class="{ 'cp-validation-errors': error }" v-if="error">{{ error[0] }}</span>
  </span>
</template>

<script>
module.exports = {
  props: {
    value: {
      default () {
        return 1
      }
    },
    tooltip: {
      type: String,
      required: false
    },
    label: {},
    error: {},
    setDisabled: {},
    hideLabel: {
      type: Boolean,
      default () {
        return false
      }
    },
    blurMethod: {
      type: Function,
      default () {
        // does nothing by default
      }
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
@import "resources/assets/sass/var.scss";
.toggle-wrapper {
  display: flex;
  justify-content: space-between;
  input[type=checkbox].toggle-switch {
    position: relative;
    appearance: none;
    -webkit-appearance: none;
    moz-appearance: none;
    width: 40px !important;
    height: 25px;
    border-radius: 30px;
    background: #ddd;
    outline: 0;
    cursor: pointer;
    transition: background .3s ease-in-out;
    &:after {
      content: '';
      width: 24px;
      height: 100%;
      background: #fff;
      border-radius: 30px;
      position: absolute;
      left: 0;
      top: 0;
      transform: scale(0.8);
      transition: left .3s ease-in-out;
      box-shadow: 0 0 3px rgba(0,0,0, .5);
    }
  }
  input[type=checkbox].toggle-switch:checked {
    background: $cp-green;
    &:after {
      left: 16px;
    }
  }
}
</style>
