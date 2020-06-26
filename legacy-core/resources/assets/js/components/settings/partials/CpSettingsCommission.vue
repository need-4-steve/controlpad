<template lang="html">
    <div class="branding-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
        <div class="cp-accordion">
            <div class="cp-accordion-head black-header">
                <h5>Commission Engine Settings</h5>
            </div>
            <div class="cp-accordion-body">
                <div class="cp-accordion-body-wrapper">
                    <div class="cp-left-col">
                        <div>
                            <h4>Commission Engine Integration</h4>
                            <hr />
                            <div class="line-wrapper">
                                <label>Api Key
                                </label>
                                <input class="input-class" type="text" v-model="env_settings.comm_api_key" disabled>
                            </div>
                            <div class="line-wrapper">
                                <label>Authorised Email
                                </label>
                                <input class="input-class" type="text" v-model="env_settings.comm_email" disabled>
                            </div>
                            <div class="line-wrapper">
                                <label>System Id
                                </label>
                                <input class="input-class" type="text" v-model="env_settings.comm_system_id" disabled>
                            </div>
                            <div class="line-wrapper">
                                <label>API URL
                                </label>
                                <input class="input-class" type="text" v-model="env_settings.comm_url" disabled>
                            </div>
                            <div class="line-wrapper">
                                <label>Use Commission Engine
                                </label>
                                <input class="toggle-switch" type="checkbox" v-model="commission_settings.use_commission_engine.value">
                            </div>
                            <div class="line-wrapper">
                                <label>Show Commission Engine Tab on Navigation
                                </label>
                                <input class="toggle-switch" type="checkbox" v-model="commission_settings.comm_engine_tab.show">
                            </div>
                            <div class="line-wrapper">
                                <span><label>Wholesale Discount Percent Difference  <cp-tooltip :options="{ content: 'When using a discount on a retail sale this setting will apply the percent difference to calulate the wholesale discount price.'}"></cp-tooltip></label></span>
                                <span><input step="1" class="input-class" type="number" v-model="commission_settings.discount_wholesale_percent"></span>
                            </div>
                            <div class="line-wrapper">
                                <label>Show Commission Engine Link
                                </label>
                                <input class="toggle-switch" type="checkbox" v-model="commission_settings.commission_engine_link.show">
                            </div>
                            <div class="line-wrapper" v-if="commission_settings.commission_engine_link.show">
                                <label>Commission Engine Link
                                </label>
                                <input class="input-class" type="text" v-model="commission_settings.commission_engine_link.value">
                            </div>
                            <div class="line-wrapper">
                                <label>Send Starter Kits to the Commission Engine
                                </label>
                                <input class="toggle-switch" type="checkbox" v-model="commission_settings.comm_engine_starter_kits.show">
                            </div>
                        </div>
                    </div>
                    <div class="cp-right-col">
                        <h4>Simple Commissions</h4>
                        <hr />
                        <div class="line-wrapper">
                            <label>Use Simple Commissions
                            </label>
                            <input class="toggle-switch" type="checkbox" v-model="commission_settings.simple_commissions.show">
                        </div>
                        <div class="line-wrapper" v-if="commission_settings.simple_commissions.show">
                            <label>Simple Commissions Percentage
                            </label>
                            <span><input step="1" class="input-class" type="number" v-model="commission_settings.simple_commissions.value"></span>
                        </div>
                    </div>
                    <div class="save-settings-button">
                        <input class="cp-button-standard" type="button" @click="saveCommSettings()" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const Settings = require('../../../resources/settings.js')
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      Auth,
      commission_settings: {
        use_commission_engine: {},
        commission_engine_link: {},
        simple_commissions: {},
        comm_engine_starter_kits: {},
        comm_engine_tab: {},
      },
      env_settings: {
        comm_api_key: {},
        comm_email: {},
        comm_system_id: {},
        comm_url: {}
      },
      loading: false
    }
  },
  computed: {},
  mounted () {
    this.getCommissionSettings()
  },
  methods: {
    getCommissionSettings: function () {
      Settings.getSettingCategory('commission_engine')
        .then((response) => {
          this.commission_settings = response
        })
      Settings.getCommissionSettings()
        .then((response) => {
          this.env_settings = response
        })
    },
    saveCommSettings: function () {
      this.loading = true
      Settings
        .update(this.commission_settings)
        .then((response) => {
          this.loading = false
          this.$updateGlobal(this.commission_settings)
          this.$toast('Commission settings saved succesfully.')
        })
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
        .loading {
            float: right;
            margin-top: 245px;
            padding-right: 5px;
        }
    }
</style>
