<template>
  <div class="pay-taxes">
  <div>
    <span v-if="payTaxesButton === true">
      <button class="cp-button-standard" @click="setPaymentType()">Pay Taxes</button>
    </span>
  </div>

  <transition name='fade'>
    <section class="cp-modal-standard" v-if="paymentModal === true">
      <div class="cp-modal-body">
        <div class="cp-modal-header">
          <h2>{{ title }}</h2>
          <span @click="paymentModal = !paymentModal"><i class="mdi mdi-close"></i></span>
        </div>

        <div v-show="step == 1">
          <div class='cp-modal-body'>
            <div v-if="checkPayEwallet()">
              <input type="radio" class='' id='ewallet' value='ewallet' v-model="paymentType"></input>
              <label @click="paymentType = 'ewallet'">eWallet</label>
            </div>
            <div v-if="checkPayEcheck()">
              <input type="radio" class='' id='echeck' value='echeck' v-model="paymentType"></input>
              <label @click="paymentType = 'echeck'">eCheck</label>
            </div>
            <div v-if="checkPayCreditCard()">
              <input type="radio" class='' id='credit-card' value='credit-card' v-model="paymentType"></input>
              <label @click="paymentType = 'credit-card'">Credit Card</label>
              <cp-tooltip :options="{ content: 'To pay with a credit card you must pay a minimum of $5.00' }"></cp-tooltip>
            </div>
          </div>
          <div class="modal-footer-single">
            <button type="button" class="cp-button-standard" @click="next(step + 1)">Next</button>
          </div>
        </div>

        <div v-show="step == 2">
          <div class='cp-modal-body'>
          <div v-show="paymentType == 'ewallet'" class="balance">
            <label class="payment-information">
              Available Funds {{ availableFunds | currency('floor') }}
            </label>
          </div>
          <div v-show="paymentType == 'echeck'">
            <cp-ach-form :payment-data="paymentData" :validation-errors="validationErrors"></cp-ach-form>
          </div>
          <div v-show="paymentType == 'credit-card'">
            <form class="cp-form-standard credit-card-form">
              <cp-payment-form :payment-data="paymentData" :validation-errors="validationErrors"></cp-payment-form>
            </form>
          </div>
          <div class="payment-information taxes">
            <label>Taxes Owed {{ taxesOwed | currency('floor') }}</label>
          </div>
          <div class="payment-information">
            <label>Enter Amount</label>
            <span><label>$</label><input class='cp-input-standard amount-input' type="number" v-model="paymentData.total" v-on:keyup="checkAmount"></input></span>
            <span v-show="paymentType === 'credit-card'"><cp-tooltip  :options=" { content: 'To pay with a credit card you must pay a minimum of $5.00' }"></cp-tooltip></span>
          </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="cp-button-standard" @click="next(step - 1)">Back</button>
            <span v-if="paymentType !== 'credit-card' && paymentData.total > 0 || paymentType === 'credit-card' && paymentData.total >= 5">
              <button type="button" class="cp-button-standard" @click="next(step + 1)">Next</button>
            </span>
            <span v-else>
              <button type="button" class="cp-button-standard" disabled>Next</button>
            </span>
          </div>
        </div>

        <div v-show="step == 3">
          <div>
            <div class="cp-modal-body">
              <p>You're about to pay {{ paymentData.total | currency('floor') }} to your open sales tax.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="cp-button-standard" @click="next(step - 1)">Back</button>
              <button type="button" class="cp-button-standard" @click="next(step + 1)">Confirm</button>
            </div>
          </div>
        </div>

        <div v-show="step == 4" class="align-center">
          <div class="modal-body">
            <p>{{ message }}</p>
          </div>
          <div class="modal-footer-single">
            <button class="cp-button-standard" @click="paymentModal = !paymentModal">OK</button>
          </div>
        </div>
      </div>
    </section>
  </transition>
</div>
</template>

<script>
const EWallet = require('../../../resources/ewallet.js')

module.exports = {
  data: function () {
    return {
      message: null,
      step: 1,
      title: 'Choose Payment Type',
      paymentModal: false,
      paymentType: 'ewallet',
      paymentData: {
        addresses: {
          billing: {}
        },
        account_name: null,
        account_number: null,
        routing_number: null,
        payment: {},
        total: 0.00
      },
      payTaxesButton: false,
      validationErrors: {}
    }
  },
  props: {
    callBack: {
      type: Function,
      required: true
    },
    modalShow: {
      type: Object
    },
    availableFunds: {
      required: true
    },
    taxesOwed: {
      required: true
    },
    user: {
      type: Object,
      required: true
    }
  },
  mounted () {
    this.paymentData.addresses.billing = this.user.billing_address
    if (this.checkPayEwallet() || this.checkPayCreditCard()) {
      this.payTaxesButton = true
    }
    this.paymentData.total = this.taxesOwed
  },
  methods: {
    payTaxes () {
      if (this.paymentType === 'credit-card') {
        EWallet.payTaxesCreditCard(this.paymentData)
        .then((response) => {
          this.responseMessage(response)
        })
      } else if (this.paymentType === 'echeck') {
        EWallet.payTaxesEcheck(this.paymentData)
        .then((response) => {
          this.responseMessage(response)
        })
      } else if (this.paymentType === 'ewallet') {
        EWallet.payTaxesEwallet(this.paymentData)
        .then((response) => {
          this.responseMessage(response)
        })
      } else {
        this.title = 'Error'
        this.message = 'Payment type not selected correctly.'
      }
    },
    responseMessage (response) {
      if (response.success) {
        this.title = 'Success'
        this.message = 'Your payment was successful.'
        this.step = 4
        this.paymentData.total = 0
        this.callBack()
        return
      } else if (!response.success && response.description) {
        this.title = 'Error'
        this.message = response.description
        this.step = 4
        return
      } else {
        if (response.code === 422) {
          this.step = 2
          this.validationErrors = response.message
        }
        return
      }
    },
    checkAmount () {
      if (this.paymentData.total > this.taxesOwed) {
        this.paymentData.total = this.taxesOwed
      }
      if (this.paymentType === 'ewallet' && this.paymentData.total > this.availableFunds) {
        this.paymentData.total = this.availableFunds
      }
    },
    next (place) {
      switch (place) {
        case 1:
          this.step = 1
          this.title = 'Pay Taxes'
          break
        case 2:
          this.step = 2
          if (this.paymentType === 'credit-card') {
            this.title = 'Pay Taxes with Credit Card'
          } else if (this.paymentType === 'echeck') {
            this.title = 'Pay Taxes with eCheck'
          } else {
            this.title = 'Pay Taxes with eWallet'
          }
          break
        case 3:
          this.step = 3
          this.title = 'Please Confirm'
          break
        case 4:
          this.payTaxes(this.paymentData)
          break
        default:
          this.step = 1
          this.title = 'Pay Taxes'
      }
    },
    checkPayEwallet () {
      if (this.user.seller_type_id === 1 && this.$getGlobal('affiliate_ewallet_taxes_balance').show) {
        return true
      }
      if (this.user.seller_type_id === 2 && this.$getGlobal('reseller_ewallet_taxes_balance').show) {
        return true
      }
      return false
    },
    checkPayCreditCard () {
      if (this.user.seller_type_id === 1 && this.$getGlobal('affiliate_ewallet_taxes_credit_card').show) {
        return true
      }
      if (this.user.seller_type_id === 2 && this.$getGlobal('reseller_ewallet_taxes_credit_card').show) {
        return true
      }
      return false
    },
    checkPayEcheck () {
      if (this.user.seller_type_id === 1 && this.$getGlobal('affiliate_ewallet_taxes_ach').show) {
        return true
      }
      if (this.user.seller_type_id === 2 && this.$getGlobal('reseller_ewallet_taxes_ach').show) {
        return true
      }
      return false
    },
    setPaymentType () {
      let ewallet = this.checkPayEwallet()
      let card = this.checkPayCreditCard()
      if (ewallet && card) {
        this.paymentModal = true
      } else if (ewallet) {
        this.paymentModal = true
        this.paymentType = 'ewallet'
        this.step = 2
        this.title = 'Pay Taxes with Ewallet'
      } else if (card) {
        this.paymentModal = true
        this.paymentType = 'credit-card'
        this.title = 'Pay Taxes with Credit Card'
        this.step = 2
      }
    }
  },
  components: {
    CpTotalsBanner: require('../../reports/CpTotalsBanner.vue'),
    CpPaymentForm: require('../../payment/CpPaymentForm.vue'),
    CpAchForm: require('../../payment/CpAchForm.vue'),
    CpInput: require('../../../cp-components-common/inputs/CpInput.vue'),
    CpTooltip: require('../../../custom-plugins/CpTooltip.vue')

  }
}
</script>

<style lang="scss">
  .pay-taxes {
    .amount-input {
      max-width: 100px;
    }
    .payment {
      padding-top: 10px;
      padding-left: 10px;
    }
    .credit-card-form {
      padding: 10px;
      overflow: hidden;
      text-align: left;
      input:disabled {
        background-color: #eee;
      }
      label {
        display: block;
      }
      .credit-cards {
        margin-top: 15px;
        margin-bottom: 20px;
      }
    }
    .payment-information {
      padding-left: 15px;
    }
    .taxes {
      padding-bottom: 5px;
    }
    .balance {
      padding-bottom: 15px;
    }
    .exit {
      color: white;
    }
    .cp-modal-header {
      h2 {
        margin: 0px;
      }
      display: flex;
      justify-content: space-between;
    }
    .modal-footer {
      display: flex;
      justify-content: space-between;
      margin: 5px;
    }
    .modal-footer-single {
      display: flex;
      justify-content: flex-end;
      margin: 5px;
    }
  }
</style>
