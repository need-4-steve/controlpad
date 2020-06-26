<template lang="html">
  <div class="">
    <cp-cart-button
      :cartPid="checkout.cart_pid"
      v-if="checkout.cart_pid">
    </cp-cart-button>
    <div class="checkout-wrapper cp-panel-standard">
      <section class="cp-steps-circle">
        <div :class="{ 'active-step': steps.get('info') }"><span>1</span> Fill in your information</div>
        <div :class="{ 'active-step': steps.get('payment') }"><span>2</span> Confirm your order & Payment</div>
        <div :class="{'active-step': steps.get('summary') }"><span>3</span> Order Summary</div>
      </section>
      <br>
      <br>

      <p v-if="steps.get('error')"><strong class="errorText">{{ pageError }}</strong></p>
      <cp-information-form
        ref="userInfoForm"
        :checkoutType="checkoutType"
        v-show="steps.get('info')"></cp-information-form>

      <div class="payment-info-section" v-show="steps.get('payment')">
        <cp-make-payment ref="makePaymentForm"
          :checkout="checkout"
          :processing="processing"
          @set-processing="setProcessing"
          @set-checkout="setCheckout">
        </cp-make-payment>
      </div>

      <cp-purchase-summary
        v-show="steps.get('summary')"
        :order="order">
      </cp-purchase-summary>

      <section class="checkout-controls">
        <div v-if="steps.get('info')">
          <button
          class="cp-button-standard"
          :disabled="!checkout || processing"
          @click="validateInfo()">Next</button>
        </div>
        <div v-else-if="steps.get('payment')">
          <button
            class="cp-button-standard checkout-next-button"
            :disabled="processing"
            @click="process()">{{ processing ? 'Processing' : 'Place Order' }}</button>
        </div>
        <div v-if="steps.get('payment')">
          <button
            class="cp-button-standard checkout-back-button"
            :disabled="processing"
            @click="steps.skipTo('info')">Back</button>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
const Auth = require('auth')
const Step = require('../../libraries/step.js')
const Checkout = require('../../resources/CheckoutAPIv0.js')
const Users = require('../../resources/UserApiv0.js')

module.exports = {
  name: 'CpCheckout',
  routing: [
    {
      name: 'site.CpCheckout',
      path: 'checkouts/:checkoutPid',
      meta: {
        title: 'Checkout'
      },
      props: true
    }
  ],
  props: {
    checkoutPid: {
      type: String,
      required: true
    },
    checkoutProp: {
      default () {
        return null
      }
    },
    buyerProp: {
      default () {
        return null
      }
    }
  },
  data () {
    return {
      steps: Step,
      pageError: null,
      processing: false,
      checkout: {},
      checkoutType: null,
      buyer: null,
      order: {}
    }
  },
  mounted () {
    if (!this.checkoutProp) {
      this.pullCheckout()
    } else {
      this.setCheckout(JSON.parse(JSON.stringify(this.checkoutProp)))
      this.updateAddresses()
    }
    if (this.buyerProp) {
      this.buyer = JSON.parse(JSON.stringify(this.buyerProp))
      this.$refs.userInfoForm.setCustomer(this.buyer)
    }
    // initilize number steps in this checkout form
    let newSteps = {
      info: true,
      payment: false,
      summary: false,
      error: false
    }
    this.steps.init(newSteps, 300)
  },
  methods: {
    pullBuyer () {
      if (this.checkout && this.checkout.buyer_pid) {
        Users.get(this.checkout.buyer_pid, {addresses: true})
          .then((response) => {
            if (!response.error) {
              this.buyer = response
              this.$refs.userInfoForm.setCustomer(this.buyer)
            }
        })
      }
    },
    setCheckout (checkout) {
      this.checkout = checkout
      this.checkoutType = checkout.type
      this.$refs.makePaymentForm.setCheckout(checkout)
    },
    navigateToCart () {
      this.$router.push({
        path: '/carts/' + this.checkout.cart_pid
      })
    },
    pullCheckout () {
      Checkout.get(this.checkoutPid).then((response) => {
        if (response.error) {
          if (response.code == 404) {
            this.pageError = 'Checkout missing'
          } else {
            this.pageError = response.message.message
          }
          this.steps.skipTo('error')
          return;
        }
        this.setCheckout(response)
        this.updateAddresses()
        this.pullBuyer()
      })
    },
    setProcessing(processing) {
      this.processing = processing
    },
    validateInfo () {
      this.processing = true
      if (!this.$refs.userInfoForm.validate()) {
        this.processing = false
        return
      }
      let addresses = this.$refs.userInfoForm.getAddresses()
      let requestBody = {
        billing_address: addresses.billingAddress,
        shipping_address: addresses.shippingAddress,
        self_pickup: this.$refs.userInfoForm.selfPickup
      }
      // TODO compare addresses before posting update
      Checkout.update(requestBody, this.checkout.pid)
        .then((response) => {
          this.processing = false
          if (response.error) {
            this.$toast((response.message ? response.message : 'Unexpected error. Please try again later.'), { error: true })
            return;
          }
          this.setCheckout(response)
          this.steps.skipTo('payment')
        })
    },
    updateAddresses () {
      if (this.checkout.billing_address) {
        this.$refs.userInfoForm.setBillingAddress(this.checkout.billing_address)
      }
      if (this.checkout.shipping_address) {
        this.$refs.userInfoForm.setShippingAddress(this.checkout.shipping_address)
      }
    },
    process () {
      this.processing = true
      if (!this.$refs.makePaymentForm.validate()) {
        this.processing = false
        return
      }

      let request = {
        source: (this.checkout.type === 'wholesale' ? 'Inventory Purchase' : 'Web'),
        payment: this.$refs.makePaymentForm.getPayment(),
        buyer: this.buyer,
        partial_reserve: true
      }

      Checkout.process(request, this.checkout.pid)
        .then((response) => {
          if (response.error) {
            let message = null
            if (response.code == 422) {
              if (response.message.result_code) {
                message = response.message.message
                switch (response.message.result_code) {
                  case 3:
                    this.pageError = message + "\nPlease try again later."
                    this.steps.skipTo('error')
                    break;
                  case 4:
                    window.alert('Some products were not available. Checkout updated to have remaining products and price.')
                    this.setCheckout(response.message.checkout)
                    break;
                  case 5:
                    // message should be set to the transaction result already
                    break;
                  case 6:
                    this.steps.skipTo('info')
                    break;
                  case 8:
                    window.alert('Coupon already used. Checkout updated to remove coupon.')
                    this.setCheckout(response.message.checkout)
                    break;
                  default:
                    // Unexpected code, ignore for now
                }
              }
            } else {
              message = response.message
            }
            if (!message) {
              message = 'Unexpected error please try again later'
            }
            this.$toast(message, { error: true })
            this.processing = false
            return
          }
          this.order = response.order
          this.steps.skipTo('summary')
        })
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue'),
    CpInformationForm: require('../checkout/CpInformationForm.vue'),
    CpMakePayment: require('../checkout/CpMakePayment.vue'),
    CpCartButton: require('../cart/CpCartButton.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.checkout-information-section .col
{
  flex: 1 !important;
  padding: 20px !important;
}

.checkout-wrapper {
  padding: 20px;
  overflow: hidden;
  section {
    margin: 0px 0px;
  }
  .checkout-controls {
    text-align: right;
    .checkout-back-button {
      float: left;
    }
    .checkout-next-button {
      float: right;
    }
  }
  .cp-steps-circle {
    text-align: center;
    & > div {
      display: inline-block;
      margin: 0 20px;
      line-height: 25px;
      font-weight: 400;
      font-size: 14;
      margin: 0px 5px;
      & > span {
        display: inline-block;
        border-radius: 50%;
        height: 25px;
        width: 25px;
        background: #959595;
        color: #fff;
        text-align: center;
        margin-right: 5px;
      }
      &.active-step {
        color: $cp-main;
        & > span {
          background: $cp-main;
        }
      }
    }
  }
}
</style>
