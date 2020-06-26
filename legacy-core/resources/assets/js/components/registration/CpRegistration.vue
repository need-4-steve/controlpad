<template lang="html">
<div class="registration-wrapper">
  <section class="no-registration" v-if="!$getGlobal('register_without_controlpad_api').show">
    <div>
    Registration is not available at this time.
    <p>Click <a href="/login">Here</a> to sign in.</p>
    </div>
  </section>
  <div v-if="$getGlobal('register_without_controlpad_api').show">
    <transition name='fade'>
        <section class="registration-step step-form-wrapper cp-panel-standard" v-show="Step.get('one')">
            <div class="reg-form">
                <div class="cp-form-inverse registration-form">
                    <cp-input
                    v-if="$getGlobal('require_registration_code').show"
                    label="Registration Code"
                    :error="validationErrors['registration_code']"
                    type="text"
                    v-model="registration_code"
                    placeholder=""></cp-input>
                    <!-- appears when the commission engine is in use -->
                    <cp-input
                    v-if="$getGlobal('collect_sponsor_id').show"
                    :tooltip="'If you have a sponsor you can receive a link or ID directly from her or him.'"
                    :label="'Sponsor ID'"
                    :subtext="'(optional)'"
                    :setting="$getGlobal('require_sponsor_id_on_registration').show"
                    :error="validationErrors['sponsor_id']"
                    type="text"
                    v-model="sponsorIdValue"
                    placeholder=""
                    :set-disabled="disableSponsor"></cp-input>
                    <cp-input
                    label="First Name"
                    :error="validationErrors['user.first_name']"
                    type="text"
                    v-model="user.first_name"
                    placeholder=""
                    :set-disabled="token"></cp-input>
                    <cp-input
                    label="Last Name"
                    :error="validationErrors['user.last_name']"
                    type="text"
                    v-model="user.last_name"
                    placeholder=""
                    :set-disabled="token"></cp-input>
                    <!-- phone -->
                    <cp-input-mask
                    v-if="$getGlobal('collect_phone_on_registration').show"
                    label="Phone Number"
                    type="text"
                    mask="###-###-####"
                    :error="validationErrors['user.phone']"
                    v-model="user.phone">
                    </cp-input-mask>
                    <cp-input
                     v-if="$getGlobal('replicated_site').show"
                    label="Store Name"
                    :subtext="'(Public Id)'"
                    type="text"
                    v-model="user.public_id"
                    placeholder=""
                    @input="checkPublicId(user.public_id)"></cp-input>
                    <span :class="{'cp-warning-message': !validPublicId, 'cp-success-message': validPublicId }">{{ validPublicIdMessage }}</span>
                    <div class="store-name" v-if="$getGlobal('replicated_site').show">
                        <label>Preview: </label>
                        <span><strong>{{ fullStoreURL }}</strong></span>
                    </div>
                    <cp-input
                    label="Email"
                    :error="validationErrors['user.email']"
                    type="text"
                    v-model="user.email"
                    :set-disabled="token"></cp-input>
                    <div class="show-hide-password">
                        <cp-input
                        label="Password"
                        :error="validationErrors['user.password']"
                        type="password"
                        v-model="user.password"
                        v-show="!showPassword"
                        placeholder=""></cp-input><button class="cp-button-standard" v-show="!showPassword" @click="showPassword=true">Show</button>
                        <cp-input
                        label="Password"
                        :error="validationErrors['user.password']"
                        type="text"
                        v-model="user.password"
                        v-show="showPassword"
                        placeholder=""></cp-input><button class="cp-button-standard" v-show="showPassword" @click="showPassword=false">Hide</button>
                    </div>
                </div>
            </div>
            <div class="sign-in">
                <button class="cp-button-standard" @click="validateUserInfo()">Get Started</button>
                <div class="login-redirect">
                    Already have an account? <a href="/login">Sign in</a>
                </div>
            </div>
        </section>
    </transition>

      <!-- STEP 2: AGREE TO TERMS SELECT PLAN AND/OR STARTER KIT -->
      <transition name='fade'>
          <section class="registration-step" v-show="Step.get('two')">
              <h2>Choose a subscription</h2>
              <div class="selection-container">
                  <div class="cp-panel-border select-plan" :class="{ selectedplan: selectedPlan.id === plan.id}" v-for="(plan, index) in plans">
                      <div>
                          <p class="plan-price">{{ plan.price.price | currency }}</p>
                          <p><strong>{{ parseDuration(plan.duration) }}</strong></p>
                          <div class="membership-details">
                              <h2>{{plan.title}}</h2>
                              <div v-html="plan.description"></div>
                          </div>
                      </div>
                      <button class="cp-button-standard" :class="{ selectedplan: selectedPlan.id === plan.id}" @click="selectPlan(plan)">Choose</button>
                  </div>
              </div>
              <div v-if="kits.length > 0" class="selection-container" v-show="selectedPlan.id" transition="expand">
                  <h2>Select a starter kit</h2>
                  <div class="cp-panel-border select-plan" v-for="(kit, index) in kits">
                      <div class="">
                        <p class="plan-price" v-if="kit.wholesale_price > 0">{{ kit.wholesale_price | currency }}</p>
                        <p class="plan-price" v-else>Free</p>
                        <p><strong>{{ kit.name }}</strong></p>
                        <p> {{ kit.long_description }}</p>
                      </div>
                      <button class="cp-button-standard" :class="{ selectedplan: selectedKit.id === kit.id }" @click="selectKit(kit)">Choose</button>
                  </div>

            </div>
              </section>
      </transition>
      <!-- STEP 3: MAKE A PAYMENT -->
      <transition name='fade'>
          <section class="registration-step step-form-wrapper cp-panel-standard" v-if="Step.get('three')">
            <div class="payment-form">
              <div class="reg-form" v-if="shippingRequired">
                  <h2>Shipping Information</h2>
                  <form class="cp-form-inverse registration-form" @submit.prevent>
                      <cp-address-form
                      :address="paymentData.addresses.shipping"
                      address-type="shipping_address"
                      :validation-errors="validationErrors"
                      :hide-name-field="true"
                      >
                    </cp-address-form>
                      <input v-if="selectedPlan.price.price+shippingCost>0" class="agree" type="checkbox" @click="copyAddress()" name="same-as"><span v-if="selectedPlan.price.price+shippingCost>0">Billing is the same as shipping</span>
                  </form>
              </div>
              <div class="reg-form" v-if="billingRequired">
                <h2>Billing Information</h2>
                <form class="cp-form-inverse registration-form" @submit.prevent>
                    <cp-address-form
                      :address="paymentData.addresses.billing"
                      address-type="billing_address"
                      :validation-errors="validationErrors"
                      :hide-name-field="true"></cp-address-form>
                </form>
              </div>
            </div>
            <div class="payment-buttons">
              <button class="cp-button-standard" @click="Step.previous()">Back</button>
              <button class="cp-button-standard" @click="validateAddresses()">Next</button>
            </div>
          </section>
          <section class="registration-step step-form-wrapper cp-panel-standard" v-if="Step.get('four')">
            <cp-registration-payment
              ref="paymentForm"
              :selectedKit="selectedKit"
              :selectedPlan="selectedPlan"
              :shippingAmount="shippingCost"
              :planTax="subscriptionTax">
            </cp-registration-payment>
            <div class="payment-buttons">
              <button class="cp-button-standard" @click="Step.previous()">Back</button>
              <button class="cp-button-standard" @click="submitPayment()" v-show="!processing && selectedPlan.price.price+shippingCost>0">Make Your Payment</button>
              <button class="cp-button-standard" @click="submitPayment()" v-show="!processing && selectedPlan.price.price+shippingCost==0">Submit</button>
              <button class="cp-button-standard" v-show="processing">Processing...</button>
            </div>
            <div v-if="validationErrors.payment" class="errorText">
              Payment Error: {{ validationErrors.payment[0] }}
            </div>
          </section>
      </transition>
    </div>
  </div>
</div>
</template>

<script>
const marked = require('marked')
const Step = require('../../libraries/step.js')
const Payments = require('../../resources/payments.js')
const Inventory = require('../../resources/InventoryAPIv0.js')
const { states } = require('../../resources/states.js')
const Users = require('../../resources/users.js')
const Shipping = require('../../resources/shipping.js')
const Subscription = require('../../resources/subscription.js')
const _ = require('lodash')
const Auth = require('auth')
const moment = require('moment')

module.exports = {
  data () {
    return {
      sponsorIdValue: this.sponsorId,
      registration_code: '',
      shippingCost: 0,
      user: {
        email: '',
        password: '',
        public_id: '',
        first_name: '',
        last_name: '',
        phone: ''
      },
      ppa: {
        businessAddress: {},
        businessAccount: {}
      },
      host_name: window.location.hostname,
      showPassword: false,
      kits: [],
      states: states,
      years: [],
      Step: Step,
      validPublicId: false,
      validPublicIdMessage: '',
      validationErrors: {},
      selectedPlan: {
        id: null,
        free_trial_time: 0
      },
      selectedKit: {
        id: null,
        wholesale_price: {
          price: 0
        }
      },
      paymentData: {
        user: {},
        payment: {
          month: null,
          year: null
        },
        addresses: {
          billing: { address_2: '' },
          shipping: { address_2: '' }
        },
        starter_kit_id: null,
        user_id: null,
        subtotal_price: null
      },
      processing: false,
      disableSponsor: false,
      subscriptionTax: 0,
      taxInvoicePid: null,
      plans: []
    }
  },
  props: {
    token: {
      default: null
    },
    sponsorId: {
      default: null
    }
  },
  filters: {
    marked: marked
  },
  mounted () {
    if (this.sponsorId) {
      this.disableSponsor = false
    }
    if (this.token) {
      this.getRegistrationToken(this.token)
    }
    // if a user token is a user object of a preregistered user
    // through an external system that needs to finish registration here
    this.getKits()
    this.initSteps()
    this.getPlans()
    // TODO: Use when Oath is working again
    // if (this.oauthUser && this.oauthUser.email && this.oauthUser.email.length > 0) {
    //   this.user.email = this.oauthUser.email
    //   this.user.first_name = this.oauthUser.first_name
    //   this.user.last_name = this.oauthUser.last_name
    // }
  },
  computed: {
    fullStoreURL () {
      return this.$getGlobal('rep_url').value.replace('%s', this.user.public_id || '')
    },
    shippingRequired () {
      return (this.selectedKit !== null && this.selectedKit.id !== null) ||
              (this.selectedPlan.seller_type_id == 2 && this.$getGlobal('reseller_purchase_inventory').show) ||
              (this.selectPlan.seller_type_id == 1 && this.$getGlobal('affiliate_purchase_inventory').show)
    },
    billingRequired () {
      return (!this.shippingRequired || (this.selectedKit !== null && this.selectedKit.wholesale_price > 0) || this.selectedPlan.price.price + this.shippingCost > 0)
    }
  },
  methods: {
    getPlans () {
      Subscription.getSubscriptionOnJoin().then(response => { this.plans.push(...response) })
    },
    getRegistrationToken (token) {
      Users.getRegistrationToken(token)
        .then(response => {
          this.user = response
        })
    },
    validateAddresses () {
      let errors = {}
      if (this.shippingRequired) {
        if (this.$isBlank(this.paymentData.addresses.shipping.address_1)) {
          errors['shipping_address.address_1'] = ['Required']
        }
        if (this.$isBlank(this.paymentData.addresses.shipping.zip)) {
          errors['shipping_address.zip'] = ['Required']
        }
        if (this.$isBlank(this.paymentData.addresses.shipping.city)) {
          errors['shipping_address.city'] = ['Required']
        }
        if (this.$isBlank(this.paymentData.addresses.shipping.state)) {
          errors['shipping_address.state'] = ["Required"]
        }
      } else {
        this.paymentData.addresses.shipping = this.paymentData.addresses.billing
      }

      if (this.billingRequired) {
        if (this.$isBlank(this.paymentData.addresses.billing.address_1)) {
          errors['billing_address.address_1'] = ['Required']
        }
        if (this.$isBlank(this.paymentData.addresses.billing.zip)) {
          errors['billing_address.zip'] = ['Required']
        }
        if (this.$isBlank(this.paymentData.addresses.billing.city)) {
          errors['billing_address.city'] = ['Required']
        }
        if (this.$isBlank(this.paymentData.addresses.billing.state)) {
          errors['billing_address.state'] = ['Required']
        }
      } else {
        this.paymentData.addresses.billing = this.paymentData.addresses.shipping
      }

      if (Object.keys(errors).length > 0) {
        this.validationErrors = errors
        return false
      } else {
        this.validationErrors = {}
      }

      if (this.selectedPlan.free_trial_time == 0) {
        // get taxes
        let taxRequest = {
          billing_address: this.paymentData.addresses.billing,
          shipping_address: this.paymentData.addresses.shipping,
          subscription: this.selectedPlan
        }
        Subscription.getTax(taxRequest)
          .then((response) => {
            this.subscriptionTax = response.tax
            this.taxInvoicePid = response.pid
          })
      }
      this.handleStep()
    },
    initSteps () {
      // init steps - only one step should be set to true
      let steps = {
        one: true,
        two: false,
        three: false,
        four: false
      }
      this.Step.init(steps, 300)
    },
    getShippingCost: function () {
      if (this.selectedKit !== null && this.$getGlobal('registration_shipping').value) {
        Shipping.shippingCost({ total_price: this.selectedKit.wholesale_price })
          .then((response) => {
            if (response.error) {
              this.$toast(response.message, { error: true })
              return
            }
            this.shippingCost = parseFloat(response.data.amount)
          })
      } else {
        this.shippingCost = 0.00
      }
    },
    checkPublicId: _.debounce(function (publicId) {
      if (!publicId || publicId === '') {
        this.validPublicIdMessage = ''
        this.validPublicId = false
        this.validPublicIdMessage = 'A store name is required.'
        return
      }
      Users.checkPublicId(publicId)
        .then((response) => {
          if (response.error) {
            this.validPublicId = false
            this.validPublicIdMessage = ''
            if (response.code === 422) {
              this.validPublicIdMessage = response.message.public_id
            }
          } else {
            this.validPublicId = true
            this.validPublicIdMessage = response
          }
        })
    }, 500),
    cleanPhone (phone) {
      if (phone) {
        phone = phone.replace(/-/g, '').replace(/\(|\)/g, '')
        phone = parseInt(phone)
      }
      return phone
    },
    validateUserInfo () {
      this.validationErrors = {}
      if (!this.$getGlobal('replicated_site').show) {
        this.user.public_id = this.user.first_name + this.user.last_name + Math.floor((Math.random() * 100) + 1)
      }
      let request = {
        user: JSON.parse(JSON.stringify(this.user)),
        registration_code: this.registration_code,
        sponsor_id: this.sponsorIdValue
      }
      if (request.user.phone) {
        request.user.phone = this.cleanPhone(request.user.phone)
      }
      Users.validateNewUser(request)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
          } else {
            this.handleStep()
          }
        })
    },
    copyAddress () {
      // copy shipping address to be same as billing
      this.paymentData.addresses.billing = JSON.parse(JSON.stringify(this.paymentData.addresses.shipping))
    },
    selectPlan (plan) {
      this.selectedPlan = plan
      if (this.kits.length < 1) {
        this.selectKit(null)
      }
    },
    getKits () {
      Inventory.getBundles({starter_kit: 1, user_id: 1})
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, { error: true })
          }
          // reposne is an object. Needs to be converted to array to use kits.length
          this.kits = _.values(response.data)
        })
    },
    selectKit (kit) {
      this.selectedKit = kit
      this.handleStep()
      this.getShippingCost()
    },
    submitPayment () {
      this.processing = true
      // in case of further errors these need to be cleared
      this.validPublicIdMessage = ''
      this.validPublicId = false
      this.validationErrors = {}
      if (!this.$refs.paymentForm.validate()) {
        this.processing = false
        return
      }
      this.paymentData.payment = this.$refs.paymentForm.getPayment()
      // request prep
      this.paymentData.user = JSON.parse(JSON.stringify(this.user))
      if (this.paymentData.user.phone) {
        this.paymentData.phone = { number: this.cleanPhone(this.paymentData.user.phone) }
      }
      this.paymentData.total_tax = this.subscriptionTax
      this.paymentData.tax_invoice_pid = this.taxInvoicePid
      this.paymentData.subscription_id = this.selectedPlan.id
      this.paymentData.registration_code = this.registration_code
      this.paymentData.sponsor_id = this.sponsorIdValue
      this.paymentData.ppa = this.ppa
      if (this.selectedKit) {
        this.paymentData.starter_kit_id = this.selectedKit.id
      } else {
        delete this.paymentData.starter_kit_id
      }

      this.paymentData.timezone = moment.tz.guess()
      Payments.subscriptionPayment(this.paymentData)
        .then((response) => {
          if (response.error) {
            this.processing = false
            this.validationErrors = response.message
            this.checkForUserErrors(response.message)
            return
          }
          Auth.setJwtToken(response.jwtToken)
          if (this.$getGlobal('join_redirect').show) {
            window.location.href = this.$getGlobal('join_redirect').value + '?return_url=' + window.location.hostname + '/my-settings'
          } else {
            window.location.href = '/my-settings'
          }
        })
    },
    checkForUserErrors (message) {
      for (var key in message) {
        if (key.includes('user')) {
          this.handleStep('one')
          return
        } else if (key.includes('address')) {
          this.handleStep('three')
          return
        }
      }
    },
    parseDuration (duration) {
      switch(duration) {
        case 1:
          return 'PER MONTH'
        case 3:
          return 'PER QUARTER'
        case 12:
          return 'PER YEAR'
        default:
          return ''
      }
    },
    handleStep (stepName) {
      if (!stepName) {
        Step.next()
        window.scrollTo(0, 0)
        return
      }
      Step.skipTo(stepName)
      window.scrollTo(0, 0)
    }
  },
  components: {
    CpRegistrationPayment: require('../registration/CpRegistrationPayment.vue'),
    CpTooltip: require('../../custom-plugins/CpTooltip.vue'),
    CpInputMask: require('../../cp-components-common/inputs/CpInputMask.vue'),
    CpAddressForm: require('../addresses/CpAddressForm.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
/* always present */
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
.registration-wrapper {
  height: 100%;
  width: 100%;
  text-align: center;
  display: flex;
  flex-direction: column;
  .no-registration {
    display: flex;
    justify-content: center;
    height: 100%;
    div {
      align-self: center;
    }
    padding: 20px;
  }
  .main-section-wrapper {
    flex: 1;
    overflow: auto;
  }
  .disabled-input {
      background: #e0e0e0 !important;
  }
  .agree {
    display: inline;
    width: 30px;
    height: 15px;
  }
  .show-hide-password {
    span {
      padding-right: 0px;
      margin-right: 0px;
      input {
        padding-right: 0px;
        margin-right: 0px;
        display: inline;
        width: 72%;
      }
    }
    button {
      margin-left: 0px;
      background: $cp-lightGrey;
      border: 1px solid $cp-lightGrey;
      color: $cp-main;
      cursor: pointer;
      width: 25%;
      padding: 8px;
      text-align: center;
      &:hover {
        background: darken($cp-lightGrey, 5);
      }
    }
  }
  .store-name {
    overflow: hidden;
    display: flex;
    justify-content: space-between;
    margin: 5px 0px;
    padding: 10px 0px;
  }
  .registration-step {
    width: 0 auto;
  }
  .step-form-wrapper {
    width: fit-content;
    max-width: 950px;
    margin: 50px auto 25px;
    padding: 50px;
    text-align: center;
    input {
      background-color: white;
      border: 1px solid $cp-lightGrey;
      &.error {
          border: 1px solid tomato;
      }
      &.success {
          border: 1px solid $cp-success !important;
      }
    }
    .right-button {
      float: right;
      margin-top: 15px;
    }
    .left-button {
      float: left;
      margin-top: 15px;
    }
  }
  .reg-form {
    width: 450px;
    margin: 10px;
  }
  .payment-buttons {
    display: flex;
    justify-content: space-between;
  }
  .payment-form {
    display: flex;
    flex-wrap: wrap;
    select {
      margin-top: 7px !important;
      height: 38px !important;
    }
  }
  @media (max-width: 800px) {
    .payment-form {
      width: 100%;
      .form-one {
        width: 100%;
        float: none;
      }
      .form-two {
        width: 100%;
        float: none;
      }
    }
  }
  .registration-form {
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
  .sign-in {
    text-align: center;
    margin-top: 20px;
    .login-redirect {
      margin-top: 20px;
    }
  }
  .facebook-button {
    margin-top: 15px;
    margin-bottom: 15px;
    width: 100%;
    background: #46a;
    border: 0;
    padding: 5px;
    &:hover {
      background: lighten(#46a, 10);
    }
  }
  .selection-container {
    width: 95%;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    .select-plan {
      display: inline-block;
      width: 400px;
      min-height: 300px;
      height: auto;
      overflow: hidden;
      margin-top: 25px;
      margin-bottom: 25px;
      h4 {
        justify-content: center;
      }
      .plan-price {
        font-size: 44px;
      }
      &.selectedplan {
        background: $cp-lighterGrey;
        button {
          background: $cp-lightGrey;
          border: 1px solid $cp-lightGrey;
        }
      }
    }
    ul {
        text-align: left;
    }
  }
  .summary-item-container {
    display: grid;
    grid-template-columns: 40% 40%;
    font-size: 20px;
    .left {
      padding: 10px;
      justify-self: end;
    }
    .right {
      padding: 10px;
      justify-self: end;
    }
  }
}
 @media (max-width: 768px) {

   .registration-wrapper {
     .show-hide-password
     span {
       input {
         width: 100%;
       }
     }
     .show-hide-password button {
       width: 100%;
       text-align: center;
     }
     .select-plan {
       width: 75% !important;
       h2 {
         font-size: 20px
       }
     }
     .cp-button-standard {
       padding: 4px 12px;
     }
     .cp-select-standard select {
       width: 49%;
       min-width: 0px;
     }
   }
 }
 @media (max-width: 476px) {
  .registration-wrapper {
    .cp-select-standard select {
      width: 100%;
      min-width: 0px;
    }
   }
  }

</style>
