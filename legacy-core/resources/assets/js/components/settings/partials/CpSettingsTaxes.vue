<template lang="html">
    <div class="branding-wrapper">
        <div class="cp-accordion">
            <div class="cp-accordion-head black-header">
                <h5>Tax Settings</h5>
            </div>
            <div class="cp-accordion-body">
                <div class="cp-accordion-body-wrapper">
                    <div class="cp-left-col">
                        <div>
                            <h4>Tax Options</h4>
                            <hr />
                            <div class="line-wrapper">
                                <label>Tax classes are required for product entries</label>
                                <input class="toggle-switch" type="checkbox" v-model="tax_settings.tax_classes_required.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Tax exempt when purchasing wholesale</label>
                                <input class="toggle-switch" type="checkbox" v-model="tax_settings.tax_exempt_wholesale.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Calculate Taxes on Orders</label>
                                <input class="toggle-switch" type="checkbox" v-model="tax_settings.tax_calculation.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Calculate Taxes on Subscriptions</label>
                                <input class="toggle-switch" type="checkbox" v-model="tax_settings.tax_subscription.show">
                            </div>
                        </div>
                    </div>
                    <div class="cp-right-col">
                    </div>
                    <div class="save-settings-button">
                        <input class="cp-button-standard" type="button" @click="saveTaxSettings()" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const Settings = require('../../../resources/settings.js')
const Dropzone = require('dropzone')

module.exports = {
  data: function () {
    return {
      closed: true,
      tax_settings: {
        tax_classes_required: {},
        tax_exempt_wholesale: {},
        tax_calculation: {},
        tax_subscription: {}
      }
    }
  },
  computed: {},
  mounted () {
    this.getTaxSettings()
  },
  methods: {
    getTaxSettings: function () {
      Settings.getTaxSettings()
              .then((response) => {
                this.tax_settings = response
              })
    },
    saveTaxSettings: function () {
      Settings
        .update(this.tax_settings)
        .then((response) => {
          this.$updateGlobal(this.tax_settings)
          this.$toast('Tax settings saved successfully.')
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
    }
</style>
