<template lang="html">
  <span class="select-standard-wrapper">
    <label v-if="!hideLabel">{{ label }}</label>
      <div class="cp-select-standard">
        <select
          :value="value"
          :disabled="disabled"
          @change="updateValue($event.target.value)"
          :class="{ 'select-error': error, 'error': error, 'disabled': disabled }"
          ref="input"
          :placeholder="placeholder">
          <option v-for="option in options" :value="option[keyValue['value']]">{{ option[keyValue['name']] || option.name }}</option>
        </select>
      </div>
      <span :class="{ 'cp-validation-errors': error }" v-if="error">{{ error[0] }}</span>
  </span>
</template>

<script>
module.exports = {
  props: {
    value: {id: 5},
    options: {
      type: Array
    },
    keyValue: {
      type: Object,
      default () {
        return {
          name: 'name',
          value: 'value'
        }
      }
    },
    disabled: {
      type: Boolean,
      default () {
        return false
      }
    },
    hideLabel: {
      type: Boolean,
      default () {
        return false
      }
    },
    placeholder: {},
    error: {},
    label: {},
    disabled: {
      type: Boolean,
      default () {
        return false
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

<style lang="scss" scoped>
.select-standard-wrapper {
  // label {
  //   display: inline-block;
  // }
}
.select-error {
  border: 1px solid tomato !important;
}

@media (max-width: 768px) {
    .cp-table-controls .limit-select select {
        min-width: 96px;
    }
}
</style>
