<template lang="html">
  <div class="">
    <cp-cart-button
      :cartPid="cartPid">
    </cp-cart-button>
    <div class="checkout-autoship-wrapper cp-panel-standard">
      <section class="cp-steps-circle">
        <div :class="{ 'active-step': steps.get('plan') }"><span>1</span> Select Plan</div>
        <div :class="{ 'active-step': steps.get('info') }"><span>2</span> Confirm Information</div>
        <div :class="{ 'active-step': steps.get('payment') }"><span>3</span> Confirm Your Subscription</div>
        <div :class="{ 'active-step': steps.get('summary') }"><span>4</span> Subscription Summary</div>
      </section>
      <br>
      <br>
      <div v-show="steps.get('info')" style="text-align:center">
        <a class="cp-button-standard" href="/my-settings" target="_blank">Click to Change Info</a>
      </div>

    <cp-autoship-plans
      :itemQuantity="cart.total_quantity"
      :steps="steps"
      ref="autoshipPlan"
      v-show="steps.get('plan')">
    </cp-autoship-plans>
      <cp-autoship-info
        ref="userInfoForm"
        :buyerPid="cart.buyer_pid"
        v-show="steps.get('info')">
      </cp-autoship-info>
      <div class="payment-info-section">
        <cp-autoship-confirm
          ref="confirmAutoship"
          v-show="steps.get('payment')">
        </cp-autoship-confirm>
      </div>
      <cp-purchase-summary
        ref="summary"
        v-show="steps.get('summary')"
        :order="order"
        :message="'Thank you for enrolling in '+ $getGlobal('autoship_display_name').value +'! Your order will processed within the next 24 hours.'">
      </cp-purchase-summary>
      <section class="checkout-controls">
        <div v-if="steps.get('info')">
          <button
            class="cp-button-standard checkout-back-button"
            @click="steps.skipTo('plan')">Back</button>
          <button
          class="cp-button-standard"
          :disabled="processing"
          @click="validateInfo()">Next</button>
        </div>
        <div v-else-if="steps.get('payment')">
          <button
            class="cp-button-standard checkout-back-button"
            :disabled="processing"
            @click="steps.skipTo('info')">Back</button>
          <button
            class="cp-button-standard"
            :disabled="processing"
            @click="process()">{{ processing ? 'Processing' : 'Place ' + $getGlobal('autoship_display_name').value }}</button>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
const Step = require('../../libraries/step.js')
const Checkout = require('../../resources/CheckoutAPIv0.js')
const Autoship = require('../../resources/AutoshipAPIv0.js')
const Tax = require('../../resources/TaxesAPIv0.js')
const moment = require('moment')
const Address = require('../../resources/addresses.js')

module.exports = {
  name: 'CpAutoshipCheckout',
  routing: [
    {
      name: 'site.CpAutoshipCheckout',
      path: 'autoship/:cartPid',
      meta: {
        title: ''
      },
      props: true
    }
  ],
  props: {
    cartProp: {
      type: Object,
      default: null
    },
    cartPid: {
      type: String
    }
  },
  data () {
    return {
      cart: {},
      steps: Step,
      processing: false,
      order: {},
      sellerAddress: {}
    }
  },
  mounted () {
    var self = this
    document.addEventListener("visibilitychange", function() {
      if (document.visibilityState === 'visible' && !self.steps.get('summary')) {
        self.getCart()
        if (self.steps.get('payment')) {
          self.steps.skipTo('info')
        }
      }
    })
    if (this.cartProp !== null) {
      this.cart = this.cartProp
      this.getTotals()
    } else {
      this.getCart()
    }
    this.getSellerAddress()
    // initilize number steps in this checkout form
    let newSteps = {
      plan: true,
      info: false,
      payment: false,
      summary: false
    }
    this.steps.init(newSteps, 300)
  },
  methods: {
    getSellerAddress () {
      let request = {
        addressable_id: 1,
        addressable_type: 'App\\Models\\User',
        label: 'Business'
      }
      Address.show(request)
        .then((response) => {
          if (response.error) {
            this.$toast("Seller's Business Address Not Set. Please contact seller.", { error: true, dismiss: false })
            return
          }
          this.sellerAddress = response
        })
    },
    getCart () {
      Checkout.getCart({'expands': ['lines']}, this.cartPid)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, { error: true, dismiss: false })
            return
          }
          this.cart = response
          this.$refs.userInfoForm.pullBuyer(this.cart.buyer_pid)
          this.countItems()
        })
    },
    countItems() {
      this.cart.total_quantity = 0
      for (i = 0; i < this.cart.lines.length; i++) {
        this.cart.total_quantity += this.cart.lines[i].quantity
      }
      if (this.$refs.autoshipPlan.plans) {
        this.$refs.autoshipPlan.getPlans()
      }
    },
    getTotals () {
      let currentDiscount = this.$refs.autoshipPlan.selectedPlan.current_discount
      this.cart.subtotal = 0
      this.cart.discount = 0
      this.cart.tax = 0
      this.cart.shipping = 0
      this.cart.total = 0
      this.cart.total_quantity = 0
      for (i = 0; i < this.cart.lines.length; i++) {
        this.cart.total_quantity += this.cart.lines[i].quantity
        this.cart.subtotal += this.cart.lines[i].quantity * this.cart.lines[i].price
        for (x = 0; x < this.cart.lines[i].items.length; x++) {
          if (this.cart.lines[i].items[x].premium_shipping_cost) {
            this.cart.shipping += this.cart.lines[i].items[x].premium_shipping_cost
          }
        }
      }
      this.cart.total = this.cart.subtotal
      if (currentDiscount) {
        this.cart.discount = Math.round(currentDiscount * this.cart.subtotal) / 100 
        this.cart.total -= this.cart.discount
      }
      this.getShippingRate()
    },
    getShippingRate () {
      let request = {
        seller_pid: this.cart.seller_pid,
        type: 'wholesale',
        amount: (this.cart.subtotal - this.cart.discount)
      }
      Checkout.getShippingRate(request)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, { error: true, dismiss: false })
            return
          }
          this.cart.shipping += parseFloat(response.amount)
          this.cart.total += this.cart.shipping
          this.getTax()
      })
    },
    getTax () {
      if (this.$getGlobal('tax_exempt_wholesale').show) {
          this.$refs.confirmAutoship.cart = this.cart
          this.steps.skipTo('payment')
          return
      }
      let lineItems = []
      for (i = 0; i < this.cart.lines.length; i++) {
        lineItems.push({
          'quantity': this.cart.lines[i].quantity,
          'subtotal': this.cart.lines[i].price * this.cart.lines[i].quantity,
          'tax_code': this.cart.lines[i].tax_class
        })
      }
      lineItems.push({
        'quantity': 1,
        'subtotal': this.cart.shipping,
        'type': 'shipping'
      })
      let request = {
        'to_address': this.$refs.userInfoForm.buyer.shipping_address,
        'from_address': this.sellerAddress,
        'line_items': lineItems,
        'merchant_id': 'default',
        'type': 'sale',
        'estimate': true
      }
      Tax.getQuote(request)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, { error: true, dismiss: false })
            return
          }
          this.cart.tax += response.tax
          this.cart.total += this.cart.tax
          this.$refs.confirmAutoship.cart = this.cart
          this.steps.skipTo('payment')
      })
    },
    validateInfo () {
      if (!this.$refs.userInfoForm.validate()) {
        this.processing = false
        return
      }
      if (!(this.cart.lines.length > 0)) {
        this.$toast('cart is empty', { error: true, dismiss: false })
        return
      }
      this.getTotals()
    },
    process () {
      this.processing = true
      let request = {
        buyer_first_name: this.$refs.userInfoForm.buyer.first_name,
        buyer_last_name: this.$refs.userInfoForm.buyer.last_name,
        cart_pid: this.cart.pid,
        plan_pid: this.$refs.autoshipPlan.selectedPlan.pid,
        next_billing_at: moment.utc().format('YYYY-MM-DD HH:mm:ss')
      }
      Autoship.createSubscription(request)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, { error: true, dismiss: false })
            this.processing = false
            return
          }
          this.order = {
            pid: response.pid,
            lines: response.lines,
            total_discount: response.discount,
            subtotal_price: response.subtotal,
            total_shipping: this.cart.shipping,
            total_tax: this.cart.tax,
            total_price: response.subtotal + response.discount + this.cart.shipping + this.cart.tax
          }
          this.steps.skipTo('summary')
      }) 
    }
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.checkout-autoship-wrapper {
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
  }
  .cp-steps-circle {
    text-align: center;
    & > div {
      display: inline-block;
      padding-bottom: 2px;
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
    @media (max-width: 768px) {
      text-align: left;
      div {
        width: 100%;
      }
    }
  }
}
</style>
