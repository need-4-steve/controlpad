<template lang="html">
  <section class="make-a-payment-section" v-if="checkout">
    <div>
      <div class="payment-flex-container">
        <div class="order-summary-section col">
          <h4 style="margin-top: 0px">Confirm Your Order</h4>
          <table class="cp-table-inverse">
            <tr>
              <th>PRODUCT</th>
              <th>SIZE</th>
              <th>QUANTITY</th>
              <th>PRICE</th>
            </tr>
            <tr v-for="line in checkout.lines">
              <td>{{line.item_id ? line.items[0].product_name : line.bundle_name}}</td>
              <td>{{line.item_id ? line.items[0].option : ''}}</td>
              <td>{{line.quantity}}</td>
              <td v-if="line.discount">{{ (line.quantity - line.discount) * line.price | currency }}</td>
              <td v-if="!line.discount">{{ line.quantity * line.price | currency }}</td>
            </tr>
          </table>
          <div class="payment-totals-section">
            <div class="total-line">
              <span>Subtotal</span>
              <span class="">
                {{ checkout.subtotal | currency }}
              </span>
            </div>
            <div class="total-line" v-if="checkout.discount > 0">
              <span> Discount</span>
              <span class="">
                {{ -checkout.discount | currency }}
              </span>
            </div>
            <div class="total-line">
              <span>Estimated Tax</span>
              <span class="">
                {{ checkout.tax | currency }}
              </span>
            </div>
            <div class="total-line">
              <span>Shipping</span>
              <span class="">
                {{ checkout.shipping | currency }}
              </span>
            </div>
            <div class="total-line">
              <span>Total</span>
              <span class="">
                {{ checkout.total | currency }}
              </span>
            </div>
          </div>
        <div class="cp-form-inverse">
          <div class="apply-coupon-wrapper" v-if="checkout.couponable">
            <cp-input
              class=""
              type="text"
              :error="couponErrors['coupon_code']"
              placeholder="Do you have a promotional code?"
              v-model="couponCode">
            </cp-input>
            <button class="cp-button-standard" :disabled="this.processing" @click="applyCoupon()">Apply</button>
          </div>
        </div>
        <div class="coupon-applied-section">
          <span v-if="checkout.discount">A discount of <i>{{ checkout.discount | currency }}</i> was applied to your order.</span>
        </div>
      </div>
      <div class="payment-method-section col cp-form-inverse">
        <div v-if="paymentTypes.length > 1">
          <span>Select payment type</span><br>
          <select class="payment-selector" v-model="selectedPaymentType" @change="setPaymentType(selectedPaymentType)">
            <option v-for="paymentType in paymentTypes" :value="paymentType">{{ paymentType.display }}</option>
          </select>
        </div>
        <cp-credit-card-form v-show="selectedPaymentType.type === 'credit-card'" ref="ccForm" :disabled="checkout.total == 0"></cp-credit-card-form>
        <div class="payment-message-section" v-if="ewalletAllowed" v-show="selectedPaymentType.type === 'e-wallet'">
          <span><strong>Pay using eWallet</strong></span><br>
          <div>Balance: {{ this.repEwallet.balance | currency('floor') }}</div>
          <div>Payment: {{ this.checkout.total | currency }}</div>
          <div>Remaining: {{ this.repEwallet.balance - this.checkout.total | currency('floor') }}</div>
        </div>
        <cp-credit-card-form v-show="selectedPaymentType.type === 'card-token'" ref="cardTokenForm" :disabled="true"></cp-credit-card-form>
        <div class="payment-message-section" v-if="selectedPaymentType.type === 'personal-cash'">
          <span><strong>Tax amount will be added to your sales tax owed balance</strong></span>
        </div>
        <div class="payment-message-section" v-if="selectedPaymentType.type === 'zero-cash'">
          <span><strong>No payment required. Order total is $0.00</strong></span>
        </div>
        <small>By clicking Place Order below, you agree to the following <a href="/return-policy" class="agree" target="_blank">terms.</a> Click <a :href="$getGlobal('return_policy').value" target="_blank">here</a> for the return policy</small>
      </div>
    </div>
    </div>
  </section>
</template>

<script>
const Auth = require('auth')
const Checkout = require('../../resources/CheckoutAPIv0.js')
const Users = require('../../resources/UsersAPIv0.js')
const Payman = require('../../resources/PaymanAPI.js')

module.exports = {
  data () {
    return {
      couponCode: '',
      couponErrors: {},
      repEwallet: null,
      cardToken: null,
      selectedPaymentType: {type: null, display: null},
      ewalletAllowed: false,
      paymentTypes: [],
      checkout: null,
      checkoutLoaded: false
    }
  },
  props: {
    processing: {
      type: Boolean,
      required: false,
      default () {
        return false
      }
    }
  },
  methods: {
    validate () {
      // Only validate credit card form
      return (this.selectedPaymentType.type !== 'credit-card' || this.$refs.ccForm.validate())
    },
    load () {
      if (this.checkout.type === 'custom_personal') {
        // Don't pull ewallet balance and card token
        return true
      }
      if (this.$getGlobal('wholesale_ewallet').show) {
        Payman.getEWalletBalance(Auth.getAuthId())
          .then((response) => {
            if (!response.error) {
              this.selectedPaymentType = {type: null, display: null}
              this.repEwallet = response
              this.updatePaymentTypes()
            }
          })
      }
      if (this.$getGlobal('wholesale_card_token').show) {
        Users.getCardToken(Auth.getAuthPid())
          .then((response) => {
            if (!response.error) {
              let expirationDate = moment(response.expiration, "MMYY")
              if (moment().isAfter(expirationDate.add(1, 'months'))) {
                return
              }
              this.selectedPaymentType = {type: null, display: null}
              this.cardToken = response
              this.updatePaymentTypes()
            }
          })
      }
    },
    setCheckout (checkout) {
      this.checkout = checkout
      // The first time checkout is set run load()
      if (!this.checkoutLoaded) {
        this.checkoutLoaded = true
        this.load()
      }
      this.updatePaymentTypes()
    },
    setCouponErrors (errors) {
      this.couponErrors = errors
    },
    setPaymentType (type) {
      this.selectedPaymentType = type
      if (this.selectedPaymentType.type === 'card-token') {
        let expirationDate = moment(this.cardToken.expiration, "MMYY")
        this.$refs.cardTokenForm.setCard({
          name: '',
          number: this.cardToken.card_digits,
          year: expirationDate.year(),
          month: expirationDate.month(),
          code: ''
        })
      }
    },
    updatePaymentTypes () {
      if (!this.checkout) {
        return false
      }
      let types = []
      if (this.checkout.total > 0) {
        if (this.checkout.type === 'custom-personal') {
          this.ewalletAllowed = false
          let selectedType = {type: 'personal-cash', display: 'None'}
          this.setPaymentType(selectedType)
          this.paymentTypes = [selectedType]
          return true
        }
        // Check that e-wallet is available
        if (this.repEwallet != null && this.repEwallet.balance >= this.checkout.total) {
          types.push({type: 'e-wallet', display: 'eWallet'})
          this.ewalletAllowed = true
        } else {
          if (this.selectedPaymentType.type === 'e-wallet') {
            this.selectedPaymentType = {type: null, display: null}
          }
          this.ewalletAllowed = false
        }
        // Check that card-token is available
        if (this.cardToken != null) {
          types.push({type: 'card-token', display: 'Saved Card: ' + this.cardToken.card_digits})
        } else if (this.selectedPaymentType.type === 'card-token') {
          this.selectedPaymentType = {type: null, display: null};
        }
        // Add credit card form
        types.push({type: 'credit-card', display: 'Credit Card'})
        // Select a default if needed
        if (this.selectedPaymentType.type == null || this.selectedPaymentType.type === 'zero-cash') {
          this.setPaymentType(types[0])
        }
      } else {
        types.push({type: 'zero-cash', display: 'Cash'})
        this.setPaymentType(types[0])
        this.ewalletAllowed = false
      }
      this.paymentTypes = types
    },
    getPayment () {
      switch (this.selectedPaymentType.type) {
        case 'personal-cash':
        case 'zero-cash':
          return {
            type: 'cash',
            cash_type: 'Zero',
            amount: this.checkout.total
          }
        case 'credit-card':
          return {
            type: 'card',
            card: this.getCard(),
            amount: this.checkout.total
          }
        case 'e-wallet':
          return {
            type: 'e-wallet',
            amount: this.checkout.total
          }
        case 'card-token':
          return {
            type: 'card-token',
            card_token: this.cardToken.token,
            gateway_customer_id: this.cardToken.gateway_customer_id,
            amount: this.checkout.total
          }
        default:
          return null
      }
    },
    getCard () {
      return this.$refs.ccForm.getCard()
    },
    applyCoupon () {
      this.$emit('set-processing', true)
      Checkout.update({coupon_code: this.couponCode}, this.checkout.pid)
        .then((response) => {
          if (response.error) {
            this.$toast((response.message ? response.message : 'Unexpected Error'), { error: true })
          } else {
            this.$emit('set-checkout', response)
          }
          this.$emit('set-processing', false)
        })
    }
  },
  components: {
    CpCreditCardForm: require('../payment/CpCreditCardForm.vue'),
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
.make-a-payment-section {
  .total-line {
    display: flex;
    span {
      flex: 1;
    }
  }
  .payment-totals-section {
    text-align: right;
    padding: 10px;
  }
  .place-order-button {
    float: right;
  }
  .payment-flex-container {
    display: flex;
    .col:first-child {
      flex: auto;
      width: 60%;
    }
    .col:last-child {
      width: 35%;
      flex: initial;
    }
    .col {
      // flex: 1;
      padding-top: 10px;
      padding-bottom: 10px;
      padding-left: 10px;
      padding-right: 10px;
    }
    .payment-method-section {
      position: relative;
      padding-bottom: 50px;
      padding-top: 0px;
      small {
        position: absolute;
        bottom: 0;
      }
    }
  }
  .coupon-input-section {
    display: flex;
    max-width: 500px;
    .col {
      padding: 0px;
      flex: 1;
      button {
        margin: 5px;
      }
    }
  }
  .apply-coupon-wrapper {
    display: flex;
    justify-content: space-between;
    span, input {
        width: 97%;
        margin: 0px;
    }

  }
}
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
@media (max-width: 768px) {
  .make-a-payment-section {
    .payment-flex-container {
      display: block;
      .col:first-child {
        flex: auto;
        width: auto;
      }
      .col:last-child {
        width: auto;
        flex: initial;
      }
      .col {
        // flex: 1;
        padding: 10px;
      }
    }
  }
}
</style>
