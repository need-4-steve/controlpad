<template lang="html">
  <div class="custom-order-process-wrapper">
    <p v-if="Step.get('error')"><strong class="errorText">{{ pageError }}</strong></p>
    <!-- gathering user info -->
    <transition name='fade'>
      <section v-show="Step.get('one')">
        <div v-if="cart.type === 'rep-transfer'">
          <h3>{{ 'Transfer to ' + $getGlobal('title_rep').value }}</h3>
          <p>{{ 'Email and numeric ID for ' + $getGlobal('title_rep').value + ' is required, and must match their account.' }}</p>
        </div>
        <div v-else class="customer-search">
          <h3>Add or Find Customers to Credit Card Payment</h3>
          <form class="cp-form-registration">
            <cp-typeahead
            @input="setSelectedCustomer"
            :options="users"
            :clear-dropdown="clearDropdown"
            :name-value="{ name: 'full_name', value: 'id' }"
            @options-cleared="function (val) { users = val }"
            :search-function="searchUsers"
            ></cp-typeahead>
          </form>
        </div>
        <div class="customer-info">
          <div class="col-wrapper">
            <div class="left-col">
              <h4>Contact Information:</h4>
              <div class="cp-form-inverse">
                <cp-input
                  label="First Name"
                  type="text"
                  v-model="customer.first_name"
                  :error="validationErrors['first_name']"></cp-input>
                <cp-input
                  type="text"
                  label="Last Name"
                  v-model="customer.last_name"
                  :error="validationErrors['last_name']"></cp-input>
                <cp-input
                  v-show="Auth.hasAnyRole('Superadmin', 'Admin')"
                  type="text"
                  :read-only="true"
                  label="Public ID"
                  v-model="customer.public_id"></cp-input>
                <cp-input
                  type="text"
                  label="ID"
                  :read-only="cart.type !== 'rep-transfer'"
                  :error="validationErrors['id']"
                  v-model="customer.id"></cp-input>
                <cp-input-mask
                  label="Phone Number"
                  type="text"
                  mask="###-###-####"
                  :error="validationErrors['customer.phone_number']"
                  v-model="customer.phone_number"></cp-input-mask>
                <cp-input
                  type="email"
                  label="Email"
                  v-model="customer.email"
                  @input="clearId()"
                  :error="validationErrors['email']"></cp-input>
              </div>
            </div>
            <div class="right-col">
              <div class="cp-form-inverse" v-show="orderType !== 'cp-send-order'">
                <h4>Shipping Information:</h4>
                  <cp-address-form ref="shippingAddressForm"
                  address-type="addresses.shipping"></cp-address-form>
              </div>
            </div>
          </div>
        </div>
        <div class="customer-order-button-wrapper-single">
            <button v-show="orderType !== 'cp-send-order'" class="cp-button-standard" @click="checkUserInfo()"> Continue to Billing</button>
            <button v-show="orderType === 'cp-send-order'"  class="cp-button-standard" @click="checkInvoice()"> Continue</button>
        </div>
      </section>
    </transition>

    <!-- gathering user billing info -->
    <transition name='fade'>
      <section class="billing-information" v-show="Step.get('two')">
        <div class="cp-form-inverse credit">
          <label for="">Billing Information:</label>
          <cp-address-form ref="billingAddressForm"
            :disabled="billingIsShipping"
            address-type="addresses.billing"></cp-address-form>
          <div class="same-as">
           <input type="checkbox" v-model="billingIsShipping">Check if same as shipping address
         </div>
        </div>
        <div class="customer-order-button-wrapper">
            <button class="cp-button-standard" @click="prev()">Prev</button>
            <button class="cp-button-standard " @click="checkBillingInfo()" :disabled="processingBillingInfo">Continue</button>
        </div>
      </section>
    </transition>
    <transition name='fade'>
      <section v-show="Step.get('three')">
        <div class="custom-order-checkout-wrapper">
          <h4>Payment information: </h4>
          <div class="table-input">
            <div class="table">
              <table class="cp-table-inverse">
                <tr>
                  <th>PRODUCT</th>
                  <th>SIZE</th>
                  <th>QUANTITY</th>
                  <th>PRICE</th>
                </tr>
                <tr v-for="line in (orderType === 'cp-send-order' ? cart.lines : checkout.lines)">
                  <td>{{line.item_id ? line.items[0].product_name : line.bundle_name}}</td>
                  <td>{{line.item_id ? line.items[0].option : ''}}</td>
                  <td>{{line.quantity}}</td>
                  <td v-if="line.discount">{{ (line.quantity - line.discount) * line.price | currency }}</td>
                  <td v-if="!line.discount">{{ line.quantity * line.price | currency }}</td>
                </tr>
              </table>
            </div>
          </div>
          <div class="details-section" v-show="orderType !== 'personal_use'">
            <div class="payment-form-custom-order" v-if="orderType === 'cp-credit-card'">
              <div class="cp-form-inverse">
                <cp-credit-card-form ref="ccForm" :disabled="checkout.total == 0"></cp-credit-card-form>
              </div>
            </div>
            <div class="payment-form-custom-order" v-else-if="orderType === 'cp-send-order'">
                <cp-pay-send-order ref="sendOrderForm"></cp-pay-send-order>
            </div>
            <div class="payment-totals-section">
              <div class="details-titles-section">
                <div>Subtotal: </div>
                <div>Discount: </div>
                <div>Shipping: </div>
                <div>Estimated Tax: </div>
                <div><b>Total: </b></div>
              </div>
              <div class="details-totals-section" v-if="orderType === 'cp-send-order'">
                <div>{{ cartSubtotal | currency }}</div>
                <div>{{ - cartDiscount | currency}}</div>
                <div>{{ shipping | currency }}</div>
                <div>{{ cartTax | currency }}</div>
                <div><b>{{ cartTotal | currency }}</b></div>
              </div>
              <div class="details-totals-section" v-else>
                <div>{{ checkout.subtotal | currency }}</div>
                <div>{{ - checkout.discount | currency}}</div>
                <div>{{ checkout.shipping | currency }}</div>
                <div>{{ checkout.tax | currency }}</div>
                <div><b>{{ checkout.total | currency }}</b></div>
              </div>
            </div>
          </div>
          <form
            v-if="showApplyCoupon"
            class="coupon-input-section cp-form-inverse"
            @submit.prevent>
            <div class="col">
              <cp-input
              type="text"
              placeholder="Do you have a coupon code?"
              v-model="couponCode"></cp-input>
            </div>
            <div class="col">
              <button class="cp-button-standard" @click="applyCoupon()">Apply Coupon</button>
            </div>
          </form>
          <div class="coupon-applied-section">
            <span v-if="checkout.discount">A discount of <i>{{ checkout.discount | currency }}</i> was applied to your order.</span>
            <span v-else-if="cartDiscount">A discount of <i>{{ cartDiscount | currency }}</i> was applied to your order.</span>
          </div>
        </div>
        <div class="customer-order-button-wrapper">
            <button  class="cp-button-standard" @click="prev(); processingBillingInfo = false">Prev</button>
            <button
            v-if="orderType !== 'cp-send-order'"
            v-show="!processingOrder"
            class="cp-button-standard place-order-button"
            @click="makeCustomOrder()"
            :disabled="processingOrder">Create Order</button>
            <button
            v-if="orderType === 'cp-send-order'"
            v-show="!processingOrder"
            class="cp-button-standard place-order-button"
            @click="makeCustomOrder()"
            :disabled="processingOrder">Send Order</button>
            <button
            v-show="processingOrder"
            class="cp-button-standard place-order-button"
            @click="makeCustomOrder()"
            :disabled="processingOrder">Processing</button>
          </div>
      </section>
    </transition>
    <transition name='fade'>
      <section v-show="Step.get('four')">
        <cp-purchase-summary :order="order" :invoice="invoice"></cp-purchase-summary>
      </section>
    </transition>
  </div>
</template>

<script>
const Auth = require('auth')
const Checkout = require('../../resources/CheckoutAPIv0.js')
const Users = require('../../resources/UserApiv0.js')
const Step = require('../../libraries/step.js')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      users: [],
      couponCode: '',
      coupon: null,
      Auth: Auth,
      Step: Step,
      pageError: null,
      validationErrors: {},
      order: {},
      invoice: {},
      customer: {},
      cartTax: 'TBD',
      billingIsShipping: false,
      checkout: {},
      processingOrder: false,
      processingBillingInfo: false
    }
  },
  props: {
    cart: {
      type: Object,
      required: true
    },
    discount: {},
    shipping: {
      default () {
        return 0.00
      }
    },
    orderType: {
      type: String,
      required: true
    },
    taxExempt: {
      type: Boolean,
      required: false,
      default () {
        return false
      }
    }
  },
  mounted () {
    this.initSteps()
    if (this.orderType === 'cp-personal-use') {
      this.getUserInformation()
    }
    this.estimateShipping()
  },
  watch: {
    billingIsShipping: 'copyAddress'
  },
  methods: {
    prev() {
      if (this.orderType === 'cp-send-order') {
        Step.skipTo('one')
      } else {
        Step.previous()
      }
    },
    checkUserInfo () {
      if (this.validateUserInfo()) {
        if (this.cart.type === 'rep-transfer') {
          this.verifyRepCustomer((customer) => {Step.next()})
        } else {
          return Step.next()
        }
      }
    },
    checkInvoice () {
      if (this.validateInvoice()) {
        this.$refs.sendOrderForm.setCustomer(this.customer)
        if (this.cart.type === 'rep-transfer') {
          this.verifyRepCustomer((customer) => {
            Step.skipTo('three')
          })
        } else {
          Step.skipTo('three')
        }
      }
    },
    verifyRepCustomer (callback) {
      Users.getByIdAndEmail(this.customer.id, this.customer.email)
        .then((response) => {
          if (response.code === 404) {
            this.validationErrors = {id: ['ID and email not verified'], email: ['ID and email not verified']}
            return this.$toast('User not found')
          }
          if (response.error) {
            return this.$toast((response.message ? response.message : 'Unexpected error.'), { error: true })
          }
          if (response.role !== 'Rep') {
            this.validationErrors = {id: ['This user must be a rep'], email: ['This user must be a rep']}
            return this.$toast('User not a rep')
          }
          this.customer.pid = response.pid
          callback(response)
        })
    },
    checkBillingInfo () {
      if (this.orderType === 'cp-send-order') {
        Step.next()
        return false
      }
      this.processingBillingInfo = true
      if (this.validateBillingInfo()) {
        return this.createCheckout()
      }
      this.processingBillingInfo = false
    },
    createCheckout () {
      let request = {
        shipping_address: this.$refs.shippingAddressForm.getAddress(),
        billing_address: this.$refs.billingAddressForm.getAddress(),
        discount: this.discount,
        shipping: this.shipping,
        tax_exempt: this.taxExempt
      }
      if (this.checkout.pid) {
        Checkout.update(request, this.checkout.pid)
          .then((response) => {
            if (response.error) {
              this.$toast((response.message ? response.message : 'Unexpected error.'), { error: true })
              this.processingBillingInfo = false
              return
            }
            this.checkout = response
            Step.next()
          })
      } else {
        Checkout.createFromCart(request, this.cart.pid)
          .then((response) => {
            if (response.error) {
              this.$toast((response.message ? response.message : 'Unexpected error.'), { error: true })
              this.processingBillingInfo = false
              return
            }
            this.checkout = response
            Step.next()
          })
      }
    },
    estimateShipping() {
      // custom-affiliate invoice create will need to estimate shipping to show what an invoice will have
      if (this.orderType !== 'cp-send-order' || this.cart.type !== 'custom-affiliate') {
        return false
      }
      Checkout.estimateCartShipping(this.cart.pid)
        .then((response) => {
          if (response.error) {
            this.$toast((response.message ? response.message : 'Unexpected error estimating tax.'), { error: true})
            this.shipping = 0.00
          } else {
            this.shipping = response.shipping
          }
        })
    },
    validateInvoice () {
      this.validationErrors = {}
      let user = this.customer
      let errors = {}
      if (!user.first_name || user.first_name === ' ' || user.first_name === '') {
        errors['first_name'] = ['First name is required']
      }
      if (!user.last_name || user.last_name === ' ' || user.last_name === '') {
        errors['last_name'] = ['Last name is required']
      }
      if (!user.email || user.email === ' ' || user.email === '') {
        errors['email'] = ['Email is required']
      }
      if (this.cart.type === 'rep-transfer' && this.$isBlank(user.id)) {
        errors['id'] = ['ID is required']
      }
      this.validationErrors = errors
      if (Object.keys(errors).length == 0) {
        return true
      }
      return false
    },
    validateUserInfo () {
      this.validationErrors = {}
      let user = this.customer
      let errors = {}
      if (this.isBlank(user.first_name)) {
        errors['first_name'] = ['First name is required']
      }
      if (this.isBlank(user.last_name)) {
        errors['last_name'] = ['Last name is required']
      }
      if (this.isBlank(user.email)) {
        errors['email'] = ['Email is required']
      }
      if (this.cart.type === 'rep-transfer' && this.$isBlank(user.id)) {
        errors['id'] = ['ID is required']
      }
      if (!this.$refs.shippingAddressForm.validate()) {
        return false
      }
      this.validationErrors = errors
      if (Object.keys(errors).length == 0) {
        return true
      }
      return false
    },
    validateBillingInfo () {
      return this.$refs.billingAddressForm.validate()
    },
    clearId () {
      if (this.customer.id !== null && this.cart.type !== 'rep-transfer') {
        this.customer.id = null
      }
    },
    isBlank (str) {
      return (!str || /^\s*$/.test(str));
    },
    copyAddress () {
      if (this.billingIsShipping) {
        this.$refs.billingAddressForm.setAddress(this.$refs.shippingAddressForm.getAddress());
      }
    },
    initSteps () {
      // init steps - only one step should be set to true
      let steps = {
        error: false,
        one: true,
        two: false,
        three: false,
        four: false

      }
      Step.init(steps, 300)
    },
    getUserInformation () {
      Users.get(Auth.getAuthPid, {addresses: true})
        .then((response) => {
          if (!response.error) {
            this.customer = response
          }
        })
    },
    setSelectedCustomer: function (customer) {
      this.customer = customer
      this.$refs.shippingAddressForm.setAddress(this.customer.shipping_address)
      this.$refs.billingAddressForm.setAddress(this.customer.billing_address)
    },
    searchUsers: _.debounce(function (searchTerm) {
      Users.search({addresses: true, search_term: searchTerm, per_page: 25})
        .then(response => {
          if (!response.error) {
            response.data.forEach((user) => {
              user.full_name = user.first_name + ' ' + user.last_name
            })
            this.users = response.data
          }
        })
    }, 500),
    clearDropdown: _.debounce(function () {
      this.users = []
    }, 500),
    makeCustomOrder () {
      this.processingOrder = true
      let request = {
        buyer: this.customer,
        partial_reserve: true
      }
      switch (this.checkout.type) {
        case 'custom-personal':
          request.source = 'Personal Use'
          break
        case 'rep-transfer':
          request.source = 'Rep Transfer'
          break
        default:
          request.source = 'Custom Order'
      }
      switch (this.orderType) {
        case 'cp-send-order':
          request.discount = this.discount
          request.shipping = this.shipping
          request.customer = this.customer
          request.note = this.$refs.sendOrderForm.getNote();
          Checkout.createInvoiceFromCart(request, this.cart.pid)
            .then((response) => {
              if (response.error) {
                this.handleCheckoutError(response)
                return
              }
              this.invoice = response
              Step.skipTo('four')
            })
          return false
        case 'cp-credit-card':
          if (this.checkout.total > 0) {
            if (this.$refs.ccForm.validate()) {
              request.payment = {
                type: 'card',
                amount: this.checkout.total,
                card: this.$refs.ccForm.getCard()
              }
            } else {
              this.processingOrder = false
              return this.$toast('Card info invalid')
            }
          } else {
            request.payment = {
              amount: this.checkout.total,
              type: 'cash',
              cash_type: 'Zero'
            }
          }
          break;
        case 'cp-personal-use':
          request.payment = {
            type: 'cash',
            amount: this.checkout.total
          }
          break;
        case 'cp-cash':
          request.payment = {
            type: 'cash',
            cash_type: 'Cash/Check',
            amount: this.checkout.total
          }
          break;
        default:
          // TODO error
      }
      Checkout.process(request, this.checkout.pid)
        .then((response) => {
          if (!response.error) {
            this.$emit('cart')
            this.order = response.order
            Step.skipTo('four')
          } else {
            this.handleCheckoutError(response)
          }
        })
    },
    applyCoupon () {
      this.processing = true
      if (this.couponCode === '') {
        this.processing = false
        this.$toast('No coupon was entered.')
        return
      }
      if (this.orderType === 'cp-send-order') {
        Checkout.applyCartCoupon({code: this.couponCode}, this.cart.pid)
          .then((response) => {
            this.processing = false
            if (response.error) {
              this.$toast('The provided coupon code could not be validated.', { error: true })
              return
            }
            this.coupon = response.coupon
            this.estimateShipping()
            this.$toast('Discount applied')
          })
      } else {
        Checkout.update({coupon_code: this.couponCode}, this.checkout.pid)
          .then((response) => {
            this.processing = false
            if (response.error) {
              this.$toast('The provided coupon code could not be validated.', { error: true })
              return
            }
            this.checkout = response
            this.$toast('Discount applied')
          })
      }
    },
    handleCheckoutError (response) {
      let message = null
      if (response.code == 422) {
        if (response.message.result_code) {
          message = response.message.message
          switch (response.message.result_code) {
            case 2:
              this.pageError = "Some or all requested inventory is either reserved or sold. Order could not be created."
              Step.skipTo('error')
              break;
            case 3:
              this.pageError = "All requested inventory is either reserved or sold."
              Step.skipTo('error')
              break;
            case 4:
              window.alert('Some products were not available. Checkout updated to have remaining products and price.')
              this.checkout = response.message.checkout
              break;
            case 5:
              // message should be set to the transaction result already
              break;
            case 6:
              Step.skipTo('two')
              break;
            case 8:
              window.alert('Coupon already used. Checkout updated to remove coupon.')
              this.checkout = response.message.checkout
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
      this.validationErrors = response.message
      this.processingOrder = false
    }
  },
  computed: {
    cartSubtotal () {
      let total = 0
      for (var i = 0; i < this.cart.lines.length; i++) {
        total = total + this.cart.lines[i].price * this.cart.lines[i].quantity
      }
      return total
    },
    cartDiscount () {
      if (this.coupon) {
        discount = 0.00
        if (this.coupon.is_percent) {
          discount = this.cartSubtotal * (this.coupon.amount / 100)
        } else {
          discount = this.coupon.amount
        }
        if (discount > this.cartSubtotal) {
          discount = this.cartSubtotal
        }
        return discount
      } else {
        return this.discount
      }
    },
    cartTotal () {
      return (parseFloat(this.cartSubtotal ? this.cartSubtotal : '0') - parseFloat(this.cartDiscount ? this.cartDiscount : '0') + parseFloat(this.shipping ? this.shipping : '0'))
    },
    showApplyCoupon () {
      if (this.discount > 0) {
        return false
      }
      if (['custom-corp','custom-affiliate'].includes(this.cart.type)) {
        return this.$getGlobal('corp_coupons').show
      }
      if (this.cart.type === 'custom-retail') {
        return this.$getGlobal('reseller_coupons').show
      }
      return false;
    }
  },
  components: {
    CpCreditCardForm: require('../payment/CpCreditCardForm.vue'),
    CpPaySendOrder: require('../store/CpPaySendOrder.vue'),
    CpPayCash: require('../store/CpPayCash.vue'),
    CpAddressForm: require('../addresses/CpAddressForm.vue'),
    CpTypeahead: require('../../cp-components-common/inputs/CpTypeahead.vue'),
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpPurchaseSummary: require('../store/CpPurchaseSummary.vue'),
    CpInputMask: require('../../cp-components-common/inputs/CpInputMask.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.expand-enter-active, .expand-leave-active {
  transition: all 1s ease;
  height: auto;
  padding: 10px;
  overflow: hidden;
}
/* .expand-enter defines the starting state for entering */
/* .expand-leave-active defines the ending state for leaving */
.expand-enter, .expand-leave-active {
  height: 0;
  padding: 0 10px;
  opacity: 0;
}

.custom-order-process-wrapper {
  .customer-info {
    background-color: $cp-lighterGrey;
    padding-left: 20px;
    padding-right: 20px;
    margin-top: 0px !important;
  }
  .customer-search {
    padding: 20px;
    background: $cp-lighterGrey;
    position: relative;
    display: block;
    -webkit-display: block;
    h3 {
      margin: 10px 0 20px;
    }
  }
  .billing-information {
    background-color: $cp-lighterGrey;
    .credit {
      padding: 30px;
    }
    .same-as {
      text-align: left;
      input {
        width: auto;
        margin-right: 5px;
        height: initial;
      }
    }
  }
  .customer-order-button-wrapper {
    margin: 0px;
    padding: 10px 20px 10px 20px;
    background-color: $cp-lighterGrey;
    display: flex;
    justify-content: space-between;
  }
  .customer-order-button-wrapper-single {
    margin: 0px;
    padding: 10px 20px 10px 20px;
    background-color: $cp-lighterGrey;
    display: flex;
    justify-content: flex-end;
  }
  .custom-order-checkout-wrapper {
    padding: 20px;
    background-color:  $cp-lighterGrey;
    .details-section {
      display: flex;
      justify-content: space-between;
      margin: 10px 0px 10px 0px;
      .payment-form-custom-order {
        margin: 5px 10px 5px 10px;
        width: 50%;
      }
    }
    .payment-totals-section {
      display: flex;
      justify-content: space-between;
      width: 50%;
      margin: 5px 5px 5px 5px;
      .details-titles-section {
        div {
          padding: 5px;
        }
      }
      .details-totals-section {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        div {
          padding: 5px;
        }
      }
    }
  }
    @media (max-width: 768px) {
      .custom-order-checkout-wrapper {
        .details-section {
          display: block;
          .payment-form-custom-order {
            width: 100%;
          }
          .payment-totals-section {
            width: 100%;
            margin: 0px;
            margin-top: 10px;

            .details-titles-section {
              div {
                padding: 5px;
              }
            }
            .details-totals-section {
              display: flex;
              flex-direction: column;
              align-items: flex-end;
              div {
                padding: 5px;
              }
            }
          }
        }
      }
    }
    @media (max-width: 768px) {
      .customer-order-button-wrapper {
      display: block !important;
          button {
            margin: 5px;
            width: 100%;
          }
      }
    }
    @media (max-width: 768px) {
      .customer-order-button-wrapper-single {
        display: block;
          button {
            margin: 5px;
            width: 100%;
          }
      }
  }
}
</style>
