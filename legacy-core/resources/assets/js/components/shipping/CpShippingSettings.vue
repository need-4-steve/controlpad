<template lang="html">
    <div class="shipping-rate-wrapper">
        <section class="shipping-info">
            <div class="left-box">
                <h3>Shipping Origin</h3>
                <p>
                    The default address used to calculate shipping rates.
                </p>
                <h3>Create Shipping Rates</h3>
                <p>
                    Here you can add new shipping rates to define your fulfillment costs. Create shipping rates based on price.
                </p>
            </div>
            <div class="right-box cp-form-standard">
                <cp-address-box
                    :address-label="'Business'"
                    :addressable-type="'App\\Models\\User'"
                    :heading-title="'Default Shipping Origin Address'">
                </cp-address-box>
            </div>
        </section>
        <section class="fulfillment-wrapper">
            <div class="cp-box-standard left-box">
                <div class="cp-box-heading">
                    <h5>Shipping Fulfillment Time</h5>
                    <button type="button" name="button" class="cp-box-heading-button save" @click="saveFulfillmentTime()"><i class="mdi mdi-floppy"></i></button>
                </div>
                <div class="cp-box-body">
                    <div class="box-one">
                        <label>Timeframe to fulfill orders: </label>
                        <input class="fulfillment" type="text" maxlength="50" v-model="shippingFulfillment">
                        <div>
                            <small>{{charactersLeft}}</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cp-box-standard right-box" v-if="$getGlobal('self_pickup_reseller').show && Auth.hasAnyRole('Rep') && Auth.getClaims().sellerType == 'Reseller'">
                <div class="cp-box-heading">
                    <h5>Self Pickup</h5>
                    <button type="button" name="button" class="cp-box-heading-button save" @click="saveSelfPickup()"><i class="mdi mdi-floppy"></i></button>
                </div>
                <div class="cp-box-body">
                    <div class="line-wrapper"> 
                        <label>Allow Customers to Self Pickup:</label>
                        <input class="toggle-switch" type="checkbox" v-model="selfPickup">
                    </div>
                </div>
            </div>
        </section>
        <div class="cp-box-standard">
            <div class="cp-box-heading">
                <h5>Shipping Rate Based on Price</h5>
                <button type="button" name="button" class="cp-box-heading-button save" @click="addRate()"><i class="mdi mdi-floppy"></i></button>
            </div>
            <div class="cp-box-body">
                <div class="cp-form-standard shipping-rate-form">
                    <div class="rate-name">
                        <div class="box-one">
                            <label>Name of Rate: </label>
                            <input :class="{ error: errorMessages.name }" type="text" v-model="newRate.name" placeholder="Standard Shipping">
                            <span v-show="errorMessages.name" class="cp-warning-message">{{ errorMessages.name }}</span>
                        </div>
                        <div class="box-one">
                            <br />
                            <input type="checkbox" @click="setFreeRate()"><label>Indicate as a free shipping rate</label>
                        </div>
                    </div>
                    <div class="rate-point">
                        <div class="box-one">
                            <label>Price Point:</label>
                            <input :class="{ error: errorMessages.max }" class="max-rate" type="number" v-model="newRate.max" placeholder="$50.00">
                            <span v-show="errorMessages.max" class="cp-warning-message">{{ errorMessages.max }}</span>
                        </div>
                        <div class="box-two">
                            <label>Shipping Rate: </label>
                            <input :class="{ error: errorMessages.amount }" class="ship-rate" type="number" v-model="newRate.amount" placeholder="$5.00">
                            <span v-show="errorMessages.amount" class="cp-warning-message">{{ errorMessages.amount }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <cp-tabs v-if="Auth.hasAnyRole('Superadmin', 'Admin')"
           :items="[
             { name: 'Retail', active: true },
             { name: 'Wholesale', active: false },
           ]"
           :callback="selectType"></cp-tabs>
            <table class="cp-table-standard shipping-table-price-rate">
                <thead>
                    <th>Name</th>
                    <th>Price Range</th>
                    <th>Rate Amount</th>
                    <th><!-- DELETE --></th>
                </thead>
                <tbody>
                    <tr v-for="(rate, index) in rates">
                        <td>{{ rate.name }}</td>
                        <td v-if="rate.max">{{ rate.min | currency }}<i> <span>-</span></i> {{ rate.max | currency }}</td>
                        <td v-else>{{ rate.min | currency }} <span><i class="mdi mdi-plus max-rate"></i></span></td>
                        <td>{{ rate.amount | currency  }}</td>
                        <td><i v-if="rates.length > 1"class="mdi mdi-close pointer" @click="deleteRate(index)"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
const Shipping = require('../../resources/shipping.js')
const Settings = require('../../resources/settings.js')
const Auth = require('auth')
const Users = require('../../resources/UserApiv0.js')

module.exports = {
  data: function () {
    return {
      id: Auth.getOwnerId(),
      Auth: Auth,
      rates: [],
      wholesaleRates: [],
      retailRates: [],
      disableDelete: false,
      shippingFulfillment: '',
      selfPickup: false,
      rateRequest: {
        ranges: []
      },
      newRate: {},
      activeShipping: 'retail',
      freeRate: false,
      errorMessages: {
        max: '',
        amount: '',
        name: ''
      }
    }
  },
  mounted: function () {
    this.getRates()
    this.getStoreSettings()
    this.getSelfPickup()
  },
  methods: {
    selectType (name) {
      switch (name) {
        case 'Retail':
          this.activeShipping = 'retail'
          this.getRates()
          break
        case 'Wholesale':
          this.getWholesaleRates()
          this.activeShipping = 'wholesale'
          break
        default:
          this.activeShipping = 'retail'
          this.getRates()
      }
    },
    getRates: function () {
      Shipping.rateRanges()
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message
            return
          }
          if (response.length <= 0) {
            this.$toast('You do not have any shipping rates set. Please define them so customers can checkout on your store.', { error: true })
          }
          this.rates = response
          this.disableDelete = false // set to prevent deletion being called before getRates returns
        })
    },
    getWholesaleRates: function () {
      Shipping.wholesaleRateRanges()
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message
            return
          }
          if (Auth.hasAnyRole('Superadmin', 'Admin')) {
            if (response.length <= 0) {
              this.$toast('You do not have any wholesale shipping rates set.', { error: true })
            }
          }
          this.rates = response
          this.disableDelete = false // set to prevent deletion being called before getRates returns
        })
    },
    getStoreSettings: function () {
      Settings.getUserStoreSettings(this.id)
        .then((response) => {
          this.shippingFulfillment = response.shipping_fulfillment_time || ''
        })
    },
    getSelfPickup: function () {
        Users.getSetting(this.Auth.getAuthPid(), 'self_pickup')
        .then((response) => {
            if (!response.error) {
                this.selfPickup = response
            }
        })
    },
    saveAllRates: function () {
      this.disableDelete = true // set to prevent deletion being called before getRates returns
      if (this.activeShipping === 'wholesale') {
        this.newRate.type = 'wholesale'
      } else {
        this.newRate.type = 'retail'
      }
      Shipping.createRateRanges(this.rateRequest)
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message
            return
          }
          if (this.activeShipping === 'wholesale') {
            this.getWholesaleRates()
          } else {
            this.getRates()
          }
          this.newRate = {}
          this.$toast('Rates saved.', { dismiss: false })
        })
    },
    addRate: function () {
      this.errorMessages = { max: '', name: '', amount: '' }
      // special client side validation rules for creating a new rate:
      if (this.newRate.max === undefined || this.newRate.max === null || this.newRate.max === '') {
        this.errorMessages.max = 'The price point field is required'
      }
      if (this.newRate.name === undefined || this.newRate.name === null || this.newRate.name === '') {
        this.errorMessages.name = 'The name field is required'
      }
      if (this.newRate.amount === undefined || this.newRate.amount === null || this.newRate.amount === '') {
        this.errorMessages.amount = 'The amount field is required'
      }
      if (this.newRate.type === undefined || this.newRate.type === null || this.newRate.type === '') {
        this.newRate.type = 'retail'
      }

      if (this.errorMessages.max === '' && this.errorMessages.name === '' && this.errorMessages.amount === '') {
        this.rateRequest.ranges = JSON.parse(JSON.stringify(this.rates))
        this.rateRequest.ranges.push(this.newRate)
        this.saveAllRates()
      }
    },
    saveFulfillmentTime: function () {
      var request = {
        key: 'shipping_fulfillment_time',
        value: this.shippingFulfillment
      }
      Settings.saveStoreSetting(request)
      .then((response) => {
        if (!response.error) {
          this.fulfillmentResponse = response
          this.$toast('Fulfillment time updated')
        }
      })
    },
    saveSelfPickup: function () {
        Users.saveSettings(this.Auth.getAuthPid(), {self_pickup: this.selfPickup})
        .then((response) => {
            if (!response.error) {
                this.selfPickup = response.self_pickup
                this.$toast('Self Pickup Setting Saved')
            }
        })
    },
    setFreeRate () {
      if (!this.freeRate) {
        this.newRate.amount = 0
        this.freeRate = true
      } else {
        this.freeRate = false
      }
    },
    deleteRate: function (index) {
      if (!this.disableDelete) { // set to prevent deletion being called before getRates returns
        this.rates.splice(index, 1)
        this.rateRequest.ranges = this.rates
        this.saveAllRates()
      }
    }
  },
  computed: {
    charactersLeft: function () {
      var char = this.shippingFulfillment.length
      var limit = 50
      return (limit - char) + ' / ' + limit + ' characters remaining'
    }
  },
  components: {
    'CpAddressBox': require('../addresses/CpAddressBox.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue')

  }
}
</script>
<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .shipping-rate-wrapper {
        label {
            font-weight: 100;
        }
        .shipping-table-price-rate {
            margin-top: 15px;
        }
        .shipping-info {
            display: flex;
            width: 100%;
            .left-box {
                padding: 20px;
                padding-top: 0;
                width: 25%;
                flex: 1;
            }
            .right-box {
                flex: 0 0 65%;
                width: 65%;
            }
        }
        .fulfillment-wrapper {
            display: flex;
            width: 100%;
            .left-box {
                width: 50%;
            }
            .right-box {
                width: 50%;
                .cp-box-body {
                    height: 80px;
                    .toggle-switch {
                        position: absolute;
                        width: 40px;
                        height: 25px;
                        padding: 0px !important;
                        margin-top: -1px;
                        margin-left: 15px;
                    }
                }
            }
        }
        .rate-name {
            display: flex;
            width: 100%;
            .box-one {
                flex: 0 0 65%;
            }
            .box-two {
                flex: 1;

            }
        }
        .rate-point {
            display: flex;
            width: 100%;
            .box-one {
                flex: 0 0 50%;
            }
            .box-two {
                flex: 1;
            }
        }
        .ship-rate {
            width: 100%;
        }
        .max-rate {
            width: 100%;
        }
        input[type='checkbox'] {
            display: inline-block;
            width: initial;
            margin: 5px;
            height: initial;
        }
        .fulfillment {
            width: 75%;
            text-indent: 3px;
            background: rgb(242, 242, 242);
        }
         @media (max-width: 960px) {
             .fulfillment-wrapper {
                display: inline-block;
                .left-box {
                    width: 100%;
                }
                .right-box {
                    width: 100%;
                }
             }
             .shipping-info {
                 display: block;
                 width: 100%;
                 .left-box {
                     padding: 20px;
                     padding-top: 0;
                     width: 100%;
                 }
                 .right-box {
                     width: 100%;
                 }
             }
        }
        @media (max-width: 767px) {
            .rate-name {
              flex-direction: column;
              -webkit-flex-direction: column;
            }
            .shipping-info {
                .right-box {
                    .toggle-switch {
                        margin-right: 0px;
                    }
                }
            }
        }
    }
</style>
