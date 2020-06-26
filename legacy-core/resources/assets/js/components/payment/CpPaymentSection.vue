<template>
    <section class="payment-section">
      <form class="cp-form-standard" @submit.prevent>
        <cp-payment-form
          v-model="Invoice"
          :payment-data="einvoice"
          :validation-errors="validationErrors"
          :hide-address="true"></cp-payment-form>
      </form>
        <div class="submit-wrapper">
            <button v-if="!processing" class="cp-button-standard" @click="createOrder()">Submit Payment</button>
            <button v-if="processing" class="cp-button-standard">Processing . . .</button>
              <small>By clicking Place Order above, you agree to the following <a href="/return-policy" class="agree" target="_blank">terms</a></small>
        </div>
    </section>

</template>
<script>
const Payments = require('../../resources/payments.js')

module.exports = {
  data: function () {
    return {
      sameAddress: false,
      processing: false,
      validationErrors: {},
      paymentData: {},
      Invoice: {
        addresses: {
          shipping: {},
          billing: {}
        }
      }
    }
  },
  props: {
    einvoice: {
      type: Object,
      required: true,
      default: {
        addresses: {
          shipping: {},
          billing: {}
        }
      }
    },
    closeModal: {
      type: Function
    }
  },
  mounted () {
    this.Invoice = this.einvoice
  },
  methods: {
    addressConfirm () {
      if (this.sameAddress) {
        this.Invoice.addresses.billing = this.Invoice.user.shipping_address
      }
    },
    createOrder () {
      this.processing = true
      this.Invoice.addresses.shipping = this.Invoice.user.shipping_address
      this.Invoice.addresses.shipping.label = 'Shipping'
      this.Invoice.addresses.billing.label = 'Billing'
      this.Invoice.cart.cash = false
      this.Invoice.cart.total_price = this.Invoice.cart.total_price
      Payments.pay(this.Invoice)
          .then((response) => {
            this.processing = false
            if (response.error) {
              var responseMessage = JSON.stringify(response.message)
              // we need to format backend responses like this better
              if (responseMessage.includes('Card failed')) {
                this.$toast(response.message[0], { error: true })
              } else {
                if (responseMessage.includes('cart.total_price')) {
                  this.closeModal()
                  this.$toast(response.message['cart.total_price'], { error: true })
                }
                if (response.code === 422) {
                  this.validationErrors = response.message
                }
              }
              return this.$emit('input', this.Invoice)
            }
            this.closeModal()
            this.Invoice = {
              cart: {
                subtotal: 0,
                subtotal_price: 0,
                msrp_subtotal: 0,
                discount: 0,
                total_shipping: 0,
                total_tax: '',
                cash: false
              },
              items: [],
              payment: { card_number: '', security: '', month: '', year: '', name: '' },
              user: {
                first_name: '',
                last_name: '',
                phone: { number: '', label: 'Mobile', type: 'Personal' },
                email: '',
                shipping_address: {},
                billing_address: {},
                role: ''
              },
              addresses: {
                shipping: {label: '', address_1: '', address_2: '', city: '', state: '', zip: ''},
                billing: {label: '', address_1: '', address_2: '', city: '', state: '', zip: ''}
              }
            }
            this.$emit('input', this.Invoice)
            this.$toast('Payment successful.')
          })
    }
  },
  components: {
    CpPaymentForm: require('./CpPaymentForm.vue')
  }
}
</script>
<style lang="scss">
.payment-section {
  .submit-wrapper {
    width: 100%;
    button {
      float: right;
    }
  }
}
</style>
