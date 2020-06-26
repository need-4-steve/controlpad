<template lang="html">
  <div class="payment-form">
    <div class="reg-form">
      <form class="cp-form-inverse registration-form" v-if="total > 0">
        <div v-if="paymentTypes.length > 1">
          <span>Select payment type</span><br>
          <select class="payment-selector" v-model="selectedPaymentType">
            <option v-for="paymentType in paymentTypes" :value="paymentType">{{ paymentType.display }}</option>
          </select>
        </div>
        <cp-credit-card-form v-if="selectedPaymentType.type == 'credit-card'" ref="ccForm"></cp-credit-card-form>
        <cp-e-check-form v-if="selectedPaymentType.type == 'e-check'" ref="echeckForm"></cp-e-check-form>
        <div class="payment-message-section" v-if="selectedPaymentType.type === 'zero-cash'">
          <span><strong>No payment required. Order total is $0.00</strong></span>
        </div>

        <input class="agree" type="checkbox" :class="{ error: validationErrors['agree'] }" name="agree" v-model="acceptAgreement" required>
        <span>I agree to the <a href="/terms-conditions/rep" target="_blank">terms and conditions</a> and to be charged a {{ selectedPlan.duration == 0 ? 'one-time' : 'subscription' }} fee of {{ selectedPlan.price.price | currency }}.</span>
        <span v-show="validationErrors['agree']" class="cp-warning-message">{{ validationErrors['agree'] }}</span>
      </form>

    </div>
    <div class="reg-form">
      <h2>Summary of Charges</h2>
      <br />
      <div>
        <div v-if="selectedKit !== null && selectedKit.id !== null" class="summary-item-container">
          <div class="left">Starter Kit: </div><div class="right">{{ selectedKit.wholesale_price | currency }}</div>
        </div>
        <div v-if="shippingAmount > 0" class="summary-item-container">
          <div class="left">Shipping: </div><div class="right">{{ shippingAmount | currency }}</div>
        </div>
        <div v-if="selectedPlan.free_trial_time == 0" class="summary-item-container">
          <div class="left">{{ selectedPlan.duration == 0 ? 'One-Time' : 'Plan' }}: </div><div class="right">{{ selectedPlan.price.price | currency }}</div>
        </div>
        <div v-if="planTax > 0" class="summary-item-container">
          <div class="left">Plan Tax: </div><div class="right">{{ planTax | currency }}</div>
        </div>
        <div class="summary-item-container">
          <div class="left"><strong>Total</strong>: </div><div class="right">{{ total | currency }}</div>
        </div>
      </div>
      <input v-show="selectedPlan.price.price+shippingAmount==0" class="agree" type="checkbox" :class="{ error: validationErrors['agree'] }" name="agree" v-model="acceptAgreement" required>
      <span v-show="selectedPlan.price.price+shippingAmount==0">I agree to the <a href="/rep-terms" target="_blank" rel="noopener">terms and conditions</a></span>
    </div>
  </div>
</template>

<script>
const Auth = require('auth')
const Checkout = require('../../resources/CheckoutAPIv0.js')
const Users = require('../../resources/UsersAPIv0.js')

module.exports = {
  data () {
    return {
      selectedPaymentType: {type: null, display: null},
      paymentTypes: [],
      validationErrors: {},
      acceptAgreement: false
    }
  },
  props: {
    selectedKit: {
      type: Object,
      required: true
    },
    selectedPlan: {
      type: Object,
      required: true
    },
    shippingAmount: {
      type: Number,
      required: false,
      default: 0
    },
    planTax: {
      type: Number,
      required: false,
      default: 0
    }
  },
  created () {
    console.log('created')
    this.updatePaymentTypes()
  },
  methods: {
    validate () {
      if (!this.acceptAgreement) {
        this.validationErrors = {agree: ['Required']}
        return false
      } else {
        this.validationErrors = {}
      }
      // Only validate credit card form
      switch (this.selectedPaymentType.type) {
        case 'credit-card':
          return this.$refs.ccForm.validate()
        case 'e-check':
          return this.$refs.echeckForm.validate()
        default:
          return true
      }
    },
    updatePaymentTypes () {
      console.log('updatePaymentTypes')
      let types = []
      if (this.total > 0) {
        let optionsSetting = this.$getGlobal('registration_payment_options').value
        if (optionsSetting.credit_card) {
          types.push({type: 'credit-card', display: 'Credit Card'})
        }
        if (optionsSetting.e_check) {
          types.push({type: 'e-check', display: 'Checking Account'}) // TODO do we need to distinguish account type?
        }
      } else {
        types.push({type: 'zero-cash', display: 'Free'})
      }
      this.paymentTypes = types
      this.selectedPaymentType = types[0]
    },
    getPayment () {
      switch (this.selectedPaymentType.type) {
        case 'zero-cash':
          return {
            type: 'cash',
            cash_type: 'Zero',
            amount: this.total
          }
        case 'credit-card':
          return {
            type: 'card',
            card: this.$refs.ccForm.getCard(),
            amount: this.total
          }
        case 'e-check':
          return {
            type: 'e-check',
            account: this.$refs.echeckForm.getAccount(),
            amount: this.total
          }
        default:
          return null
      }
    },
  },
  computed: {
    total () {
      return (this.selectedKit ? this.selectedKit.wholesale_price : 0.00) + (this.selectedPlan ? this.selectedPlan.price.price : 0.00)
    }
  },
  components: {
    CpCreditCardForm: require('../payment/CpCreditCardForm.vue'),
    CpECheckForm: require('../payment/CpECheckForm.vue')
  }
}
</script>

<style lang="scss">
.payment-selector {
  margin-top: 21px;
}
.payment-message-section {
  margin-top: 12px;
  margin-bottom: 16px;
  div {
    margin-top: 15px;
  }
}
</style>
