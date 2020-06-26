<template lang="html">
  <div class="shippo-wrapper">
    <section class="align-center shippo-loading" v-show="loading">
        <p>Please Wait</p>
        <img class="loading" :src="$getGlobal('loading_icon').value">
    </section>
    <div v-show="!loading">
      <!-- STEP ONE  -->
      <!-- FORM TO GET LIST OF SHIPPING RATES AND CARRIERS -->
      <section class="shippo-step" v-show="steps.one">
        <h3>Step One: Enter Package Information</h3>
        <form class="cp-form-standard">
          <div class="shipping-section">
            <h4>Parcel Dimensions</h4>
            <cp-input
            subtext="(in.) :"
            label="Length"
            type="number"
            :error="validationErrors['parcel.length']"
             v-model="ratesRequest.parcel.length"
             placeholder="Length"></cp-input>
            <cp-input
            subtext="(in.) :"
            label="Width"
            type="number"
            :error="validationErrors['parcel.width']"
            v-model="ratesRequest.parcel.width"
            placeholder="Width"></cp-input>
            <cp-input
            subtext="(in.) :"
            label="Height "
            type="number"
            :error="validationErrors['parcel.height']"
             v-model="ratesRequest.parcel.height"
             placeholder="Height"></cp-input>
            <cp-input
            subtext="(lb.) :"
             label="Weight"
             type="number"
             :error="validationErrors['parcel.weight']"
             v-model="ratesRequest.parcel.weight"
             placeholder="Weight"></cp-input>
          </div>
        </form>
        <div class="shipping-addresses-wrapper">
          <div class="shipping-section shipping-address-section1">
            <h4>Shipping To Address </h4>
            <ul class="shippo-adddress-show" v-show="!shippingAddressEdit">
              <li><strong>{{ shippingAddress.name }}</strong></li>
              <li>{{ shippingAddress.line_1 }}</li>
              <li v-if="shippingAddress.line_2 !== ''">{{ shippingAddress.line_2 }}</li>
              <li>{{ shippingAddress.city }}</li>
              <li>{{ shippingAddress.state }}</li>
              <li>{{ shippingAddress.zip }}</li>
              <li>{{ toContact.email }}</li>
              <li><br /></li>
            </ul>
            <form class="cp-form-standard" v-show="shippingAddressEdit">
              <cp-input
              label="Name:"
              :error="validationErrors['address_to.name']"
              v-model="shippingAddress.name"
              placeholder="Name"></cp-input>
              <cp-input
              label="Address Line 1:"
              :error="validationErrors['address_to.street1']"
              v-model="shippingAddress.line_1"
              placeholder="Street address, P.O. Box"></cp-input>
              <cp-input
              label="Address Line 2:"
              :error="validationErrors['address_to.street2']"
              v-model="shippingAddress.line_2"
              placeholder="Apartment, suite, building, floor"></cp-input>
              <cp-input
              label="City: "
              :error="validationErrors['address_to.city']"
              v-model="shippingAddress.city"
              placeholder="City"></cp-input>
                <cp-select
                label="State: "
                :error="validationErrors['address_to.state']"
                 v-model="shippingAddress.state"
                 :options="states"
                 :key-value="{name: 'name', value: 'value'}">
               </cp-select>
              <cp-input
              label="Zip Code:"
              :error="validationErrors['address_to.zip']"
              v-model="shippingAddress.zip"
              placeholder="Zip Code"></cp-input>
              <cp-input
              label="Email:"
              :error="validationErrors['address_to.email']"
              v-model="toContact.email"
              placeholder="Email"></cp-input>
            </form>
            <button v-show="!shippingAddressEdit" class="cp-button-standard edit-address-button" @click="shippingAddressEdit = true">Edit</button>
            <button v-show="shippingAddressEdit" class="cp-button-standard edit-address-button" @click="editShippingAddress()">Save</button>
          </div>
          <div class="shipping-section shipping-address-section2">
            <h4>Shipping From Address</h4>
            <ul class="shippo-adddress-show" v-if="!fromAddressEdit">
              <li><strong>{{ fromAddress.name }}</strong></li>
              <li>{{ fromAddress.line_1 }}</li>
              <li v-if="fromAddress.line_2 !== ''">{{ fromAddress.line_2 }}</li>
              <li>{{ fromAddress.city }}</li>
              <li>{{ fromAddress.state }}</li>
              <li>{{ fromAddress.zip }}</li>
              <li>{{ fromContact.phone }}</li>
              <li>{{ fromContact.email }}</li>
            </ul>
            <form class="cp-form-standard" v-show="fromAddressEdit">
              <cp-input
              label="Name:"
              :error="validationErrors['address_from.name']"
              v-model="fromAddress.name"
              placeholder="Name"></cp-input>
              <cp-input
              label="Address Line 1:"
              :error="validationErrors['address_from.street1']"
              v-model="fromAddress.line_1"
              placeholder="Street address, P.O. Box"></cp-input>
              <cp-input
              label="Address Line 2: "
              :error="validationErrors['address_from.street2']"
              v-model="fromAddress.line_2"
              placeholder="Apartment, suite, building, floor"></cp-input>
              <cp-input
              label="City:"
              :error="validationErrors['address_from.city']"
              v-model="fromAddress.city"
              placeholder="City"></cp-input>
              <cp-select
              label="State:"
              v-model="fromAddress.state"
              :error="validationErrors['address_from.state']"
              :options="states"
              :key-value="{name: 'name', value: 'value'}"></cp-select>
              <cp-input
              label="Zip Code:"
              :error="validationErrors['address_from.zip']"
              v-model="fromAddress.zip"
              placeholder="Zip Code"></cp-input>
              <cp-input
              label="Email:"
              :error="validationErrors['address_from.email']"
              v-model="fromContact.email"
              placeholder="Email"></cp-input>
              <cp-input
              label="Phone:"
              :error="validationErrors['address_from.phone']"
              v-model="fromContact.phone"
              placeholder="Phone"></cp-input>
            </form>
            <button v-show="!fromAddressEdit" class="cp-button-standard edit-address-button" @click="fromAddressEdit = true">Edit</button>
            <button v-show="fromAddressEdit" class="cp-button-standard edit-address-button" @click="editAddress(fromAddress, 'from')">Save</button>
          </div>
        </div>
        <button class="cp-button-standard continue-button" @click="getShippoRates()">Next</button>
      </section>
      <!-- STEP TWO -->
      <!-- SELECT CARRIER AND RATE -->
      <section class="shippo-step" v-show="steps.two">
        <h3>Step Two: Choose Delivery Option</h3>
        <table class="cp-table-standard shippo-table">
          <tr>
            <th> </th>
            <th>Service Level</th>
            <th>Carrier</th>
            <th>Transit Time</th>
            <th>Amount</th>
          </tr>
          <tr v-for="rate in shippingRates" >
            <td><input class="shippo-select-radio" v-bind:id="rate.object_id" :name="rate.object_id" type="radio" :value="rate" v-model="selectedRate"></td>
            <td><label :for="rate.object_id"><span class="rate-select-name">{{ rate.servicelevel_name }}</span></label></td>
            <td><label :for="rate.object_id"><img :src="rate.provider_image_75" :alt="rate.provider"></label></td>
            <td><label :for="rate.object_id">{{ rate.duration_terms }}</label></td>
            <td><label :for="rate.object_id">{{ rate.total_price | currency }}</label></td>
          </tr>
        </table>
        <button class="cp-button-standard continue-button" @click="selectRate()">Next</button>
      </section>
      <!-- STEP THREE  -->
      <!-- ENTER PAYMENT INFORMATION -->
      <section class="shippo-step" v-if="steps.three">
        <h3>Step Three: Enter Payment Information</h3>
        <h4>Selected Delivery Option: </h4>
        <div class="shipping-section">
          <ul class="shippo-adddress-show">
            <li><strong>Service Level: </strong> {{ selectedRate.servicelevel_name }}</li>
            <li><strong>Provider: </strong><img :src="selectedRate.provider_image_75" :alt="selectedRate.provider"></li>
            <li><strong>Transit Time: </strong>{{ selectedRate.duration_terms }}</li>
            <li><strong>Cost: </strong>{{ selectedRate.amount | currency }}</li>
          </ul>
        </div>
        <div class="shipping-section">
          <form class="cp-form-standard">
            <h4>Enter Billing Address: </h4>
            <cp-input
            :error="validationErrors['payment.address_1']"
            label="Address Line 1:"
            v-model="payment.address_1"
            placeholder="Street address, P.O. Box"></cp-input>
            <cp-input
            :error="validationErrors['payment.address_']"
            label="Address Line 2:"
            v-model="payment.address_2"
            placeholder="Apartment, suite, building, floor"></cp-input>
            <cp-input
            :error="validationErrors['payment.city']"
            label="City:"
            v-model="payment.city"
            placeholder="City"></cp-input>
              <cp-select
               v-model="payment.state"
               :options="states"
               :key-value="{name: 'name', value: 'value'}"
               :error="validationErrors['payment.state']"
                ></cp-select>
            <cp-input
            :error="validationErrors['payment.zip']"
            label="Zip Code:"
            v-model="payment.zip"
            placeholder="Zip Code"></cp-input>
            <h4>Enter Payment Information: </h4>
            <cp-input
            :error="validationErrors['payment.name']"
            label="Name: "
            v-model="payment.name"
            placeholder="Name"></cp-input>
            <cp-input
            :error="validationErrors['payment.card_number']"
            label="Card Number:"
            type="number"
            v-model="payment.card_number"
            placeholder="Card Number"></cp-input>
            <cp-input
            :error="validationErrors['payment.security']"
            label="Security Code:"
            v-model="payment.security"
            placeholder="Security Code"></cp-input>
            <div class="expiration-dates">
                <cp-select
                label="Expiration:"
                 v-model="payment.month"
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
                 { name: '12', value: '12' }]"
                 ></cp-select>
                <cp-select
                label="Year:"
                 v-model="payment.year"
                 :error="validationErrors['payment.year']"
                 :options="years"
                 :key-value="{name: 'name', value: 'name'}"
                 ></cp-select>
               </div>
          </form>
        </div>
        <button class="cp-button-standard continue-button" @click="purchaseLabel()">Purchase Label</button>
      </section>
      <!-- STEP FOUR  -->
      <!-- ENTER PAYMENT INFORMATION -->
      <section class="shippo-step" v-show="steps.four">
        <h3>Step Four: Print Label</h3>
        <div class="shipping-section shippo-payment-success">
          <h4>Your payment was successfully received!</h4>
          <p>With the following link you may print your shipping label.</p>
          <a :href="label.label_url" target="_blank" class="cp-button-link">Print Label</a>
        </div>
        <button v-if="order !== null" class="cp-button-standard continue-button" @click="$emit('close')">Return To Orders</button>
      </section>
      <button v-if="steps.one && order" class="cp-button-standard close-button" @click="$emit('close')">Cancel</button>
      <button v-if="!steps.one && !steps.four" class="cp-button-standard close-button" @click="setStep('previous')">Back</button>
    </div>
  </div>
</template>

<script>
const Auth = require('auth')
const Shipping = require('../../resources/shipping.js')
const Addresses = require('../../resources/addresses.js')
const Users = require('../../resources/UsersAPIv0.js')
const Orders = require('../../resources/OrdersAPIv0.js')
const { states } = require('../../resources/states.js')

module.exports = {
  data () {
    return {
      loading: false,
      states: states,
      years: [],
      steps: {
        one: true,
        two: false,
        three: false,
        four: false
      },
      ratesRequest: {
        parcel: {
          distance_unit: 'in',
          mass_unit: 'lb'
        },
        address_to: {},
        address_from: {}
      },
      order: null,
      validationErrors: {},
      shippingAddress: {},
      fromAddress: {},
      toContact: { email: '' },
      fromContact: { phone: '', email: '' },
      shippingAddressEdit: false,
      fromAddressEdit: false,
      shippingRates: {},
      selectedRate: null,
      payment: {},
      paymentErrors: {},
      label: {}
    }
  },
  props: {
    // optional prop: for use when putting this component in a modal
    showModal: {
      type: Boolean,
      required: false
    },
    getOrders: {
      type: Function
    }
  },
  mounted () {
    this.getYears()
    this.getOwner()
    if (!this.order) {
      this.shippingAddressEdit = true
    }
  },
  methods: {
    getYears: function () {
      var currentYear = new Date().getFullYear()
      for (var i = 0; i <= 10; i++) {
        this.years.push({name: currentYear + i})
      }
    },
    setOrder(order) {
      this.order = order
      this.toContact.email = order.buyer_email
      this.shippingAddress = order.shipping_address
      this.shippingAddressEdit = false
    },
    getOwner() {
      let request = {
        addresses: true
      }
      Users.getById(request, Auth.getOwnerId())
        .then((response) => {
          if (response.error && response.code === 404) {
            return
          }
          this.fromAddress = response.business_address
          this.fromContact.email = response.email
          this.fromContact.phone = response.phone
          this.copyAddress(this.fromAddress)
          this.fromAddressEdit = !this.fromContact.phone
        })
    },
    setStep (step) {
      // go back to pervious button if set to previous
      var previousStep = ''
      if (step === 'previous') {
        for (let key in this.steps) {
          if (this.steps[key] === true) {
            this.steps[key] = false
            this.steps[previousStep] = true
            return
          }
          previousStep = key
        }
      } else {
        // other wise go to specificed step assigned by argument
        for (let key in this.steps) {
          if (key === step) {
            this.steps[key] = true
          } else {
            this.steps[key] = false
          }
        }
      }
    },
    prepareShippingRatesRequest () {
      this.ratesRequest.address_to = {
        object_purpose: 'PURCHASE',
        name: this.shippingAddress.name,
        street1: this.shippingAddress.line_1,
        street2: this.shippingAddress.line_2,
        state: this.shippingAddress.state,
        city: this.shippingAddress.city,
        zip: this.shippingAddress.zip,
        country: 'US',
        email: this.toContact.email
      }
      this.ratesRequest.address_from = {
        object_purpose: 'PURCHASE',
        name: this.fromAddress.name,
        street1: this.fromAddress.line_1,
        street2: this.fromAddress.line_2,
        state: this.fromAddress.state,
        city: this.fromAddress.city,
        zip: this.fromAddress.zip,
        country: 'US',
        phone: this.fromContact.phone,
        email: this.fromContact.email
      }
    },
    getShippoRates () {
      let errors = this.getAddressErrors(this.shippingAddress, 'address_to')
      errors = Object.assign(errors, this.getAddressErrors(this.fromAddress, 'address_from'))
      if (this.isBlank(this.ratesRequest.parcel.length)) {
        errors['parcel.length'] = ['Required']
      }
      if (this.isBlank(this.ratesRequest.parcel.width)) {
        errors['parcel.width'] = ['Required']
      }
      if (this.isBlank(this.ratesRequest.parcel.height)) {
        errors['parcel.height'] = ['Required']
      }
      if (this.isBlank(this.ratesRequest.parcel.weight)) {
        errors['parcel.weight'] = ['Required']
      }
      if (this.isBlank(this.toContact.email)) {
        errors['address_to.email'] = ['Required']
      }
      if (this.isBlank(this.fromContact.phone)) {
        errors['address_from.phone'] = ['Required']
      }
      if (this.isBlank(this.fromContact.email)) {
        errors['address_from.email'] = ['Required']
      }
      if (Object.keys(errors).length > 0) {
        this.validationErrors = errors
        return
      }
      // set to step 2
      // prepare request
      this.prepareShippingRatesRequest()

      // api request for shipping rates
      this.loading = true
      window.scrollTo(0, 0) // to make sure the user sees the loading gif
      Shipping.shippoRates(this.ratesRequest)
        .then((response) => {
          this.loading = false
          if (response.error) {
            this.shippingRates = {}
            this.validationErrors = response.message
            return
          }
          this.validationErrors = {}
          this.setStep('two')
          this.shippingRates = response
        })
    },
    selectRate () {
      if (!this.selectedRate) {
        return this.$toast('Please select a rate to continue.', { error: true })
      }
      this.setStep('three')
    },
    purchaseLabel () {
      // purchase label
      this.loading = true
      var request = {
        order_id: null,
        rate_id: this.selectedRate.object_id,
        payment: this.payment
      }
      if (this.order) {
        request.order_id = this.order.id
      }
      window.scrollTo(0, 0) // to make sure the user sees the loading gif
      Shipping.label(request)
        .then((response) => {
          this.loading = false
          if (response.error) {
            // check for unique to shippo validation rules
            if (response.code === 400 && response.message.text) {
              this.$toast(response.message.text, { error: true })
              return
            }
            this.validationErrors = response.message
            return // prevent moving to next step in case of error
          }
          this.label = response
          this.setStep('four')
          this.getOrders()
        })
    },
    getAddressErrors(address, errorLabel) {
      let errors = [];
      if (this.isBlank(address.name)) {
        errors[errorLabel + '.name'] = ['Required']
      }
      if (this.isBlank(address.line_1)) {
        errors[errorLabel + '.street1'] = ['Required']
      }
      if (this.isBlank(address.city)) {
        errors[errorLabel + '.city'] = ['Required']
      }
      if (this.isBlank(address.state)) {
        errors[errorLabel + '.state'] = ['Required']
      }
      if (this.isBlank(address.zip)) {
        errors[errorLabel + '.zip'] = ['Required']
      }
      return errors
    },
    isBlank (str) {
      return (!str || /^\s*$/.test(str));
    },
    copyAddress: function (address) {
      this.payment.address_1 = address.line_1
      this.payment.address_2 = address.line_2
      this.payment.city = address.city
      this.payment.state = address.state
      this.payment.zip = address.zip
    },
    editShippingAddress: function () {
      let shippingErrors = this.getAddressErrors(this.shippingAddress, 'address_to')
      if (Object.keys(shippingErrors).length > 0) {
        this.validationErrors = shippingErrors
        return
      }
      this.
      Orders.updateShippingAddress(this.shippingAddress, this.order.id)
        .then((response) => {
          if (response.error) {
            this.toAddressErrors = response.message
            return
          }
          this.setOrder(response)
        })
    },
    editFromAddress: function () {
      let fromErrors = this.getAddressErrors(this.fromAddress, 'address_from')
      if (Object.keys(fromErrors).length > 0) {
        this.validationErrors = fromErrors
        return
      }
      Addresses.create(this.fromAddress)
        .then((response) => {
          if (response.error) {
            return
          }
          this.fromAddress = response
        })
    }
  },
  components: {
    CpAddressBox: require('../addresses/CpAddressBox.vue'),
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue')
  }
}
</script>

<style lang="scss">
.shippo-wrapper {
  label {
    display: block;
  }
  .expiration-dates {
        display: flex;
        justify-content: space-between;
       input {
        margin-top:5px;
      } span:nth-child(2) {
      width: 49%;
      } span:nth-child(1){
      width: 49%;
    }
  }
  .shipping-section {
    width: 100%;
    border: 1px solid lightgrey;
    padding: 20px;
    overflow: hidden;
    margin-bottom: 20px;
  }
  .continue-button {
    float: right;
  }
  .shippo-adddress-show {
    padding: 0;
    list-style: none;
  }
  .edit-address-button {
    float: right;
  }
  .shippo-table {
    margin-bottom: 20px;
  }
  .shippo-payment-success {
    text-align: center;
  }
  .shipping-addresses-wrapper {
    overflow: hidden;
    width: 100%;
    .shipping-address-section1 {
      width: 47%;
      float: left;
    }
    .shipping-address-section2 {
      width: 47%;
      float: right;
    }
  }
  .shippo-loading {
    padding: 25px;
  }
  .rate-select-name {
    width: 100%;
    height: 100%;
    padding: 5px;
    cursor: pointer;
  }
  .shippo-select-radio {
    padding: 60px;
    border: 1px solid #444;
    cursor: pointer;
  }
}
</style>
