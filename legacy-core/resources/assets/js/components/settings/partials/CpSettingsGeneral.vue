<template lang="html">
    <div class="branding-wrapper">
        <div class="cp-accordion">
            <div class="cp-accordion-head black-header">
                <h5>General Settings</h5>
            </div>
            <div class="cp-accordion-body">
                <div class="cp-accordion-body-wrapper">
                    <div class="cp-left-col">
                        <h4>Company Info</h4>
                        <hr />
                        <div class="line-wrapper">
                            <label>Company Name</label>
                            <input class="input-class" type="text" v-model="general_settings.company_name.value">
                        </div>
                        <div class="line-wrapper">
                            <label>Company Address</label>
                            <input class="input-class" type="text" v-model="general_settings.address.value">
                        </div>
                        <div class="line-wrapper">
                            <label>Company Phone Number</label>
                            <input class="input-class" type="text" v-model="general_settings.phone.value">
                        </div>
                        <div class="line-wrapper">
                            <label>Company E-mail</label>
                            <input class="input-class" type="text" v-model="general_settings.company_email.value">
                        </div>
                        <div class="line-wrapper">
                            <label>Order Notification E-mail</label>
                            <input class="input-class" type="text" v-model="general_settings.order_notification_email.value">
                        </div>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                            <label>From E-mails</label>
                            <input class="input-class" type="text" v-model="general_settings.from_email.value">
                        </div>
                        <div class="line-wrapper">
                          <label>EIN</label>
                            <cp-input-mask
                              custom-class="input-class"
                              type="text"
                              placeholder="EIN"
                              :mask="'##-########'"
                              :error="null"
                              v-model="general_settings.ein.value">
                          </cp-input-mask>
                        </div>
                        <div class="line-wrapper">
                            <label>Show EIN on Order Receipt</label>
                            <input class="toggle-switch" type="checkbox" v-model="general_settings.ein.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Corporate can use coupons</label>
                            <input class="toggle-switch" type="checkbox" v-model="general_settings.corp_coupons.show">
                        </div>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                            <label>Allow Edit to User Join Date</label>
                            <input class="toggle-switch" type="checkbox" v-model="general_settings.edit_join_date.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Allow users access to PayQuicker</label>
                            <input class="toggle-switch" type="checkbox" v-model="general_settings.payquicker.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Allow Superadmin access to Direct Deposit</label>
                            <input class="toggle-switch" type="checkbox" v-model="general_settings.direct_deposit.show">
                        </div>
                            <cp-toggle label="Allow Corporate to use YouTube" v-model="general_settings.corp_youtube.show"></cp-toggle>
                        <div class="line-wrapper">
                            <label>Number of Hours Invoices are Stored</label>
                            <input class="input-class" type="number" v-model="general_settings.einvoice_expire_time.value">
                        </div>
                        <div class="line-wrapper">
                            <label>Enable Shipping Label Creation</label>
                            <input class="toggle-switch" type="checkbox" v-model="general_settings.enable_shipping_label_creation.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Auto export orders to third party service
                                <cp-tooltip :options="{ content: 'This setting can only be updated when the system is integrated with a third party shipping service. Automatically exports corporate orders to the shipping service.'}"></cp-tooltip>
                            </label>
                            <input v-if="general_settings.shipping_team_id.value == 'company'" class="toggle-switch" type="checkbox" v-model="general_settings.auto_transfer_orders.value">
                            <input v-else class="toggle-switch" type="checkbox" v-model="general_settings.auto_transfer_orders.value" disabled>
                        </div>
                        <div class="line-wrapper">
                            <label>Announcements Title</label>
                            <input class="input-class" type="text" v-model="general_settings.title_announcement.value">
                        </div>
                        <div class="line-wrapper">
                            <label>Your Timezone</label>
                               <cp-tooltip :options="{ content: 'Every effort is made to calculate timezone based on your browser settings. When this information is unavailable, the selected timezone will be used for calculations.'}"></cp-tooltip><div class="input-class">
                              <select v-model="settings.timezone" @change="saveTimezoneSettings()" >
                                <option v-for="option in optionsTime" v-bind:value="option.value">
                                {{ option.text }}
                                </option>
                              </select>
                            </div>
                        </div>
                        <div class="line-wrapper">
                            <label>Landing Page</label>
                            <div class="input-class">
                              <select v-model="general_settings.landing_page.value">
                                <option v-for="option in options" v-bind:value="option.value">
                                  {{ option.text }}
                                </option>
                              </select>
                              {{ hostName }}/{{ general_settings.landing_page.value }}
                            </div>
                        </div>
                        <br />
                        <br />
                        <div class="line-wrapper">
                            <label>Hex Background Color </label>
                            <div class="input-class">
                                <input class="input-class jscolor" type="text" v-model="general_settings.hex_color.value"  @input="addEvent" @change="addEvent" />
                            </div>
                        </div>
                    </div>
                    <div class="cp-right-col">
                      <div>
                          <h4>Hide/Show links:</h4>
                          <hr />
                          <div class="line-wrapper">
                              <label>Google Store</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.google_store_url.show">
                          </div>
                          <div class="line-wrapper">
                              <label>iOS Store</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.ios_store_url.show">
                          </div>
                          <div class="line-wrapper">
                              <label>Social Media Links</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.social_media_link.show">
                          </div>
                          <h4>Footer Links and Information:</h4>
                          <hr />
                          <div class="line-wrapper">
                              <label>Address</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.address.show">
                          </div>
                          <div class="line-wrapper">
                              <label>Phone</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.phone.show">
                          </div>
                          <div class="line-wrapper">
                              <label>Terms &amp; Conditions</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.terms.show">
                          </div>
                          <div class="line-wrapper">
                              <label>Return Policy</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.return_policy.show">
                          </div>
                          <!-- commented until we fix about us so we can bring it back -->
                          <!-- <div class="line-wrapper">
                              <label>About Us</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.about_us.show">
                          </div> -->
                          <h4> Third Party Chat Integration:</h4>
                          <hr />
                          <div class="line-wrapper">
                              <label>Use Olark Chat</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.olark_chat_integration.show">
                          </div>
                          <div class="line-wrapper">
                              <label>Use Tawk.To Chat</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_settings.tawk_chat_integration.show">
                          </div>
                          <div class="line-wrapper" v-if='general_settings.olark_chat_integration.show'>
                              <label>Add Olark Chat Code</label>
                              <textarea class="textarea" rows="8" cols="3" type="text" @change="addCustom()" v-model="general_settings.olark_chat_integration.value"></textarea>
                          </div>
                          <div class="line-wrapper" v-if='general_settings.tawk_chat_integration.show'>
                              <label>Add Tawk.To Chat Code</label>
                              <textarea class="textarea" rows="8" cols="3" type="text" v-model="general_settings.tawk_chat_integration.value"></textarea>
                          </div>
                      </div>
                    </div>
                    <div class="save-settings-button">
                        <input class="cp-button-standard" type="button" @click="saveSettings()" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const Settings = require('../../../resources/settings.js')
const Users = require('../../../resources/users.js')
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      Auth,
      general_settings: {
        auto_transfer_orders: {},
        shipping_team_id: {},
        corp_coupons: {},
        company_name: {},
        company_email: {},
        corp_youtube: {},
        address: {},
        phone: {},
        from_email: {},
        order_notification_email: {},
        use_built_in_store: {},
        store_builder_admin: {},
        store_builder_reseller: {},
        edit_join_date: {},
        about_us: {},
        return_policy: {},
        terms: {},
        landing_page: {},
        rep_facebook_login: {},
        title_announcement: {},
        google_store_url: {},
        ios_store_url: {},
        title_store: {},
        reseller_coupons: {},
        social_media_link: {},
        ein: {},
        olark_chat_integration: {},
        tawk_chat_integration: {},
        payquicker: {},
        direct_deposit: {},
        einvoice_expire_time: {},
        enable_shipping_label_creation: {},
        hex_color:{},
        self_pickup_wholesale: {},
        self_pickup_reseller: {},
      },
      olark_chat: false,
      mask: {
        default () {
          return '##-#######'
        }
      },
      settings: {
        timezone: ''
      },
      options: [
        { text: 'Home', value: '' },
        { text: 'Login', value: 'login' },
        { text: 'Store', value: 'store' }
      ],
      optionsTime: [
        { text: 'Universal Time Coordinated', value: 'UTC' },
        { text: 'Eastern', value: 'America/New_York' },
        { text: 'Central', value: 'America/Chicago' },
        { text: 'Mountain', value: 'America/Denver' },
        { text: 'Arizona', value: 'America/Phoenix' },
        { text: 'Pacific', value: 'America/Los_Angeles' },
        { text: 'Alaska', value: 'America/Anchorage' },
        { text: 'Hawaii', value: 'Pacific/Honolulu' }
      ]
    }
  },
  computed: {
    hostName () {
      return window.location.host
    }
  },
  mounted () {
    this.getGeneralSettings()
    this.getUserTimezone();
    var color = new jscolor($('body').find('input.jscolor')[0]);

  },
  methods: {
    getGeneralSettings: function () {
      Settings.getAllSettings()
        .then((response) => {
          this.general_settings = response
        })
    },
    saveSettings: function () {
      if (!parseInt(this.general_settings.einvoice_expire_time.value)) {
        return this.$toast('Invoice time is an invalid number', {error: true})
      }
      Settings
        .update(this.general_settings)
        .then((response) => {
          if (!response.error) {
            this.$updateGlobal(this.general_settings)
            this.$toast('General settings saved succesfully.')
          }
        })
    },
    saveTimezoneSettings: function () {
      Users.updateUserSettings(this.settings)
        .then((response) => {
          this.$toast('Timezone settings saved successfully.')
        })
    },
    getUserTimezone: function () {
      Users.userSettings(1)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          } else {
            this.settings.timezone = response.timezone
          }
        })
    },
    addCustom: function () {
      var code = this.general_settings.olark_chat_integration.value
      var length = code.length
      var index = length - 68
      if (length > 68) {
        var newCode = code.substr(0, index) + "olark.configure('system.hb_primary_color', '#4A4A4A');   " + code.substr(index)
        this.general_settings.olark_chat_integration.value = newCode
      }
    },
    addEvent ({ type, target }) {
        this.general_settings.hex_color.value = target.value;
        $("nav.main-menu-nav-scope").attr("style", "background-color: "+'#'+target.value+" !important;");
    }
  },
  components: {
    'CpInputMask': require('../../../cp-components-common/inputs/CpInputMask.vue'),
    'CpSettingsTimezone': require('../../settings/partials/CpSettingsTimezone.vue')
  }
}
</script>

<style lang="scss">
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
            span {
              width: 50% !important;
              input {
                width: 100% !important;
              }
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
