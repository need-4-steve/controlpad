<template>
    <section class="customer-info">
        <div class="contact-info">
            <h3>Contact Information</h3>
            <p>
                <span>{{customer.first_name}}</span>&nbsp;<span>{{customer.last_name}}</span>
            </p>
            <p>{{customer.phone_number}}</p>
            <p>{{customer.email}}</p>
        </div>
        <div class="col-wrapper">
            <div class="left-col">
              <h3>Shipping Address:</h3>
              <cp-address-form
                ref="shippingAddressForm"
                address-type="shipping_address"
                :hide-name-field="true"></cp-address-form>
            </div>
            <div class="right-col">
              <h3>Billing Address:</h3>
              <cp-address-form
                ref="billingAddressForm"
                address-type="billing_address"
                :disabled="sameAddress"
                :hide-name-field="true"></cp-address-form>
            </div>
          </div>
        <div class="sameAs">
            <input type="checkbox" v-model="sameAddress" @change="addressCopy(sameAddress)"><span>Check if same as shipping address</span>
        </div>
    </section>
</template>
<script>

module.exports = {
  data () {
    return {
      customer: {},
      sameAddress: false
    }
  },
  methods: {
    validate () {
      valid = true
      let addressFields = ['address_1', 'city', 'state', 'zip']
      if (!this.$refs.shippingAddressForm.validate(addressFields)) {
        valid = false
      }
      if (!this.$refs.billingAddressForm.validate(addressFields)) {
        valid = false
      }
      return valid
    },
    getAddresses () {
      addresses = {
        shippingAddress: this.$refs.shippingAddressForm.getAddress(),
        billingAddress: this.$refs.billingAddressForm.getAddress()
      }
      let name = this.customer.first_name + ' ' + this.customer.last_name
      addresses.shippingAddress.name = name
      addresses.billingAddress.name = name
      return addresses
    },
    setBillingAddress (address) {
      if (address) {
        this.$refs.billingAddressForm.setAddress(address)
      }
    },
    setShippingAddress (address) {
      if (address) {
        this.$refs.shippingAddressForm.setAddress(address)
      }
    },
    setSameAs (same) {
      this.sameAddress = same
    },
    setCustomer (customer) {
      this.customer = customer
    },
    getCustomer () {
      return this.customer
    },
    addressCopy (same) {
      if (same) {
        this.$refs.billingAddressForm.setAddress(this.$refs.shippingAddressForm.getAddress())
      }
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpAddressForm: require('../addresses/CpAddressForm.vue')
  }
}
</script>
<style lang="scss">
@import "resources/assets/sass/var.scss";
.customer-info {
    margin-top: 50px;
    .contact-info {
        margin-top: 0;
        padding: 25px 10px;
    }
    .col-wrapper {
        display: flex;
        -webkit-display: flex;
        justify-content: space-between;
        -webkit-justify-content: space-between;
        border-top: solid 1px #eee;
        border-bottom: solid 1px #eee;
        padding: 30px 0;
        &:after {
            display: table;
            content: "";
            clear: both;
        }
    }
    .left-col, .right-col {
        float: left;
        width: 49%;
        margin: 0 auto;
        &:after {
            display: table;
            content: "";
            clear: both;
        }
    }
    h3 {
        margin-top: 0;
        padding-bottom: 10px;
    }
    .sameAs {
        padding-top: 5px;
        max-width: 100%;
        text-align: right;
        input {
            margin-right: 10px;
        }
    }
    .side-by-side {
     box-sizing: border-box;
     display: flex;
     justify-content: space-between;
     span:nth-child(1){
         width: 60%;
     }
     span:nth-child(2){
         width: 30%;
     }
      :first-child {
          label {
              margin-top: 0px;
          }
      }
  }
}
@media (max-width: 700px) {
    .customer-info {
        .contact-info {
            padding: 25px 20px;
            max-width: 80%;
            margin: 0 auto;
        }
        .col-wrapper {
            flex-direction: column;
            -webkit-flex-direction: column;
            padding: 30px 10px;
        }
        .left-col, .right-col {
            float: none;
            width: 100%;
        }
    }
}
</style>
