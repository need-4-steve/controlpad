<template lang="html">
    <div class="branding-wrapper">
        <div class="cp-accordion">
            <div class="cp-accordion-head black-header">
                <h5>Registration Settings</h5>
            </div>
            <div class="cp-accordion-body">
                <div class="cp-accordion-body-wrapper">
                    <div class="cp-left-col">
                        <div>
                            <h4>User registration options</h4>
                            <hr />
                            <div class="line-wrapper">
                                <label>Users can register through built in registration </label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.register_without_controlpad_api.show">
                            </div>
                            <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                                <label>Collect sponsor ID on registration</label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.collect_sponsor_id.show">
                            </div>
                            <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin') && registration_settings.collect_sponsor_id.show">
                                <label>Require sponsor ID on registration </label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.require_sponsor_id_on_registration.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Collect phone number on registration </label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.collect_phone_on_registration.show">
                            </div>
                            <div class="line-wrapper" v-if="registration_settings.collect_phone_on_registration.show">
                                <label>Require phone number on registration </label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.require_phone_on_registration.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Users can register using an integration API</label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.register_with_controlpad_api.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Require a registration code on sign up</label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.require_registration_code.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Charge shipping for starter kits</label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.registration_shipping.value">
                            </div>
                            <div class="line-wrapper">
                                <label>Add generated coupon code to Welcome Email<cp-tooltip :options="{ content: 'The variable [coupon_code] will still need to be added to the email.'}"></cp-tooltip></label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.registration_coupon.show">
                            </div>
                            <div class="line-wrapper" v-if="registration_settings.registration_coupon.show">
                                <label>Registration Coupon Amount<cp-tooltip :options="{ content: 'The coupon amount will defalut to the order total for the starter kit if no value is specified.'}"></cp-tooltip></label>
                                <input class="input-class" type="number" v-model="registration_settings.registration_coupon.value" @keyup="checkForNull()">
                            </div>
                            <div class="line-wrapper" v-show="registration_settings.require_registration_code.show">
                              <label>Required Code:</label>
                              <input class="input-class" v-model="registration_settings.require_registration_code.value" />
                            </div>
                            <div class="line-wrapper">
                                <label>Redirect on sign up<cp-tooltip :options="{ content: 'By default a new user is logged in to the back office'}"></cp-tooltip></label>
                                <input class="toggle-switch" type="checkbox" v-model="registration_settings.join_redirect.show">
                            </div>
                            <div class="line-wrapper" v-show="registration_settings.join_redirect.show">
                              <label>Join Redirect:</label>
                              <input class="input-class" v-model="registration_settings.join_redirect.value" />
                            </div>
                            <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                                <label>Merchant Category Code:</label>
                                <input class="input-class" type="text" v-model="registration_settings.merchant_category_code.value">
                            </div>
                        </div>
                    </div>
                    <div class="cp-right-col">
                      <div v-if="registration_settings.registration_payment_options.show">
                        <h4>Payment Options</h4>
                        <hr />
                        <div class="line-wrapper">
                          <label>Allow Credit Cards</label>
                          <input class="toggle-switch" type="checkbox" v-model="registration_settings.registration_payment_options.value.credit_card">
                        </div>
                        <!--DEBIT CARD NOT SUPPORTED YET -->
                        <!--div class="line-wrapper">
                          <label>Allow Debit Cards</label>
                          <input class="toggle-switch" type="checkbox" v-model="registration_settings.registration_payment_options.value.debit_card">
                        </div -->
                        <div class="line-wrapper">
                          <label>Allow E-Checks</label>
                          <input class="toggle-switch" type="checkbox" v-model="registration_settings.registration_payment_options.value.e_check">
                        </div>
                      </div>
                    </div>
                    <div class="save-settings-button">
                        <input class="cp-button-standard" type="button" @click="saveRegistrationSettings()" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const Auth = require('auth')
const Settings = require('../../../resources/settings.js')

module.exports = {
  data: function () {
    return {
      Auth,
      closed: true,
      registration_settings: {
        register_with_controlpad_api: {},
        register_without_controlpad_api: {},
        require_registration_code: {},
        merchant_category_code: {},
        reseller_payment_option: {},
        collect_sponsor_id: {},
        require_sponsor_id_on_registration: {},
        collect_phone_on_registration: {},
        require_phone_on_registration: {},
        registration_shipping: {},
        registration_coupon: {},
        join_redirect: {},
        registration_payment_options: {
          value: {}
        }
      }
    }
  },
  computed: {},
  mounted () {
    this.getRegistrationSettings()
  },
  methods: {
    getRegistrationSettings: function () {
      Settings.getRegistrationSettings()
        .then((response) => {
          this.registration_settings = response
        })
    },
    saveRegistrationSettings: function () {
      Settings
        .update(this.registration_settings)
        .then((response) => {
          this.$updateGlobal(this.registration_settings)
          this.$toast('Registration settings saved successfully.')
        })
    },
    checkForNull: function () {
        if (this.registration_settings.registration_coupon.value === "") {
            this.registration_settings.registration_coupon.value = null
        }
    }
  },
  components: {}
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .branding-wrapper {
        .cp-accordion-head {
            &.black-header {
                position: relative;
                padding: 10px;
                height: auto;
                h5 {
                    margin: 0;
                    display: inline-block;
                    margin-right: 0px;
                    font-weight: 300;
                    font-size: 1.2em;
                }
                background-color: $cp-main;
                color: $cp-main-inverse;
            }
        }
        .cp-left-col {
            width: 48%;
        }
        .cp-right-col {
            width: 48%;
        }
        .line-wrapper {
            display: flex;
            -webkit-display: flex;
            justify-content: space-between;
            -webkit-justify-content: space-between;
            label {
                font-size: 14px;
                font-weight: 300;
                margin-bottom: 0;
            }
            .input-class {
                height: 100%;
                width: 50%;
                height: 30px;
                text-indent: 10px;
                margin: 5px 0;

            }
            &.toggle-switch {
                width: 40px;
            }
        }
    }
</style>
