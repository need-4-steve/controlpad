<template lang="html">
    <div class="settings-wrapper">
        <cp-settings-general></cp-settings-general>
        <cp-settings-branding></cp-settings-branding>
        <cp-settings-general-store></cp-settings-general-store>
        <cp-settings-inventory></cp-settings-inventory>
        <cp-settings-rep-settings></cp-settings-rep-settings>
        <cp-settings-autoship></cp-settings-autoship>
        <cp-settings-registration-settings></cp-settings-registration-settings>
        <cp-settings-events></cp-settings-events>
        <cp-settings-taxes></cp-settings-taxes>
        <cp-settings-commission></cp-settings-commission>
        <cp-settings-user-status></cp-settings-user-status>
        <cp-settings-order></cp-settings-order>
        <div class="cp-box-standard">
          <div class="cp-box-heading" @click="showSetting = !showSetting">
            <h5>Documents</h5>
          </div>
          <div class="cp-box-body table-setting">
            <cp-custom-pages-index></cp-custom-pages-index>
          </div>
       </div>
       <cp-custom-emails></cp-custom-emails>
    </div>
</template>

<script>
module.exports = {
  data: function () {
    return {
    }
  },
  components: {
    'CpSettingsBranding': require('../settings/partials/CpSettingsBranding.vue'),
    'CpSettingsRepSettings': require('../settings/partials/CpSettingsRepSettings.vue'),
    'CpSettingsInventory' : require('../settings/partials/CpSettingsInventory.vue'),
    'CpSettingsEmail': require('../settings/partials/CpSettingsEmail.vue'),
    'CpSettingsBackOffice': require('../settings/partials/CpSettingsBackOffice.vue'),
    'CpCustomPagesIndex': require('../custom-pages/CpCustomPagesIndex.vue'),
    'CpSettingsRegistrationSettings': require('../settings/partials/CpSettingsRegistrationSettings.vue'),
    'CpSettingsTaxes': require('../settings/partials/CpSettingsTaxes.vue'),
    'CpSettingsGeneralStore': require('../settings/partials/CpSettingsGeneralStore.vue'),
    'CpSettingsGeneral': require('../settings/partials/CpSettingsGeneral.vue'),
    'CpSettingsCommission': require('../settings/partials/CpSettingsCommission.vue'),
    'CpSettingsOrder': require('../settings/partials/CpSettingsOrder.vue'),
    'CpSettingsUserStatus': require('../settings/partials/CpSettingsUserStatus.vue'),
    'CpCustomEmails': require('../settings/partials/CpCustomEmails.vue'),
    CpSettingsEvents: require('../settings/partials/CpSettingsEvents.vue')
  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
        .settings-wrapper {
            hr {
              margin-top: 5px;
              margin-bottom: 10px;
            }
            .cp-box-body {
                max-height: 2000px;
                transition: max-height .2s ease-in-out;
                border: 0px;
                &.table-setting {
                    padding: 0px;
                    border-top: 0px;
                    th {
                        background-color: $cp-lightGrey;
                        color: black;
                    }
                }
            }
            .save-settings-button {
              display: block;
              width: 100%;
              float: right;
              text-align: right;
              margin-top: 20px;
            }
        }
</style>
