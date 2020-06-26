<template lang="html">
    <div class="basic-wrapper">
        <div class="cp-box-standard">
          <div class="cp-box-heading">
            <h5>Basic Information</h5>
           </div>
          <div class="cp-box-body">
              <div class="cp-form-standard">
                <div  v-if="userInfo.role_id === 5 && $getGlobal('collect_sponsor_id').show">
                  <label for="Sponsor ID">Sponsor Id</label>
                  <cp-typeahead
                  label="Sponsor"
                  v-if="Auth.hasAnyRole('Superadmin', 'Admin')"
                  :search-term.sync="repSearchTerm"
                  v-model="selectedSponsor"
                  :options="reps"
                  :name-value="{ name: 'full_name', value: 'id'}"
                  @options-cleared="val => reps = val"
                  :search-function="searchReps"></cp-typeahead>
                  {{ selectedSponsor.first_name }}
                  {{ selectedSponsor.last_name }}
                  ({{ selectedSponsor.id }})
                </div>
                <div v-if="Auth.hasAnyRole('Customer') && selectedSponsor != null">
                  <label for="Referer ID">Referer Id</label>
                  {{ selectedSponsor.first_name }}
                  {{ selectedSponsor.last_name }}
                  ({{ selectedSponsor.id }})
                </div>
                <cp-input v-show="$getGlobal('replicated_site').show && userInfo.role_id === 5" label="Display Name" type="text" v-model="displayName" :error="validationErrors['display_name']"></cp-input>
                <cp-input v-show="Auth.hasAnyRole('Rep') && $getGlobal('replicated_site').show" label="Store Name (Public ID)" type="text" v-model="userInfo.public_id" :error="validationErrors['public_id']"></cp-input>
                <cp-input label="First Name" type="text" v-model="userInfo.first_name" :error="validationErrors['first_name']"></cp-input>
                <cp-input label="Last Name" type="text" v-model="userInfo.last_name" :error="validationErrors['last_name']"></cp-input>
                <cp-input label="E-mail" type="text" :error="validationErrors['email']" v-model="userInfo.email"></cp-input>
                <cp-input-mask
                v-if="Auth.hasAnyRole('Superadmin', 'Admin', 'Rep')"
                label="Phone Number"
                type="text"
                mask="###-###-####"
                placeholder="###-###-####"
                :error="validationErrors['phone.number']"
                v-model="userInfo.phone_number"></cp-input-mask>
                <cp-input label="New Password" type="password" name="" v-model="userInfo.password"></cp-input>
                <cp-input label="Confirm Password" type="password" name="" v-model="userInfo.password_confirmation" ></cp-input>
              </div>
            <!-- billing accordion -->
              <div class="cp-accordion">
                <div class="cp-accordion-head" @click="billing = !billing">
                  <h5> Billing Address </h5>
                  <span v-if="billing" class="arrow"><i class="mdi mdi-chevron-down"></i></span>
                  <span v-if="!billing" class="arrow"><i class="mdi mdi-chevron-left"></i></span>
                </div>
                <div class="cp-form-standard cp-accordion-body" :class="{closed: billing === true}">
                  <div class="cp-accordion-body-wrapper">
                    <cp-input label="Address Name" type="text" name="" :error="validationErrors['billing_address.name']"  v-model="userInfo.billing_address.name"  placeholder="Name"></cp-input>
                    <cp-input label="Address 1" type="text" name="" :error="validationErrors['billing_address.address_1']"  v-model="userInfo.billing_address.address_1"placeholder="Street address, P.O. Box"></cp-input>
                    <cp-input label="Address 2" type="text" name="" :error="validationErrors['billing_address.address_2']"  v-model="userInfo.billing_address.address_2" placeholder="Apartment, suite, building, floor"></cp-input>
                    <cp-input label=" City" type="text" name="" :error="validationErrors['billing_address.city']"  v-model="userInfo.billing_address.city" placeholder="City"></cp-input>
                    <cp-select label=" State"billing_address
                    :error="validationErrors['billing_address.state']"
                    v-model="userInfo.billing_address.state"
                    :options="states"
                    :key-value="{name: 'name', value: 'value'}"></cp-select>
                    <cp-input  label="Zipcode" type="text" name="" :error="validationErrors['billing_address.zip']" v-model="userInfo.billing_address.zip"  placeholder="Zipcode"></cp-input>
                  </div>
                </div>
              </div>
              <!-- shipping accordion -->
              <div class="cp-accordion">
                <div class="cp-accordion-head" @click="shipping = !shipping">
                  <h5>Shipping Address</h5>
                  <span v-if="shipping" class="arrow"><i class="mdi mdi-chevron-down"></i></span>
                  <span v-if="!shipping" class="arrow"><i class="mdi mdi-chevron-left"></i></span>
                </div>
                <div class="cp-form-standard cp-accordion-body" :class="{closed: shipping === true}">
                  <div class="cp-accordion-body-wrapper">
                    <cp-input label="Address Name" type="text" name="" :error="validationErrors['shipping_address.name']"  v-model="userInfo.shipping_address.name"  placeholder="Name"></cp-input>
                    <cp-input label="Address 1" type="text" name="" :error="validationErrors['shipping_address.address_1']"  v-model="userInfo.shipping_address.address_1"placeholder="Street address, P.O. Box"></cp-input>
                    <cp-input label="Address 2" type="text" name="" :error="validationErrors['shipping_address.address_2']"  v-model="userInfo.shipping_address.address_2" placeholder="Apartment, suite, building, floor"></cp-input>
                    <cp-input label=" City" type="text" name="" :error="validationErrors['shipping_address.city']"  v-model="userInfo.shipping_address.city" placeholder="City"></cp-input>
                    <cp-select label=" State"
                    :error="validationErrors['shipping_address.state']"
                    v-model="userInfo.shipping_address.state"
                    :options="states"
                    :key-value="{name: 'name', value: 'value'}"></cp-select>
                    <cp-input  label="Zipcode" type="text" name="" :error="validationErrors['shipping_address.zip']" v-model="userInfo.shipping_address.zip"  placeholder="Zipcode"></cp-input>
                  </div>
                </div>
              </div>
              <!-- business accordion -->
              <div class="cp-accordion" v-if="Auth.hasAnyRole('Rep')">
               <div class="cp-accordion-head" @click="business = !business">
                 <h5>Business Address</h5>
                  <!-- <small>(Shipped from Setting)</small> -->
                   <span v-if="business" class="arrow"><i class="mdi mdi-chevron-down"></i></span>
                   <span v-if="!business" class="arrow"><i class="mdi mdi-chevron-left"></i></span>
                   </div>
                   <div class="cp-form-standard cp-accordion-body" :class="{closed: business === true}">
                     <div class="cp-accordion-body-wrapper">
                       <cp-input label="Address Name" type="text" name="" :error="validationErrors['business_address.name']"  v-model="userInfo.business_address.name"  placeholder="Name"></cp-input>
                       <cp-input label="Address 1" type="text" name="" :error="validationErrors['business_address.address_1']"  v-model="userInfo.business_address.address_1"placeholder="Street address, P.O. Box"></cp-input>
                       <cp-input label="Address 2" type="text" name="" :error="validationErrors['business_address.address_2']"  v-model="userInfo.business_address.address_2" placeholder="Apartment, suite, building, floor"></cp-input>
                       <cp-input label=" City" type="text" name="" :error="validationErrors['business_address.city']"  v-model="userInfo.business_address.city" placeholder="City"></cp-input>
                       <cp-select label=" State"
                       :error="validationErrors['business_address.state']"
                       v-model="userInfo.business_address.state"
                       :options="states"
                       :key-value="{name: 'name', value: 'value'}"></cp-select>
                       <cp-input  label="Zipcode" type="text" name="" :error="validationErrors['business_address.zip']" v-model="userInfo.business_address.zip"  placeholder="Zipcode"></cp-input>
                     </div>
                    </div>
                  </div>
              <div class="button-wrapper" v-if="Auth.hasAnyRole('Superadmin', 'Admin') || $getGlobal('rep_edit_information').show">
                <button class="cp-button-standard" @click="updateUser()">Save User Info</button>
              </div>
            </div>
          </div>
      </div>
</template>

<script>
const Users = require('../../../resources/users.js')
const StoreSetting = require('../../../resources/settings.js')
const Auth = require('auth')
const { states } = require('../../../resources/states.js')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      Auth: Auth,
      states: states,
      billing: true,
      shipping: true,
      business: true,
      user: this.userInfo,
      displayName: '',
      errorMessages: {},
      validationErrors: {},
      selectedSponsor: {},
      reps: [],
      repSearchTerm: ''
    }
  },
  props: {
    userInfo: {
      required: true
    },
    userId: {
      required: true
    }
  },
  mounted () {
    this.getDisplayName()
    if (this.userInfo.sponsor) {
      this.selectedSponsor = this.userInfo.sponsor
    }
  },
  methods: {
    searchReps: _.debounce(function (value) {
      this.reps = []
      Users.searchSponsors({'search_term': value})
        .then((response) => {
          if (response.error) {
            this.reps = []
            return false
          }
          // format response for typehead
          for (var i = 0; i < response.length; i++) {
            response[i].full_name = response[i].first_name + ' ' + response[i].last_name + ' (' + response[i].id + ')'
          }
          this.reps = response
        })
    }, 600),
    cleanDashes (number) {
      if (number) {
        number = String(number)
        number = number.replace(/-/g, '').replace(/\(|\)/g, '')
        number = parseInt(number)
      }
      return number
    },
    updateUser () {
      this.errorMessages = {}
      let request = JSON.parse(JSON.stringify(this.userInfo))
      request.sponsor_id = this.selectedSponsor.id
      if (!this.$isBlank(request.phone_number)) {
        request.phone = { number: this.cleanDashes(request.phone_number) }
      }
      if (this.userInfo.role_id !== 5) {
        delete request.business_address
      }
      Users.update(request)
        .then((response) => {
          if (response.error && response.code === 422) {
            this.errorMessages = response.message
            return
          }
          this.$emit('user-info-updated')
          this.$toast('User updated successfully', {dismiss: false})
        })
      request = {
        key: 'display_name',
        value: this.displayName
      }
      if (request.value) {
        StoreSetting.saveStoreSetting(request)
          .then((response) => {
            if (response.error) {
              this.errorMessages = response.message
              this.$toast(response.message, { error: true, dismiss: false })
              return
            }
            this.$toast('Display name updated.', {dismiss: false})
          })
      }
    },
    getDisplayName: function () {
      StoreSetting.getUserStoreSettings(this.userId)
        .then((response) => {
          this.displayName = response.display_name
        })
    }
  },
  components: {
    CpInput: require('../../../cp-components-common/inputs/CpInput.vue'),
    CpTypeahead: require('../../../cp-components-common/inputs/CpTypeahead.vue')
  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .basic-wrapper {
        .cp-form-standard {
          input {
            text-indent: 6px;
            padding: 0px;
          }
          margin: 5px;
        }
    }
</style>
