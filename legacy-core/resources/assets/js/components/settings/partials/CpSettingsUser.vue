<template lang="html">
    <div class="my-setting-wrapper">
      <div class="cp-box-standard" v-show="auth.hasAnyRole('Rep')">
        <div class="cp-box-heading">
          <h5>My Settings</h5>
        </div>
        <div class="cp-box-body">
          <div v-if="settings">
            <div class="cp-accordion" v-show="auth.hasAnyRole('Rep', 'Admin', 'Superadmin') && $getGlobal('replicated_site').show">
              <div class="cp-accordion-head" @click="locatorSettings = !locatorSettings"  v-show="$getGlobal('rep_locator_enable').show">
                <h5>Locator Settings</h5>
                <span v-if="locatorSettings" class="arrow"><i class="mdi mdi-chevron-down"></i></span>
                <span v-if="!locatorSettings" class="arrow"><i class="mdi mdi-chevron-left"></i></span>
              </div>
              <div class="cp-accordion-body" :class="{closed: locatorSettings === true}"  v-show="$getGlobal('rep_locator_enable').show">
                <div class="cp-accordion-body-wrapper">
                    <cp-toggle tooltip="Choose wether or not customers should be able to find you and your products through the store locator" label="Allow store to be located" v-model="settings.show_location"></cp-toggle>
                  </div>
                </div>
              </div>
            </div>
            <div class="cp-accordion" v-show="auth.hasAnyRole('Rep') && $getGlobal('replicated_site').show">
              <div class="cp-accordion-head" @click="storeSettings = !storeSettings">
                <h5>Store Settings ( Display Options )</h5>
                <span v-if="storeSettings" class="arrow"><i class="mdi mdi-chevron-down"></i></span>
                <span v-if="!storeSettings" class="arrow"><i class="mdi mdi-chevron-left"></i></span>
              </div>
              <div class="cp-accordion-body" :class="{closed: storeSettings === true}">
                <div class="cp-accordion-body-wrapper">
                    <cp-toggle tooltip="Use this to suspend selling in your store" label="Hide All Products" v-model="settings.hide_products"></cp-toggle>
                    <cp-toggle tooltip="If you are purchasing a product that is already in inventory, it will show/hide to what it already is set as." label="Automatically Show New Inventory in Store" v-model="settings.show_new_inventory"></cp-toggle>
                    <cp-toggle v-model="settings.show_address" label="Show Address"></cp-toggle>
                    <cp-toggle v-model="settings.show_email" label="Show Email"></cp-toggle>
                    <cp-toggle label="Show Phone" v-model="settings.show_phone"></cp-toggle>
                </div>
              </div>
            </div>
            <div class="cp-accordion" v-if="shippingSettingsAvailable">
              <div class="cp-accordion-head" @click="shippingSettings = !shippingSettings">
                <h5>Shipping Settings</h5>
                <span v-if="shippingSettings" class="arrow"><i class="mdi mdi-chevron-down"></i></span>
                <span v-if="!shippingSettings"class="arrow"><i class="mdi mdi-chevron-left"></i></span>
              </div>
              <div class="cp-accordion-body" :class="{closed: shippingSettings === true}">
                <div class="cp-accordion-body-wrapper">
                      <a href="/shipping/settings" class="action-btn shipping-link">Change Shipping Rates</a>
                </div>
              </div>
            </div>
            <cp-settings-timezone v-if="settings && user && user.id !== undefined" :old-settings="oldSettings" :user-id="user.id" v-model="settings.timezone"></cp-settings-timezone>
            <div class="cp-accordion-body-wrapper" v-if="showInvoiceSetting()">
              <cp-toggle label="Show Address on Invoices" v-model="settings.show_address_on_invoice"></cp-toggle>
            </div>
            <div class="button-wrapper">
              <button class="cp-button-standard"type="button" name="button" @click="updateSettings()">Save My Settings</button>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
const Users = require('../../../resources/users.js')
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      auth: Auth,
      storeSettings: true,
      shippingSettings: true,
      locatorSettings: true,
      settings: {
        show_new_inventory: 0
      }
    }
  },
  props: {
    oldSettings: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      required: true
    }
  },
  mounted () {
    this.settings = this.oldSettings
  },
  methods: {
    updateSettings () {
      // do not update payment_account
      if (this.settings.payment_account || this.settings.payment_account === 0) {
        delete this.settings.payment_account
      }
      this.settings.user_id = this.user.id
      Users.updateUserSettings(this.settings)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          } else {
            this.settings = response
            this.$toast('Setting successfully updated.', {error: false})
          }
        })
    },
    showInvoiceSetting () {
      switch (Auth.getClaims().sellerType) {
        case 'Affiliate':
          return this.$getGlobal('affiliate_custom_order').show || this.$getGlobal('affiliate_custom_corp').show
        case 'Reseller':
          return this.$getGlobal('reseller_custom_order').show || this.$getGlobal('reseller_custom_corp').show
        default:
          return false
      }
    }
  },
  computed: {
    shippingSettingsAvailable () {
      return (this.auth.hasAnyRole('Rep') && this.$getGlobal('replicated_site').show && (this.user.seller_type_id !== 1 || this.$getGlobal('affiliate_shipping_rates').show))
    }
  },
  components: {
    'CpSettingsTimezone': require('../../settings/partials/CpSettingsTimezone.vue')
  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .my-setting-wrapper {
      .cp-box-standard {
        .shipping-link {
          padding-left: 5px;
        }
        .cp-accordion-body-wrapper span {
          margin: 5px;
        }
        .toggle-switch {
          padding: 0px !important;
        }
        .close-modal {
          position: absolute;
          right: 10px;
          top: 6px;
          color: #fff;
          font-size: 20px;
          cursor: pointer;
        }

      }
    }
</style>
