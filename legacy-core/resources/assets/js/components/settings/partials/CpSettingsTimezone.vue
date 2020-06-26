<template lang="html">
  <div class="timezone-wrapper">
    <div class="cp-accordion">
        <div class="cp-accordion-head" @click="timezoneSettings = !timezoneSettings">
            <h5>Timezone Settings
              <cp-tooltip :options="{ content: 'Every effort is made to calculate timezone based on your browser settings. When this information is unavailable, the selected timezone will be used for calculations.'}"></cp-tooltip>
            </h5>
            <span v-if="timezoneSettings"class="arrow"><i class="mdi mdi-chevron-down"></i></span>
            <span v-if="!timezoneSettings"class="arrow"><i class="mdi mdi-chevron-left"></i></span>
        </div>
        <div class="cp-accordion-body" :class="{closed: timezoneSettings === true}">
            <div class="cp-accordion-body-wrapper">
              <cp-select
              @input="$emit('input', settings.timezone)"
              label="Your Timezone"
              v-model="settings.timezone"
              :options="options"
              :key-value="{name: 'text' , value: 'value'}"
              ></cp-select>
            </div>
        </div>
    </div>
  </div>
</template>
<script>
const Users = require('../../../resources/users.js')

module.exports = {
  data: function () {
    return {
      timezoneSettings: true,
      options: [
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
  props: {
    oldSettings: {
      type: Object,
      required: true
    },
    userId: {
      type: Number,
      required: true
    }
  },
  computed: {
    settings: {
      get () {
        return JSON.parse(JSON.stringify(this.oldSettings))
      },
      set () {}
    }
  },
  methods: {
    saveTimezoneSettings () {
      this.settings.user_id = this.userId
      // do not update payment_account
      if (this.settings.payment_account || this.settings.payment_account === 0) {
        delete this.settings.payment_account
      }
      Users.updateUserSettings(this.settings)
        .then((response) => {
          if (!response.error) {
            this.$toast(`Timezone settings saved successfully.`)
          }
        })
    }
  }
}
</script>

<style lang="scss" scoped>
    @import "resources/assets/sass/var.scss";

    .button-wrapper {
      margin: 5px;
      display: flex;
      justify-content: flex-end;
    }
</style>
