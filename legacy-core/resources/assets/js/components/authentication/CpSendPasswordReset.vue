<template lang="html">
    <div class="cp-password-reset-wrapper">
      <div class="cp-form-inverse">
        <cp-input v-model="request.email" placeholder="Enter your email here..."></cp-input>
        <button style="width:100%;" class="cp-button-standard " type="button" name="button" @click="sendResetLink()">Send Reset Password Link</button>
      </div>
    </div>
</template>

<script>
const Auth = require('auth')

module.exports = {
  data () {
    return {
      request: {
        email: ''
      }
    }
  },
  mounted () {
  },
  methods: {
    sendResetLink () {
      Auth.sendResetLink(this.request)
        .then((response) => {
          if (!response.error) {
            this.request.email = ''
            this.$toast(response.message, { dismiss: true })
          }
        })
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

  .cp-password-reset-wrapper {
    display: flex;
    justify-content: center;
    margin: 100px 0px;
    .cp-form-inverse {
      background-color: $cp-grey;
      padding: 30px 20px;
      border-radius: 3px;
      input {
        text-align: center;
      }
    }
  }
</style>
