<template lang="html">
  <span>
    <label v-if="label">{{ label }}</label>
      <date-picker
          v-model="newDate"
          :config="{format: 'MM/DD/YYYY h:mm a', useCurrent: false, sideBySide: true}"
          @dp-change="updateValue()"></date-picker>
      <span :class="{ 'cp-validation-errors': error }" v-if="error">{{ error[0] }}</span>
  </span>
</template>

<script>
module.exports = {
  data () {
    return {
      newDate: null
    }
  },
  props: ['date', 'label', 'error'],
  mounted () {
    this.newDate = this.$clientDate(this.date)
  },
  methods: {
    updateValue () {
      return this.$emit('newdate', this.$serverDate(this.newDate))
    }
  }
}
</script>

<style lang="scss">
</style>
