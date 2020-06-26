<template lang="html">
  <div class="email-wrapper">
    <div class="">
      <div class="panel item-list">
        <div class="panel-heading">
          <h2 class="panel-title align-center">Email Message Settings</h2>
        </div>
        <div class="panel-body">
          <div class="cp-accordion">
            <div class="cp-accordion-head" @click="closed = !closed">
              <span class="arrow">
                <i class="mdi mdi-chevron-down" v-if="closed"></i>
              </span>
              <span class="arrow">
                <i class="mdi mdi-chevron-up" v-if="!closed"></i>
              </span>
            </div>
            <div class="cp-accordion-body" :class="{closed:closed}">
              <div class="cp-accordion-body-wrapper">
                <div class="cp-left-col">
                  <h5>Email Addresses: </h5>
                  <div class="line-wrapper">
                    <label>From Email Address</label>
                    <input type="email" name="name" value="" v-model="settings.from_email">
                  </div>
                  <div class="line-wrapper">
                    <label>To Email Address</label>
                    <input type="email" name="name" value="" v-model="settings.to_email">
                  </div>
                  <div class="line-wrapper">
                    <label>Reply To Email Address</label>
                    <input type="email" name="name" value="" v-model="settings.reply_email">
                  </div>
                </div>
                <div class="cp-right-col">
                  <h5>Message Text:</h5>
                  <div class="line-wrapper">
                    <label>Order Confirmation Message</label>
                    <input type="text" name="name" value="" v-model="settings.order_message">
                  </div>
                  <div class="line-wrapper">
                    <label>New Rep Welcome Message</label>
                    <input type="text" name="name" value="" v-model="settings.rep_message">
                  </div>
                  <div class="save">
                    <input class="cp-button-standard" type="button" @click="saveSettings()" value="Save">
                  </div>
                </div>
              </div>
            </div>
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
      importRequest: {
        id: ''
      },
      settings: {
        from_email: '',
        to_email: '',
        reply_email: '',
        order_message: '',
        rep_message: ''
      }
    }
  },
  computed: {},
  ready () {},
  attached () {},
  methods: {
    saveSettings: function () {
      Settings
        .getEmail(this.settings)
        .then((response) => {
          if (response.error) {
            return this.$broadcast('errorMessage', response.message)
          }
          this.$updateGlobal(this.settings)
        })
    }
  },
  components: { }
}
</script>
<style lang="sass">
  .email-wrapper {
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
      input {
        height: 100%;
        width: 50%;
        height: 30px;
        text-indent: 10px;
        margin: 5px 0;
        &.toggle-switch {
          width: 55px;
        }
      }
    }
    .save {
      float: right;
      margin-top: 10px;
    }
  }
</style>
