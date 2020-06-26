<template lang="html">
  <div class="cp-box-standard">
    <div class="cp-box-heading">
      {{ title }}
    </div>
    <div class="cp-box-body">
      <cp-text-area
        label=""
        type="textarea"
        :error="null"
        :value="description"
        @input="emitInput"></cp-text-area>
    </div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      newDescription: null
    }
  },
  mounted () {
    this.newDescription = this.description
  },
  props: {
    title: {},
    validationErrors: {
      default () {
        return {}
      }
    },
    description: ''
  },
  methods: {
    emitInput (value) {
      this.$emit('input', value)
    }
  },
  components: {
    CpTextArea: require('../../cp-components-common/inputs/CpTextArea.vue')
  }
}
</script>

<style lang="scss">

</style>
