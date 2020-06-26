<template>
    <div class="autoship-wrapper" v-if="!loading">
        <div class="cp-box-standard">
            <div class="cp-box-heading">
                <h5>Autoship Settings</h5>
            </div>
            <div class="cp-box-body cp-form-standard">
                <div class="columns">
                    <div class="col">
                      <!--<cp-toggle label="Autoship Enabled" v-model="autoship_settings.autoship_enabled.show"></cp-toggle>-->
                      <cp-input label="Autoship Title" v-model="autoship_settings.autoship_display_name.value"></cp-input>
                      <cp-input label="Create Autoship Label" v-model="autoship_settings.autoship_purchase_label.value"></cp-input>
                      <cp-toggle label="Default Purchasing to Autoship" v-model="autoship_settings.autoship_default_purchase.value"></cp-toggle>
                      <!--<cp-toggle label="Reminder Email" v-model="autoship_settings.autoship_reminder.show"></cp-toggle>
                      <cp-input label="Days before payment" v-if="autoship_settings.autoship_reminder.show" type="number" v-model.number="autoship_settings.autoship_reminder.value"></cp-input>-->
                    </div>
                </div>
            </div>
            <div class="save-settings-button">
                <button class="cp-button-standard" @click="saveAutoshipSettings()">Save</button>
            </div>
        </div>
    </div>
</template>
<script>
const Settings = require('../../../resources/settings.js')

module.exports = {
  data: function () {
    return {
      autoship_settings: {
      },
      loading: true
    }
  },
  mounted () {
    this.getAutoshipSettings()
  },
  methods: {
    getAutoshipSettings () {
      Settings.getSettingCategory('auto_ship')
        .then((response) => {
          this.loading = false
          this.autoship_settings = response
        })
    },
    saveAutoshipSettings () {
      Settings.update(this.autoship_settings)
        .then((response) => {
          if (!response.error) {
            this.$updateGlobal(this.autoship_settings)
            this.$toast('Autoship Settings Saved successfully', {dismiss: false})
          }
        })
    }
  }
}
</script>
<style lang="scss">
.autoship-wrapper{
    margin-bottom: 70px;
}
</style>
