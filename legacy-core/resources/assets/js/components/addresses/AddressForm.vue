<template lang="html">
  <div class="address-form">
    <cp-input v-if="!hideNameField"
      label="Name"
      type="text"
      :error="validationErrors[ addressType + '.name' ]"
      :value.sync="address.name"></cp-input>
    <cp-input
      label="Address line 1"
      type="text"
      :error="validationErrors[ addressType + '.address_1' ]"
      :value.sync="address.address_1"></cp-input>
    <cp-input
      label="Address line 2"
      type="text"
      :error="validationErrors[ addressType + '.address_2' ]"
      :value.sync="address.address_2"></cp-input>
    <cp-input
      label="City"
      type="text"
      :error="validationErrors[ addressType + '.city' ]"
      :value.sync="address.city"></cp-input>
    <cp-select
      label="State"
      type="text"
      :options="states"
      :key-value="{ name: 'name' , value: 'value' }"
      :error="validationErrors[ addressType + '.state' ]"
      :value.sync="address.state"></cp-select>
    <cp-input
      label="Zip"
      type="text"
      :error="validationErrors[ addressType + '.zip' ]"
      :value.sync="address.zip"></cp-input>
  </div>
</template>

<script>
const { states } = require('../../resources/states.js')

module.exports = {
  data: function () {
    return {
      states: states
    }
  },
  props: {
    address: {
      type: Object,
      required: true,
      twoWay: true
    },
    addressType: {
      type: String,
      default: 'address'
    },
    validationErrors: {
      default () {
        return {}
      }
    },
    hideNameField: {
      type: Boolean,
      default () {
        return false
      }
    }
  },
  computed: {},
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue')
  }
}
</script>

<style lang="sass">
@import "resources/assets/sass/var.scss";

.address-form {
  .input-wrapper {
      label {
          display: block;
          font-size: 14px;
          font-weight: 300;
          margin-bottom: 0;
          &.product-name {
              display: inline-block;
          }
      }
      input {
          height: 100%;
          width: 100%;
          height: 30px;
          background: $cp-lighterGrey;
          border: none;
          text-indent: 10px;
          margin: 5px 0;
          &.no-top {
            // margin-top : 0;
          }
      }
      &.duo {
          display: flex;
          -webkit-display: flex;
          justify-content: space-between;
          -webkit-justify-content: space-between;
          margin-top: 5px;
          .half {
              width: 48%;
          }
      }
  }
  .select-wrapper {
      position: relative;
      background: rgba(248,248,248, 1);
      height: 30px;
      width: 100%;
      margin: 5px auto;
      &:after {
          position: absolute;
          right: 5px;
          top: 10px;
          font-family: "Linearicons";
          content: "\e93a";
          font-size: 10px;
          pointer-events: none;
      }
      select {
          height: 30px;
          width: 100%;
          border: none;
          -webkit-appearance: none;
          -moz-appearance: none;
          -webkit-border-radius: 0px;
          text-align: left;
          text-align-last: left;
          text-indent: 10px;
          background: $cp-lighterGrey;
          option {
              text-align: center;
          }
      }
  }
}

</style>
