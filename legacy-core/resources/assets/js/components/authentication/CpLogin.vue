<!--
  This login is not being used for the SPA (or shouldn't be, at least)
-->

<template lang="html">
  <div class="cp-wrapper">
    <div class="login-wrapper">
      <form @submit.prevent>
        <h2 class="no-top medium">Sign in</h2>
        <cp-input type="text" placeholder="Email Address" v-model="authRequest.email"></cp-input>
        <cp-input type="password" placeholder="Password" v-model="authRequest.password"></cp-input>
        <button class='cp-button-standard login-btn' @click="authenticate()">Sign in</button>
        <a class="cp-button-link login-btn fb-login" v-if="loginSettings.rep_facebook_login.show" :href="$getGlobal('facebook_oauth_url').value"><i class="mdi mdi-facebook"></i>Sign in with Facebook</a>
        <p class="password-forgot"><a href="/password/remind">Forgot Password?</a></p>
      </form>
    </div>
  </div>
</template>

<script>
const Auth = require('auth')

module.exports = {
  data () {
    return {
      authRequest: {
        email: '',
        password: '',
        cp_redirect: null
      },
      loginSettings: {
        rep_facebook_login: {
          show: false
        }
      }
    }
  },
  mounted () {
    this.getLoginSettings()
  },
  methods: {
    authenticate: function () {
      var redirect = window.cpHelpers.getParameterByName('cp_redirect')
      this.authRequest.cp_redirect = redirect
      Auth.login(this.authRequest).then((response) => {
        if (response.error) {
          const message = response.code === 403 ? 'Invalid credentials' : response.message[0]
          return this.$toast(message, { error: true })
        }
        Jwt.setToken(response.cp_token)
        if (redirect) {
          var token = response.cp_token
          redirect = decodeURIComponent(redirect)
          var tokenPrefix = redirect.includes('?') ? '&' : '?'
          var redirectUrl = redirect + tokenPrefix + 'cp_token=' + token
          window.location = redirectUrl
          return
        }
        const isAdmin = Auth.hasAnyRole('Admin', 'Superadmin')
        if (isAdmin || (response.activeSubscription && response.termsAccepted)) {
          window.location = '/dashboard'
          return
        } else {
          window.location = '/my-settings'
          return
        }
      })
    },
    getLoginSettings: function () {
      Auth.getLoginSettings()
          .then((response) => {
            if (response.error) {
              return this.$toast(response.message, {error: true})
            } else {
              this.loginSettings = response
            }
          })
    }
  }
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";

    .login-btn {
        height: 40px;
        width: 100%;
        color: #fff;
        float: left;
        margin: 10px 10px 0 0;
    }
    .fb-login {
        background: #3b5998;
        line-height: 2;
        box-sizing:border-box;
    }
    .fb-login:hover {
        background: #4264aa;
    }
    .fa-facebook {
        float: left;
        line-height: 2;
        font-size: 15px;
    }
</style>
