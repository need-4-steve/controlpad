<template>
  <div>
    <section class="payment-section">
        <div class="submit-wrapper">
             <select v-show="einvoice.is_cash" v-model="invoice.cashType">
                 <option :value="'none'" selected disabled>Payment Method</option>
                 <option>Apple Pay</option>
                 <option>Cash/Check</option>
                 <option>Google Wallet</option>
                 <option>PayPal</option>
                 <option>Square</option>
                 <option>Square Cash</option>
                 <option>Venmo</option>
                 <option>Zelle</option>
                 <option>Other</option>
             </select>
            <button v-if="!processing" class="cp-button-standard" @click="createOrder">Submit Payment</button>
            <button v-if="processing" class="cp-button-standard">Processing . . .</button>
        </div>
    </section>

    <small>By clicking Place Order above, you agree to the following <a href="/return-policy" class="agree" target="_blank">terms</a></small>
  </div>
</template>
<script>
const Payments = require('../../resources/payments.js')

module.exports = {
  data: function () {
    return {
      sameAddress: false,
      processing: false,
      invoice: {}
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
    this.invoice = this.einvoice
  },
  methods: {
    addressConfirm () {
      if (this.sameAddress) {
        this.invoice.addresses.billing = this.invoice.user.shipping_address
      }
    },
    createOrder () {
      if (this.invoice.cashType === 'none' && this.invoice.is_cash) {
        return this.$toast('Please select payment method', { error: true, dismiss: false })
      }
      this.processing = true
      this.invoice.addresses.shipping = this.invoice.user.shipping_address
      this.invoice.addresses.shipping.label = 'Shipping'
      this.invoice.addresses.billing.label = 'Billing'
      this.invoice.cart.cash = true
      Payments.pay(this.invoice)
          .then((response) => {
            this.processing = false
            if (response.error) {
              return
            }
            this.closeModal()
            this.invoice = {
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
              payment: { card_number: '', security: '', month: '', year: '' },
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
              },
              cashType: ''
            }
            this.$emit('input', this.invoice)
            this.$toast('Payment successful.', { dismiss: false })
          })
    }
  }
}
</script>
<style lang="scss">
.submit-wrapper {
  select {
    margin-left: 79%;
    margin-bottom: 5px;
  }
}
</style>
