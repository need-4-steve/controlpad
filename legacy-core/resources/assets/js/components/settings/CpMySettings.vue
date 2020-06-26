<template>
  <div class="my-settings" v-show="!loading">
    <div v-if="Auth.hasAnyRole('Rep', 'Admin', 'Superadmin', 'Customer')">
      <section class="accept-payments" v-if="$getGlobal('reseller_payment_option').show && !settings.payment_account && $getGlobal('replicated_site').show && Auth.hasAnyRole('Rep')">
        <div class="section1">
          <p><strong>Attention!</strong> You are not yet setup to accept payments from your customers.</p>
        </div>
        <cp-splash-registration v-if="!loading && user.id && ppa" :ppa="ppa" @account-processed="val => settings.payment_account = 1" :user-info="user"></cp-splash-registration>
      </section>
      <section class="accept-payments" v-if="!termsAccepted">
          <div class="cp-modal-standard terms-modal">
            <div class="cp-modal-body">
              <div class="text-wrapper" v-if="terms !== null">
                <h1>{{ terms.title }}</h1>
                <div v-html="terms.content"></div>
              </div>
              <div class="cp-modal-controls">
                <button class="cp-button-standard right" @click="acceptTermsAndConditions()">Accept Terms and Conditions</button>
              </div>
            </div>
          </div>
          <div class="cp-modal-controls">
            <button class="cp-button-standard right" @click="acceptTermsAndConditions()">Accept Terms and Conditions</button>
          </div>
      </section>
      <section>
        <div class="cp-left-col">
          <div class="personal-info" v-if="['Superadmin', 'Admin', 'Rep'].includes(user.role.name) || Auth.hasAnyRole('Superadmin', 'Admin', 'Rep')">
            <div class="cp-left-col">
              <div class="dropbox" v-if="useProfilePic()">
                <form action="api/v1/media/create-user-image" method="POST" class="dropzone" id="profileImage">
                </form>
              </div>
            </div>
            <div class="cp-left-col wide">
              <p>{{user.first_name}} {{user.last_name}} (ID#{{ user.id}})</p>
              <p v-if="user.role.name === 'Rep' && $getGlobal('replicated_site').show">
                <a :href="repUrl" target="_blank">{{ repUrl }}</a>
                <div>
                  <strong> Join Date: </strong> {{ this.user.join_date | cpStandardDate(0, 0) }}
                </div>
              </p>
            </div>
            <br/>
            <br/>
            <br/>
            <br/>
            <br/>
            <br/>
            <button v-if="Auth.hasAnyRole('Superadmin', 'Admin') && user.role.name === 'Rep'" class="cp-button-standard" @click="loginAs()"><i class="mdi mdi-login"></i> Sign in as {{ user.first_name }}</button>
          </div>

          <div class="cp-box-standard" v-if="$getGlobal('collect_sponsor_id').show && Auth.hasAnyRole('Rep')">
            <div class="cp-box-heading">
              <h5> My Sponsor Link:</h5>
            </div>
            <div class="cp-box-body">
              <div class="line-wrapper"><span>{{ shareLink }}</span></div>
              <div class="media-links">
                <span>Share with:</span>
                <a target="_blank" :href="`https://www.facebook.com/sharer/sharer.php?u=${ this.shareLink }`"><i class="mdi mdi-facebook"></i></a>
                <a target="_blank" :href="`https://twitter.com/home?status=${ this.shareLink }`"><i class="mdi mdi-twitter"></i></a>
                <a class="copy-link-to-clipboard" :data-clipboard-text="shareLink" @click="copyUrl()"><i class="mdi mdi-clipboard-outline"></i></a>
              </div>
            </div>
          </div>

          <div class="cp-box-standard" v-if="$getGlobal('affiliate_link').show && Auth.hasAnyRole('Rep')">
            <div class="cp-box-heading">
              <h5> My Affiliate Link:</h5>
            </div>
            <div class="cp-box-body">
              <div class="line-wrapper"><span>{{ affiliateLink }}</span></div>
              <div class="media-links">
                <span>Share with:</span>
                <a target="_blank" :href="`https://www.facebook.com/sharer/sharer.php?u=${ this.affiliateLink }`"><i class="mdi mdi-facebook"></i></a>
                <a target="_blank" :href="`https://twitter.com/home?status=${ this.affiliateLink }`"><i class="mdi mdi-twitter"></i></a>
                <a class="copy-link-to-clipboard" :data-clipboard-text="affiliateLink" @click="copyUrl()"><i class="mdi mdi-clipboard-outline"></i></a>
              </div>
            </div>
          </div>

          <cp-basic-info @user-info-updated="refreshToken" :user-id="user_id" :user-info="user" v-if="!loading && user.id"></cp-basic-info>
        </div>
        <div class="cp-right-col">
          <!-- BANKING INFORMATION  -->
          <cp-banking v-if="Auth.hasAnyRole('Rep') && $getGlobal('replicated_site').show" :bank="user.bank" :user-id="user.id"></cp-banking>
          <!-- SUBSCRIPTION INFO -->
          <div class="cp-box-standard" v-if="user.role.id === 5 && user.subscriptions.ends_at != null">
            <div class="cp-box-heading">
              <h5>Subscription Information</h5>
            </div>
            <div class="cp-box-body">
              <div class="line-wrapper">
                <span>Subscription Name</span>
                <p>{{user.subscriptions.subscription.title}}</p>
              </div>
              <div class="line-wrapper">
                <span>Subscription Price</span>
                <p>{{ user.subscriptions.subscription.price.price | currency }}</p>
              </div>
              <div class="line-wrapper">
                <span>Next Payment</span>
                <p>{{ moment(user.subscriptions.ends_at).format('MM/DD/YYYY') }}</p>
              </div>
              <div class="line-wrapper">
                <p><a @click="showReceipt = true">Show Payment Receipts</a></p>
              </div>
              <div v-if="showPayNow">
                <div class="button-wrapper">
                  <button class="cp-button-standard" @click="showConfirm = true" :disabled="disablePayNow">Pay Now</button>
                </div>
                <section class="cp-modal-standard" v-if="showConfirm">
                  <div class="cp-modal-body">
                    <div class="line-wrapper"><span class="bold">Subscription Charges</span>
                      <p>{{subscriptionRenewAmount.subtotal_price | currency}} <span v-if="subscriptionRenewAmount.months > 1">({{ subscriptionRenewAmount.months }} months)</span></p>
                    </div>
                    <div class="line-wrapper"><span class="bold">Subscription Tax</span>
                      <p>{{subscriptionRenewAmount.total_tax | currency}}</p>
                    </div>
                    <div class="line-wrapper"><span class="bold">Total Subscription Charges</span>
                      <p>{{subscriptionRenewAmount.total_tax + parseFloat(subscriptionRenewAmount.subtotal_price) | currency}}</p>
                    </div>
                    <div class="line-wrapper"><span class="bold">Next Payment Due Date</span>
                      <p>{{moment(subscriptionRenewAmount.expires_at).format('MMMM Do YYYY')}}</p>
                    </div>
                    <br/>
                    <div>You will be charged a total of {{subscriptionRenewAmount.total_tax + parseFloat(subscriptionRenewAmount.subtotal_price) | currency}}.</div>
                    <div class="button-wrapper">
                      <button class="cp-button-standard" @click="showConfirm = false">Cancel</button>
                      <button class="cp-button-standard" :disabled="disablePayNow" @click.once="paySubscription()">Confirm</button>
                    </div>
                  </div>
                </section>
              </div>
            </div>
          </div>
          <div v-show="Auth.hasAnyRole('Rep', 'Customer')" class="cp-box-standard">
            <div class="cp-box-heading">
              <h5>Card Information</h5>
            </div>
            <div class="cp-box-body">
              <div class="cp-accordion">
                <div class="cp-accordion-head" @click="paymentInfo = true, subscriptionInfo = !subscriptionInfo">
                  <h5>Subscription Payment Information</h5>
                  <span class="arrow" v-if="closed"><i class="mdi mdi-chevron-down"></i></span>
                  <span class="arrow" v-if="!closed"><i class="mdi mdi-chevron-up"></i></span>
                </div>
              </div>
              <div class="cp-accordion">
                <div class="cp-accordion-body" :class="{closed: subscriptionInfo === true}">
                  <div class="cp-accordion-body-wrapper">
                    <div class="line-wrapper action-wrapper">
                      <h5>Credit Card Information</h5>
                      <button class="action-btn" v-show="user.card_token" @click="creditCardModal = true, creditCardChange = true, creditCardRequest.type ='subscription', scrollTop(), checkBillingForCardUpdate()">Update</button>
                      <button class="action-btn" v-show="!user.card_token" @click="creditCardModal = true, addCreditCard = true,creditCardRequest.type ='subscription', scrollTop(), checkBillingForCardUpdate()">Add Credit Card</button>
                    </div>
                    <div class="line-wrapper">
                      <span>Card Type</span>
                      <p>{{user.card_token.card_type}}</p>
                    </div>
                    <div class="line-wrapper">
                      <span>Card Number</span>
                      <p>{{user.card_token.card_digits}}</p>
                    </div>
                    <div class="line-wrapper">
                      <span>Expiration Date</span>
                      <p>{{user.card_token.expiration}}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <cp-settings-user v-if="user && user.id !== undefined" :old-settings="settings" :user="user"></cp-settings-user>
          <cp-company-info v-if="Auth.hasAnyRole('Rep')"></cp-company-info>
          <cp-rep-logo v-if="Auth.hasAnyRole('Rep') && user.seller_type_id === 2 && $getGlobal('reseller_logo').show"></cp-rep-logo>
        </div>
        <cp-dialog class="credit-card-modal" :open="creditCardModal" @close="clearModal">
          <div class="cp-box-heading" slot="header">
              <h2 v-show="creditCardChange" >Change Credit Card</h2>
              <h2 v-show="addCreditCard">Add Credit Card</h2>
          </div>
          <template slot="content">
            <div class="body-wrapper cp-form-standard">
                  <cp-input :error="validationErrors['payment.name']" label="Name on card" v-model="creditCardRequest.payment.name"></cp-input>
                  <cp-input :error="validationErrors['payment.card_number']" label="Card Number" v-model="creditCardRequest.payment.card_number"></cp-input>
                  <div class="three-field-wrapper">
                        <cp-select
                        label="Expiration Date"
                        v-model="creditCardRequest.payment.month"
                        :error="validationErrors['payment.month']"
                        :options="[
                        { name: '01', value: '01' },
                        { name: '02', value: '02' },
                        { name: '03', value: '03' },
                        { name: '04', value: '04' },
                        { name: '05', value: '05' },
                        { name: '06', value: '06' },
                        { name: '07', value: '07' },
                        { name: '08', value: '08' },
                        { name: '09', value: '09' },
                        { name: '10', value: '10' },
                        { name: '11', value: '11' },
                        { name: '12', value: '12' }]">
                        </cp-select>
                        <cp-select
                        label="Year"
                        :options="years"
                        :key-value="{ name:'name', value: 'name'}"
                        v-model="creditCardRequest.payment.year"
                        :error="validationErrors['payment.year']">
                        </cp-select>
                        <cp-input label="CVV" :error="validationErrors['payment.code']" type="number" name="" v-model="creditCardRequest.payment.code"></cp-input>
                  </div>
                  <div class="billing-select">
                    <h6>Choose your billing address</h6>
                    <div class="cp-select-standard">
                      <select v-model="selectedAddress" @change="addressAssignment()">
                        <option v-if="user.billing_address.address_1 != '' && user.billing_address.city != '' && user.billing_address.state != '' && user.billing_address.zip != ''" value="billing">
                          {{user.billing_address.address_1}}, {{user.billing_address.city}}, {{ user.billing_address.state}}, {{user.billing_address.zip}}
                        </option>
                        <option value="newAddress"><span><i class="mdi mdi-plus"></i></span>Add a new Address</option>
                        <option v-if="showNewAddress" :value="creditCardRequest.addresses.billing" selected>{{creditCardRequest.addresses.billing.address_1}}, {{creditCardRequest.addresses.billing.city}}, {{creditCardRequest.addresses.billing.state}}, {{creditCardRequest.addresses.billing.zip}}</option>
                      </select>
                    </div>
                    <span v-show="validationErrors['addresses.billing.address_1']" class="cp-warning-message">{{ validationErrors['addresses.billing.address_1'] }}</span>
                    <span v-show="validationErrors['addresses.billing.address_2']" class="cp-warning-message">{{ validationErrors['addresses.billing.address_2'] }}</span>
                    <span v-show="validationErrors['addresses.billing.city']" class="cp-warning-message">{{ validationErrors['addresses.billing.city'] }}</span>
                    <span v-show="validationErrors['addresses.billing.state']" class="cp-warning-message">{{ validationErrors['addresses.billing.state'] }}</span>
                    <span v-show="validationErrors['addresses.billing.zip']" class="cp-warning-message">{{ validationErrors['addresses.billing.zip'] }}</span>
                  </div>
                    <div class="button-wrapper">
                      <button class="cp-button-standard" @click="updateCard()">Update Account</button>
                    </div>
            </div>
          </template>
        </cp-dialog>
        <cp-dialog :open="addBillingAddress" @close="addBillingAddress = false">
          <h2 slot="header"> Add an address</h2>
          <section class="credit-card-modal" slot="content">

                <div class="body-wrapper cp-form-standard">
                  <cp-input :error="validationErrors['selected.billing_address.address_1']" label="Address 1" type="" name="" placeholder="Street address, P.O. Box" v-model="selected.billing_address.address_1"></cp-input>
                  <cp-input :error="validationErrors['selected.billing_address.address_2']" label="Address 2" type="" name="" placeholder="Apartment, suite, building, floor" v-model="selected.billing_address.address_2"></cp-input>
                  <cp-input :error="validationErrors['selected.billing_address.city']" label="City" type="" name="" placeholder="City" v-model="selected.billing_address.city"></cp-input>
                  <cp-input :error="validationErrors['selected.billing_address.zip']" label="Zip Code" type="" name="" placeholder="Zip code" v-model="selected.billing_address.zip"></cp-input>
                  <cp-select
                  label="State"
                  :error="validationErrors['billing_address.state']"
                  v-model="selected.billing_address.state"
                  :options="states"
                  :key-value="{ name: 'name', value: 'value'}"></cp-select>
                  <div class="button-wrapper">
                    <button class="cp-button-standard" @click="assignBillingAddress()">Add Address</button>
                  </div>
              </div>
          </section>
        </cp-dialog>
        <!--  MODAL RECEIPTS -->
          <transition>
            <section class="cp-modal-standard" v-if="showReceipt" @click="showReceipt = false">
              <div class="cp-modal-body">
                <cp-show-receipt :user="settings"></cp-show-receipt>
              </div>
            </section>
          </transition>
        </section>
    </div>
    <div class="align-center">
      <img class="loading" :src="$getGlobal('loading_icon').value" v-show="loading">
    </div>
  </div>
</template>
<script type="text/javascript">
const Dropzone = require('dropzone')
const Users = require('../../resources/users.js')
const {states} = require('../../resources/states.js')
const Banking = require('../../resources/banking.js')
const Clipboard = require('clipboard')
const Media = require('../../resources/media.js')
const Payments = require('../../resources/payments.js')
const Auth = require('auth')
const moment = require('moment')

module.exports = {
  name: 'CpMySettings',
  routing: [
    {
      name: 'site.CpMySettings',
      path: '/my-settings',
      meta: {
        title: 'My Account',
        nosubscription: true
      },
      props: true
    },
    {
      name: 'site.UserSettings',
      path: '/my-settings/:id',
      meta: {
        title: 'User Settings'
      },
      props: true
    }
  ],
  data: function () {
    return {
      user_id: null,
      repUrl: '',
      loading: false,
      Auth: Auth,
      moment: moment,
      showPaymentAccountModal: false,
      showTermsAndConditions: false,
      showConfirm: false,
      showReceipt: false,
      showPayNow: false,
      disablePayNow: false,
      closed: true,
      selectedAddress: {},
      selected: {
        billing_address: {}
      },
      importRequest: {
        id: ''
      },
      url: '',
      avatar: {
        url: ''
      },
      paymentInfo: true,
      subscriptionInfo: true,
      storeSettings: true,
      shippingSettings: true,
      locatorSettings: true,
      expired: false,
      showNewAddress: false,
      addBillingAddress: false,
      creditCardChange: false,
      addCreditCard: false,
      bankUpdate: false,
      creditCardModal: false,
      states: states,
      years: [],
      isReadOnly: true,
      profileIcon: true,
      termsAccepted: true,
      terms: {},
      subscription: {},
      subscriptionRenewAmount: {
        total_tax: 0,
        subtotal_price: 0
      },
      ppa: {
        type: '0',
        account: {},
        address: {},
        owner: {
          address: {}
        },
        businessAccount: {},
        businessAddress: {}
      },
      ppaLoading: false,
      ppaMessage: '',
      ppaProcessed: false,
      user: {
        first_name: '',
        last_name: '',
        email: '',
        public_id: '',
        phone: {
          number: ''
        },
        bank: {
          bankName: '',
          name: '',
          number: '',
          routing: '',
          type: '',
          validated: false
        },
        role: {
          name: ''
        },
        subscriptions: {
          card_digits: '',
          subscription: {
            price: {}
          }
        },
        billing_address: {
          address_1: '',
          address_2: '',
          city: '',
          state: '',
          zip: '',
          label: 'business'
        },
        business_address: {},
        shipping_address: {
          address_1: '',
          address_2: '',
          city: '',
          state: '',
          zip: '',
          label: 'Shipping'
        },
        profileImage: {
          url: ''
        },
        card_token: {
          card_digits: '',
          card_type: ''
        }
      },
      creditCardRequest: {
        payment: {
          name: '',
          number: '',
          month: '',
          year: '',
          code: '',
          zip: ''
        },
        addresses: {
          billing: {
            address_1: '',
            address_2: '',
            city: '',
            state: '',
            zip: '',
            label: ''
          }
        },
        type: ''
      },
      cardInfo: {
        card_type: '',
        number: ''
      },
      settings: {
        user_id: '',
        show_new_inventory: false,
        hide_products: false,
        show_address: false,
        show_email: false,
        show_phone: false,
        show_location: false,
        show_address_on_invoice: false,
        will_deliver: false,
        new_customer_message: '',
        order_confirmation_message: '',
        timezone: '',
        payment_account: false
      },
      uploadZone: {},
      errorMessages: {
        phone: {}
      },
      oauths: {
        facebook: {
          email: ''
        }
      },
      validationErrors: {
        payment: {
          name: false,
          card_number: false,
          month: false,
          year: false,
          code: false
        },
        addresses: {
          billing: {
            address_1: false,
            address_2: false,
            city: false,
            state: false,
            zip: false
          }
        }
      }
    }
  },
  props: {},
  mounted () {
    this.uploadDropzone()
    this.getUser()
    this.getYears()
    this.getUserSettings()
    this.getTerms()
    Dropzone.autoDiscover = false
  },
  computed: {
    shareLink () {
      return window.location.protocol + '//' + window.location.host + '/sign-up-with/' + this.user.public_id
    },
    affiliateLink () {
      return this.$getGlobal('affiliate_link').value.url + '?public_id=' + this.user.public_id
    }
  },
  methods: {
    clearModal () {
      this.validationErrors = {}
      this.creditCardModal = false
      this.creditCardChange = false
      this.addCreditCard = false
      this.selectedAddress = ''
      this.creditCardRequest.payment = {}
    },
    getUserId () {
      if (this.$pathParameter()) {
        this.user_id = this.$pathParameter()
      } else {
        this.user_id = Auth.getAuthId()
      }
    },
    refreshToken () {
      this.repUrl = this.$getGlobal('rep_url').value.replace('%s', this.user.public_id)
      Auth.refreshToken().then(res => Auth.setJwtToken(res.cp_token))
    },
    getTerms () {
      let request = {}
      if (Auth.hasAnyRole('rep') && !Auth.hasAcceptedTerms()) {
        Users.getTerms(request, this.user_id).then((response) => {
          this.terms = response.terms
          this.termsAccepted = response.termsAccepted
        })
      }
    },
    copyUrl () {
      var thisClipboard = this
      var clipboard = new Clipboard('.copy-link-to-clipboard')
      clipboard.on('success', (e) => {
        thisClipboard.$toast('Successfuly copied to clipboard', {
          success: true,
          dismiss: false
        })
      })
      clipboard.on('error', (e) => {
        thisClipboard.$toast('Could not copy to clipboard', {
          error: true,
          dismiss: true
        })
      })
    },
    loginAs () {
      Auth.loginAs(this.user.id).then(res => {
        if (res.error) {
          this.errorMessages = res.message
          return this.$toast(res.message, { error: true, dismiss: false })
        }
        this.$events.$emit('login-as-change')
        this.$router.push('/dashboard')
      })
    },
    getBanking () {
      this.ppaLoading = true
      Banking.bankInfo(this.user_id, {
        number: true
      }).then((response) => {
        if (response.error) {
          return
        }
        this.ppa.businessAccount.number = response.account
        this.ppa.businessAccount.type = response.type
        this.ppa.businessAccount.routing = response.routing
        this.ppaLoading = false
      })
    },
    uploadDropzone () {
      if (!this.useProfilePic()) {
        return false
      }
      var uploadZoneConfig = Media.dropzoneConfig()
      var vm = this
      uploadZoneConfig.success = function (file, response) {
        vm.media = response
        vm.uploading = false
        vm.modalDisplay = false
        vm.$toast('Media source successfully uploaded.')
      }
      uploadZoneConfig.dictRemoveFile = 'Replace Image'
      uploadZoneConfig.maxFiles = 1
      vm.uploadZone = new Dropzone('#profileImage', uploadZoneConfig)
    },
    assignBillingAddress () {
      this.creditCardRequest.addresses.billing = this.selected.billing_address
      this.selectedAddress = this.creditCardRequest.addresses.billing
      this.showNewAddress = true
      this.addBillingAddress = false
      this.selected.billing_address = {}
    },
    acceptTermsAndConditions () {
      Users.acceptTermsAndConditions()
        .then((response) => {
          this.termsAccepted = true
          Auth.refreshToken()
        })
    },
    addressAssignment: function () {
      if (this.selectedAddress === 'billing') {
        this.addBillingAddress = false
        this.creditCardRequest.addresses.billing.address_1 = this.user.billing_address.address_1
        this.creditCardRequest.addresses.billing.address_2 = this.user.billing_address.address_2
        this.creditCardRequest.addresses.billing.city = this.user.billing_address.city
        this.creditCardRequest.addresses.billing.state = this.user.billing_address.state
        this.creditCardRequest.addresses.billing.zip = this.user.billing_address.zip
        this.creditCardRequest.addresses.billing.label = this.user.billing_address.label
      } else if (this.selectedAddress === 'newAddress') {
        this.addBillingAddress = true
      }
    },
    getUser () {
      this.getUserId()
      Users.userAccount(this.user_id)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, {
              error: true
            })
          } else {
            if (!response.business_address) {
              response.business_address = {}
            }
            if (!response.billing_address) {
              response.billing_address = {}
            }
            if (!response.shipping_address) {
              response.shipping_address = {}
            }
            this.user = response
            this.repUrl = this.$getGlobal('rep_url').value.replace('%s', this.user.public_id)
            if (response.profile_image && response.profile_image[0] && this.useProfilePic()) {
              this.avatar = response.profile_image[0]
              Dropzone.forElement('#profileImage').emit('addedfile', this.avatar)
              Dropzone.forElement('#profileImage').emit('thumbnail', this.avatar, this.avatar.url_sm)
              Dropzone.forElement('#profileImage').emit('complete', this.avatar)
            }
            if (response.card_token != null) {
              this.user.card_token.card_digits = response.card_token.card_digits
              this.user.card_token.card_type = response.card_token.card_type
              this.user.card_token.expiration = response.card_token.expiration.substring(0, 2) + ' / ' + response.card_token.expiration.substring(2, 4)
            } else {
              this.user.card_token = 0
            }
            if (response.subscription && response.subscriptions.card_expired > 0) {
              this.expired = true
            } else {
              this.expired = false
            }
          }
          if (Auth.hasAnyRole('Rep')) {
            if (moment(this.user.subscriptions.ends_at).isBefore(moment())) {
              this.subscriptionExpired()
            }
            this.loading = false
          }
        })
    },
    scrollTop: function () {
      window.scrollTo(0, 0)
    },
    checkBillingForCardUpdate: function () {
      this.creditCardRequest.address_1 = this.user.billing_address.address_1
      this.creditCardRequest.address_2 = this.user.billing_address.address_2
      this.creditCardRequest.city = this.user.billing_address.city
      this.creditCardRequest.state = this.user.billing_address.state
      this.creditCardRequest.zip = this.user.billing_address.zip
      this.creditCardRequest.label = this.user.billing_address.label
      this.creditCardRequest.name = this.user.billing_address.name
      Banking.checkBillingForCardUpdate(this.creditCardRequest)
        .then((response) => {
          if (response.code === 422) {
            this.$toast('Please verify billing address, must have valid billing address to add a card for subscriptions', {
              error: true,
              dismiss: false
            })
            this.creditCardModal = false
          }
        })
    },
    preventDefault: function (event) {
      event.stopPropagation()
    },
    getYears () {
      var currentYear = new Date().getFullYear()
      for (var i = 0; i <= 10; i++) {
        this.years.push({name: currentYear + i})
      }
    },
    updateCard: function () {
      this.validationErrors = {}
      Banking.cardUpdate(this.creditCardRequest)
        .then((response) => {
          if (response.error) {
            if (response.message.payman_message) {
              this.$toast(response.message.payman_message, {
                'error': true
              })
            } else {
              this.validationErrors = response.message
            }
          } else {
            this.$toast('Successfuly updated subscription card on file', {
              error: false,
              dismiss: false
            })
            this.creditCardModal = false
            this.creditCardChange = false
            this.addCreditCard = false
            this.user.card_token.expiration = this.creditCardRequest.payment.month + ' / ' + this.creditCardRequest.payment.year.substring(2, 4)
          }
        })
    },
    getUserSettings: function () {
      Users.userSettings(this.user_id)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {
              error: true
            })
          } else {
            this.settings.user_id = response.user_id
            this.settings.hide_products = response.hide_products
            this.settings.show_location = response.show_location
            this.settings.show_new_inventory = response.show_new_inventory
            this.settings.show_address = response.show_address
            this.settings.show_email = response.show_email
            this.settings.show_phone = response.show_phone
            this.settings.show_address_on_invoice = response.show_address_on_invoice
            this.settings.will_deliver = response.will_deliver
            this.settings.timezone = response.timezone
            this.settings.new_customer_message = response.new_customer_message
            this.settings.order_confirmation_message = response.order_confirmation_message
            this.settings.payment_account = response.payment_account
          }
        })
    },
    updateSettings: function () {
      Users.updateUserSettings(this.settings)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {
              error: true
            })
          } else {
            this.settings = response
          }
        })
    },
    useProfilePic: function () {
      return this.Auth.hasAnyRole('Superadmin', 'Admin') || (this.Auth.hasAnyRole('Rep') && this.$getGlobal('replicated_site').show)
    },
    paySubscription: function () {
      this.disablePayNow = true
      Payments.subscriptionPayNow()
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message.message
            this.disablePayNow = false
            return this.$toast(response.message.message, {
              error: true,
              dismiss: false
            })
          }
          this.showPayNow = false
          this.user.subscriptions.ends_at = response.data.ends_at
          Auth.refreshToken()
          return this.$toast(response.message, {
            error: false,
            dismiss: false
          })
        })
    },
    subscriptionExpired: function () {
      if (moment(this.user.subscriptions.ends_at).isBefore(moment())) {
        this.showPayNow = true
        Payments.subscriptionAmount(this.user_id)
          .then((response) => {
            this.subscriptionRenewAmount = response.data
            this.subscriptionRenewAmount.expires_at = moment(this.subscriptionRenewAmount.expires_at).format('MM/DD/YYYY')
            this.subscriptionRenewAmount.price = this.$options.filters.currency(response.data.price)
          })
      } else {
        this.showPayNow = false
      }
    }
  },
  components: {
    'CpBanking': require('../payment/CpBanking.vue'),
    'CpSplashRegistration': require('../registration/CpSplashRegistration.vue'),
    'CpBasicInfo': require('../settings/partials/CpBasicInfo.vue'),
    'CpSettingsUser': require('../settings/partials/CpSettingsUser.vue'),
    'CpOauthConnections': require('../settings/partials/CpOauthConnections.vue'),
    'CpCompanyInfo': require('../settings/partials/CpCompanyInfo.vue'),
    'CpConfirm': require('../../cp-components-common/CpConfirm.vue'),
    'CpRepLogo': require('../settings/partials/CpRepLogo.vue'),
    'CpShowReceipt': require('../subscription/CpShowReceipt.vue')
  }
}
</script>
<style lang="scss">
@import "resources/assets/sass/var.scss";
    .my-settings {
      .cp-form-standard {
        .three-field-wrapper{
          display: flex;
          justify-content: space-between;
          span:nth-child(3) {
            width: 33%;
            margin-left: 5px;
          }
          span:nth-child(2){
            width: 33%;
            margin: 0px 5px;
          }
          span:nth-child(1){
            width: 30%;
            margin-right: 5px;
          }
        }
      }
      .media-links {
        display: flex;
        justify-content: space-between;
        padding: 10px 15px;
        span {
            align-self: center;
        }
        .bold {
          font-weight: bold;
        }
      }
        .button-wrapper {
          margin: 5px;
          display: flex;
          justify-content: flex-end;
    }
    h5 {
        font-weight: 300;
        font-size: 16px;
    }
    h6 {
        &.verification {
            position: absolute;
            right: 10px;
            top: 13px;
            margin: 0;
            font-weight: 400;
            text-decoration: underline;
        }
    }
    .bold {
        font-weight: bold;
    }
    .confirm {
        float: right;
    }
    .terms-modal {
        .cp-modal-body {
            .text-wrapper {
                max-height: 75vh;
                overflow-x: scroll;
            }
            .cp-modal-controls {
                padding-top: 10px;
            }
        }
    }
    .accept-payments {
        background-color: $cp-lighterGrey !important;
        width: 100%;
        margin: 5px 10px 25px 5px;
        padding: 15px;
        overflow: hidden;
        .cp-button-standard {
            margin: 0;
        }
        .section1 {
            p {
                margin-top: 5px;
                margin-bottom: 0;
            }
            float: left;
        }
        .section2 {
            float: right;
        }
    }
    .cp-box-standard {
        margin: 0 5px 20px;
        &.inside {
            margin: 0;
        }
    }
    .cp-box-heading {
        position: relative;
        &.sub-heading {
            background: lighten($cp-main, 20%);
        }
        button {
            &.action-btn {
                width: auto;
                background: inherit;
                padding: 0 10px;
                color: #fff;
            }
        }
    }
    .cp-box-body {
        padding: 0;
        &.quick-links {
            padding: 0 0 100px;
        }
        h5 {
            font-size: 15px;
        }
    }
    .new-bank,
    .new-card {
        line-height: 34px;
        margin-right: 48px;
        color: $cp-LightBlue;
        text-decoration: underline;
        cursor: pointer;
    }
    .new-card {
        margin-right: 19px;
    }
    .line-wrapper {
        display: flex;
        -webkit-display: flex;
        justify-content: space-between;
        -webkit-justify-content: space-between;
        padding: 5px 10px;
        border-bottom: solid 1px $cp-lighterGrey;
        min-height: 40px;
        span {
            padding: 5px;
        }
        &.sub {
            display: block;
        }
        &.credit {
            justify-content: center;
            -webkit-justify-content: center;
            img {
                width: 75%;
            }
        }
        &.cvv {
            justify-content: flex-start;
            -webkit-justify-content: flex-start;
        }
        &.action-wrapper {
            justify-content: flex-start;
            -webkit-justify-content: flex-start;
            button {
                margin-left: 20px;
            }
        }
        .toggle {
            display: inline-block;
            width: 55px;
            input {
                padding: 0;
            }
        }
        .sub-line-wrapper {
            display: flex;
            -webkit-display: flex;
            justify-content: space-between;
            -webkit-justify-content: space-between;
        }
        p {
            margin: 0;
            width: 35%;
            text-align: left;
            &.inactive {
                color: rgba(0,0,0,.4);
            }
        }
        .input {
            background: #fff;
            width: 100%;
            margin: 5px 2px;
            &.edit {
                border: solid 1px $cp-lighterGrey;
                background: $cp-lighterGrey;
            }
        }
        .input-wrapper {
            width: 100%;
            max-width: 70%;
        }
    }
    .city-state-wrapper {
        display: flex;
        -webkit-display: flex;
        justify-content: space-between;
        -webkit-justify-content: space-between;
        input,
        select {
            width: 49%;
        }
    }
    .select-wrapper {
        width: 49%;
        position: relative;
        select {
            width: 100%;
            height: 30px;
            margin-top: 5px;
            background: transparent;
            border: none;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            &.edit {
                background: $cp-lighterGrey;
            }
        }
    }
    .action-btn {
        background: transparent;
        border: none;
        color: $cp-LightBlue;
        padding: 0;
    }
    .action-btns {
        display: flex;
        -webkit-display: flex;
        justify-content: center;
        -webkit-justify-content: space-center;
        width: 100%;
        .action-btn {
            margin: 0 20px;
        }
    }
    .bank-modal,
    .billing-modal,
    .credit-card-modal,
    .verify-modal {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        z-index: 100;
        background: rgba(255,255,255,.5);
        .cp-box-standard {
            width: 95%;
            max-width: 600px;
            margin: 200px auto;
            background: #fff;
            z-index: 5000;
        }
        .line-wrapper {
            border-bottom: none;
            span {
                display: inline-block;
                padding: 10px 0;
            }
        }
        input,
        select {
            border: solid 1px #ccc;
            text-indent: 10px;
        }
        .body-wrapper {
            padding: 20px;
            .input-wrapper,
            .select-wrapper {
                width: 70%;
                &.expiration {
                    width: 29%;
                    .icon {
                        position: absolute;
                        right: 10px;
                        top: 2px;
                    }
                }
                &.cvv {
                    width: 20%;
                    margin-left: 135px;
                }
            }
        }
    }
    .billing-modal {
        background: #fff;
        .close {
            position: fixed;
            right: 15px;
            top: 15px;
            font-size: 30px;
            color: $cp-main;
            opacity: 1;
        }
        .cp-box-body {
            border: none;
        }
        .heading {
            padding: 5px 10px;
        }
        .line-wrapper {
            display: block;
            input,
            select {
                height: 40px;
            }
            &.zip {
                max-width: 50%;
            }
            &.flex {
                display: flex;
                -webkit-display: flex;
            }
        }
        button {
            border: none;
            height: 40px;
            padding: 10px 15px;
            color: #fff;
            background: $cp-main;
            margin: 30px 0;
        }
    }
    .verify-modal {
        .cp-box-body {
            padding: 20px;
        }
        .line-wrapper {
            max-width: 300px;
            margin: 0 auto;
            input {
                width: 90%;
            }
            .input-wrapper {
                max-width: 60%;
            }
        }
    }
    .close-modal {
        position: absolute;
        right: 10px;
        top: 6px;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
    }
    .cp-button-standard {
        &:hover {
            background: $cp-main;
        }
    }
    .personal-info {
        margin: 0 5px 20px;
        &:after {
            display: table;
            content: "";
            clear: both;
        }
        .cp-left-col {
            width: 30%;
            &.wide {
                width: 55%;
            }
        }
        .dropbox {
            position: relative;
            height: 102px;
            width: 102px;
            border: dotted 1px #000;
            .dropzone {
                max-height: 100px;
                border: none;
                padding: 0;
                min-height: 100px;
                background: url("https://s3-us-west-2.amazonaws.com/controlpad/no-avatar.png") no-repeat;
                background-size: cover;
                .dz-preview {
                    width: 100%;
                    margin: 0 !important;
                    max-height: 100px;
                    &:hover {
                        .dz-image {
                            img {
                                margin: 0 auto;
                                filter: none;
                                -webkit-filter: none;
                                transform: none;
                            }
                        }
                    }
                }
                .dz-image {
                    width: 100%;
                    height: 100%;
                    max-height: 100px;
                    img {
                        max-width: 100%;
                    }
                }
                .dz-details,
                .dz-message {
                    display: none;
                }
                .dz-error-mark {
                    top: 35%;
                    left: 35%;
                    margin-left: 0;
                    margin-top: 0;
                    svg {
                        width: 25px;
                        height: 25px;
                    }
                }
                .dz-error-message {
                    max-width: 600px;
                }
            }
            .dropzone,
            .dropzone-wrapper {
                position: absolute;
                width: 100px;
                height: 100px;
            }
        }
        p {
            margin: 0 0 5px;
        }
    }
}
@media (max-width: 1024px) {
    .my-settings {
        .personal-info {
            .cp-left-col {
                float: left;
                width: 15%;
                &.wide {
                    width: 60%;
                    float: right;
                }
            }
        }
        .cp-left-col,
        .cp-right-col {
            float: none;
            width: 100%;
        }
    }
}
@media (max-width: 676px) {
    .my-settings {
        .line-wrapper {
            display: block;
            h5 {
                display: inline-block;
            }
        }
        .bank-modal {
            .line-wrapper {
                span {
                    padding: 5px 0;
                }
            }
        }
        .bank-modal,
        .credit-card-modal {
            .cp-box-standard {
                height: 100%;
                width: 100%;
                max-width: 100%;
                margin: 0;
            }
            .cp-box-body {
                height: 100%;
            }
        }
        .credit-card-modal {
            .body-wrapper {
                .select-wrapper {
                    &.expiration {
                        display: inline-block;
                        width: 45%;
                        .icon {
                            top: 8px;
                        }
                    }
                }
                .input-wrapper {
                    &.cvv {
                        margin-left: 0;
                    }
                }
            }
            .line-wrapper {
                span {
                    padding: 5px 0;
                    &.expiration {
                        display: block;
                    }
                }
            }
        }
    }
}
@media (max-width: 350px) {
    .my-settings {
        .credit-card-modal {
            .body-wrapper {
                .select-wrapper {
                    &.expiration {
                        .icon {
                            display: none;
                        }
                    }
                }
            }
        }
    }
}
</style>
