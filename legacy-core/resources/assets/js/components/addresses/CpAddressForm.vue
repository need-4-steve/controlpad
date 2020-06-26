<template lang="html">
  <div class="cp-form-standard">
      <cp-input v-if="!hideNameField"
      label="Name"
      type="text"
      :disabled="disabled"
      :error="validationErrors[ addressType + '.name' ]"
      v-model="address.name"></cp-input>
      <cp-input
      label="Address line 1"
      type="text"
      :disabled="disabled"
      :error="validationErrors[ addressType + '.address_1' ]"
      v-model="address.address_1"></cp-input>
      <cp-input
      label="Address line 2"
      type="text"
      :disabled="disabled"
      :error="validationErrors[ addressType + '.address_2' ]"
      v-model="address.address_2"></cp-input>
      <cp-input
      label="City"
      type="text"
      :disabled="disabled"
      :error="validationErrors[ addressType + '.city' ]"
      v-model="address.city"></cp-input>
      <div class="form-specific-wrapper">
        <cp-select
        label="State"
        type="text"
        :disabled="disabled"
        :options="states"
        :key-value="{ name: 'name' , value: 'value' }"
        :error="validationErrors[ addressType + '.state' ]"
        v-model="address.state"></cp-select>
        <cp-input
        label="Zip"
        type="text"
        :disabled="disabled"
        :error="validationErrors[ addressType + '.zip' ]"
        v-model="address.zip"></cp-input>
    </div>
  </div>
</template>

<script>
const { states } = require('../../resources/states.js')

module.exports = {
  data: function () {
    return {
      states: states,
      defaultValidationFields: ['name', 'address_1', 'city', 'state', 'zip'],
      errors: {}
    }
  },
  props: {
    address: {
      type: Object,
      default () {
        return {
          name: null,
          address_1: null,
          address_2: null,
          city: null,
          state: null,
          zip: null
        }
      }
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
    },
    disabled: {
      type: Boolean,
      default () {
        return false
      }
    }
  },
  methods: {
    getAddress () {
      let address = {
        line_1: this.address.address_1,
        line_2: this.address.address_2,
        city: this.address.city,
        state: this.address.state,
        zip: this.address.zip
      }
      if (this.address.name) {
        address.name = this.address.name
      }
      return address
    },
    setAddress(address) {
      if (address) {
        this.address.name = address.name
        this.address.address_1 = address.line_1
        this.address.address_2 = address.line_2
        this.address.city = address.city
        this.address.state = address.state
        this.address.zip = address.zip
      }
    },
    validate (requiredFields) {
      if (!requiredFields) {
        requiredFields = this.defaultValidationFields
      }
      let errors = {}
      requiredFields.forEach((field) => {
        if (!this.address[field]) {
          errors[this.addressType + '.' + field] = ['required']
        }
      })
      if (Object.keys(errors).length > 0) {
        this.validationErrors = errors
        return false
      } else {
        this.validationErrors = {};
      }
      return true
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.form-specific-wrapper {
      display: flex;
      justify-content: space-between;
     input {
      margin-top:5px;
    } span:nth-child(2) {
    width: 49%;
    } span:nth-child(1){
    width: 49%;
  }
}
@media (max-width: 476px) {
   .address-form {
       div input {
         margin: 0;
       } span:nth-child(2) {
       width: 40%;
       } span:nth-child(1){
       width: 58%;
     }
   }
 }

</style>
