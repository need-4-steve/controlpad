<template lang="html">
    <div class="cp-password-reset-wrapper">
      <div class="cp-form-inverse">
        <cp-input v-model="request.email" type="email" placeholder="E-Mail Address"></cp-input>
        <cp-input v-model="request.password" type="password" placeholder="New Password"></cp-input>
        <cp-input v-model="request.password_confirmation" type="password" placeholder="Verify Password"></cp-input>
        <button style="width:100%;" class="cp-button-standard " type="button" name="button" @click="resetPassword()">Create New Password</button>
      </div>
    </div>
</template>

<script>
const Auth = require('auth')

module.exports = {
  data () {
    return {
      email: false,
      request: {
        token: this.token
      }
    }
  },
  props: {
    token: {
      default () {
        return this.$pathParameterName()
      }
    }
  },
  mounted () {
  },
  methods: {
    resetPassword () {
      Auth.resetPassword(this.request)
        .then((response) => {
          if (!response.error) {
            this.$toast(response.message, { dismiss: true })
            window.location = '/dashboard'
          } else {
            this.$toast(response.message['password'][0], { dismiss: true, error: true })
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
      .black-header {
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
  }
</style>
