<template lang="html">
    <div class="oauth-wrapper">
        <div class="cp-box-standard">
            <div class="cp-box-heading">
                <h5>Social Integrations</h5>
            </div>
            <div class="cp-box-body">
                <div class="line-wrapper">
                    <h5>Facebook <span v-show="oauths.facebook.email">({{ oauths.facebook.email }})</span></h5>
                    <a class="cp-button-link" v-show="!oauths.facebook.email" v-bind:href="oauths.facebook.buttonUrl">Link Your Account</a>
                    <button class="cp-button-link" v-show="oauths.facebook.email" @click="disconnectFromFacebook()"> Disconnect</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script type="text/javascript">
const SocialMedia = require('../../../resources/social-media.js')

module.exports = {
  data: function () {
    return {
      oauthUrls: {},
      oauths: {
        facebook: {
          email: '',
          buttonUrl: ''
        }
      }
    }
  },
  props: {
    oauthTokens: {
      type: Array,
      required: true
    }
  },
  mounted () {
  },
  methods: {
    getUrl () {
      SocialMedia.connectFacebook().then((response) => {
        this.oauths.facebook.buttonUrl = response.facebook
      })
    },
    disconnectFromFacebook: function () {
      SocialMedia.disconnectFacebook()
        .then((response) => {
          if (response.error === false) {
            this.oauths.facebook.email = ''
          }
        })
    }
  }
}
</script>
<style lang="sass">
</style>
