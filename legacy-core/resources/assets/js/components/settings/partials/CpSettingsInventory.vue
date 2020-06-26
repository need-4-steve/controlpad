<template lang="html">
    <div class="branding-wrapper">
        <div class="cp-accordion">
            <div class="cp-accordion-head black-header">
                <h5>Inventory Settings</h5>
            </div>
            <div class="cp-accordion-body">
                <div class="cp-accordion-body-wrapper">
                    <div class="cp-left-col">
                        <div>
                            <h4>Inventory Options</h4>
                            <hr />
                            <div class="line-wrapper">
                                <label>Corp Inventory Low Quantity Alert</label>
                                <input class="toggle-switch" type="checkbox" v-model="inventory_settings.low_inventory_alert_corp.show">
                            </div>
                            <div class="line-wrapper" v-if="inventory_settings.low_inventory_alert_corp.show">
                              <label>Enter Alert Quantity</label>
                                <cp-input-mask
                                  custom-class="input-class"
                                  type="number"
                                  mask="#####"
                                  v-model="inventory_settings.low_inventory_alert_corp.value"></cp-input-mask>
                            </div>
                            <div class="line-wrapper">
                                <label>Rep Inventory Low Quantity Alert</label>
                                <input class="toggle-switch" type="checkbox" v-model="inventory_settings.low_inventory_alert_rep.show">
                            </div>
                            <div class="line-wrapper" v-if="inventory_settings.low_inventory_alert_rep.show">
                              <label>Enter Alert Quantity</label>
                                <cp-input-mask
                                  custom-class="input-class"
                                  type="number"
                                  mask="#####"
                                  v-model="inventory_settings.low_inventory_alert_rep.value"></cp-input-mask>
                            </div>
                        </div>
                    </div>
                    <div class="cp-right-col">
                    </div>
                    <div class="save-settings-button">
                        <input class="cp-button-standard" type="button" @click="saveInventorySettings()" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const Settings = require('../../../resources/settings.js')

module.exports = {
  data: function () {
    return {
      closed: true,
      inventory_settings: {
        low_inventory_alert_corp: {},
        low_inventory_alert_rep: {}
      }
    }
  },
  computed: {},
  mounted () {
    this.getInventorySettings()
  },
  methods: {
    getInventorySettings: function () {
      Settings.getInventorySettings()
              .then((response) => {
                this.inventory_settings = response
              })
    },
    saveInventorySettings: function () {
      Settings
        .update(this.inventory_settings)
        .then((response) => {
          this.$updateGlobal(this.inventory_settings)
          this.$toast('Inventory settings saved successfully.')
        })
    }
  },
  components: {}
}
</script>
