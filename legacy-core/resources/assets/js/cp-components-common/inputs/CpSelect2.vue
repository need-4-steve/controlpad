<template lang="html">
<select
    class="cp-select2"
    :value="value"
    @change="updateValue($event.target.value)"
    ref="input">
    <option v-for="option in options" :value="option[keyValue['value']] || option">{{ option[keyValue['name']] || option.name }}</option>
</select>
</template>
 <script id="CpSelect2">
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
select {
        margin: 0 auto;
        margin-top: 5px;
        margin-bottom: 5px;
        position: relative;
        height: 30px;
        width: 100%;
        border: none;
        padding: 3px;
        border-radius: 3px !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        -webkit-border-radius: 0px;
        text-align: left;
        text-align-last: left;
        text-indent: 10px;
        background: $cp-lighterGrey;
        font: $cp-standardFontSize $cp-fontFamily;
        &:before {
            position: absolute;
            right: 5px;
            top: 11px;
            font-family: "Material Icons";
            content: "\e5cb";
            font-size: 10px;
            pointer-events: none;
        }
    }

</style>
