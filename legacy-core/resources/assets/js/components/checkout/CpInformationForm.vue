<template lang="html">
  <section class="checkout-information-section cp-form-inverse">
    <div class="col custom-info">
      <h3>Information</h3>
      <cp-input
        label="First Name"
        type="text"
        :error="validationErrors['first_name']"
        :value="customerInfo.user.first_name"
        v-model="customerInfo.user.first_name"></cp-input>
      <cp-input
        label="Last Name"
        type="text"
        :error="validationErrors['last_name']"
        :value="customerInfo.user.last_name"
        v-model="customerInfo.user.last_name"></cp-input>
      <cp-input
        label="Email"
        type="email"
        :error="validationErrors['email']"
        :value="customerInfo.user.email"
        v-model="customerInfo.user.email"></cp-input>
        <div v-if="$getGlobal('self_pickup_wholesale').show && checkoutType === 'wholesale'"><input class="self-pickup" type="checkbox" name="self-pickup" v-model="selfPickup" @change="useSelfPickup"><label for="self-pickup">Pick Up From {{ $getGlobal('company_name').value }}</label></div>
    </div>
    <div class="col">
      <h3>Billing Address</h3>
      <cp-address-form
        ref="billingAddressForm"
        address-type="billing_address"
        :hide-name-field="true"></cp-address-form>
    </div>
    <div class="col">
      <h3>Shipping Address</h3>
      <cp-address-form
        ref="shippingAddressForm"
        address-type="shipping_address"
        :disabled="sameAsBilling || selfPickup"
        :hide-name-field="true"></cp-address-form>
        <input class="same-as-billing" type="checkbox" name="same-as-billing" v-model="sameAsBilling" :disabled="selfPickup"><label for="same-as-billing">Same as billing address.</label>
    </div>
  </section>
</template>

<script>
const Address = require('../../resources/addresses.js')
module.exports = {
  data () {
    return {
      sameAsBilling: false,
      selfPickup: false,
      validationErrors: {},
      oldAddress: {}
    }
  },
  props: {
    customerInfo: {
      default () {
        return {
          user: {
            first_name: null,
            last_name: null,
            email: null
          }
        }
      }
    },
    checkoutType: {
      defalut: null
    },
  },
  watch: {
    sameAsBilling: 'onSameAsClick'
  },
  methods: {
    onSameAsClick () {
      if (this.sameAsBilling) {
        this.$refs.shippingAddressForm.setAddress(this.$refs.billingAddressForm.getAddress())
      }
    },
    getCustomer () {
      return this.customerInfo.user
    },
    setCustomer (customer) {
      this.customerInfo.user = customer
    },
    getAddresses () {
      let addresses = {
        billingAddress: this.$refs.billingAddressForm.getAddress(),
        shippingAddress: this.$refs.shippingAddressForm.getAddress(),
        sameAsBilling: this.sameAsBilling
      }
      return addresses
    },
    setBillingAddress (address) {
      this.$refs.billingAddressForm.setAddress(address)
    },
    setShippingAddress (address) {
      this.$refs.shippingAddressForm.setAddress(address)
    },
    useSelfPickup () {
      if (this.selfPickup) {
        this.oldAddress = this.$refs.shippingAddressForm.getAddress()
        Address.show({addressable_id: 1, label: 'Business', addressable_type: 'App\\Models\\User'})
          .then((response) => {
            if (!response.error) {
              let address = {
                line_1: response.address_1,
                line_2: response.address_2,
                city: response.city,
                state: response.state,
                zip: response.zip
              }
              this.setShippingAddress(address)
            }
        })
      } else {
        this.setShippingAddress(this.oldAddress)
      }
    },
    validate () {
      let valid = true
      let errors = {}
      if (!this.customerInfo.user.first_name) {
        errors['first_name'] = ['required']
        valid = false
      }
      if (!this.customerInfo.user.last_name) {
        errors['last_name'] = ['required']
        valid = false
      }
      if (!this.customerInfo.user.email) {
        errors['email'] = ['required']
        valid = false
      }
      if (Object.keys(errors).length > 0) {
        this.validationErrors = errors
      } else {
        this.validationErrors = {};
      }
      let addressFields = ['address_1', 'city', 'state', 'zip']
      if (!this.$refs.billingAddressForm.validate(addressFields) |
          (!this.sameAsBilling && !this.$refs.shippingAddressForm.validate(addressFields))) {
        valid = false
      }
      return valid
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue'),
    CpAddressForm: require('../addresses/CpAddressForm.vue')
  }
}
</script>

<style lang="scss">
  .checkout-information-section {
    display: flex;
    h3 {
      padding-bottom: 15px;
    }
    .col {
      flex: 1;
      padding: 20px;
    }
    .same-as-billing {
      width: 35px;
      height: 15px;
    }
    .self-pickup {
      width: 35px;
      height: 15px;
    }
  }

  @media (max-width: 768px) {
    .checkout-information-section {
      display: block;
    }
  }
  .cp-form-inverse {
      .custom-info {
          input {
              margin-top: 5px !important;
              margin-bottom: 5px !important;
          }
      }
  }
</style>
