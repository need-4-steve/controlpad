<template lang="html">
  <span class="textarea-wrapper">
    <label v-if="label">{{ label }}</label>
    <textarea
      class="'wysiwyg long"
      name="description"
      rows="8"
      cols="40"
      v-model="newValue"
      @input="updateValue($event.target.value)"
      :placeholder="placeholder"></textarea>
    <span :class="{ 'cp-validation-errors': error }" v-if="error">{{ error[0] }}</span>
  </span>
</template>

<script>
module.exports = {
  data () {
    return {
      newValue: {}
    }
  },
  props: {
    value: {},
    type: {},
    placeholder: {},
    error: {},
    label: {},
    setDisabled: {}
  },
  methods: {
    updateValue (value) {
      return this.$emit('input', value)
    }
  },
  mounted: function () {
    this.newValue = this.value
  }
}
</script>

<style lang="scss">
.textarea-wrapper {
  textarea {
    width: 100%;
    border: none;
  }
}
</style>
