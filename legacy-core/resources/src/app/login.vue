<template>
  <div layout="public-layout-wrapper-login">
    <div layout="public-layout-main-content-login">
    <span v-if="env">{{env}} Server</span>
      <section class="ankit">
          <span class="logo"><img :src="$getGlobal('back_office_logo').value" /></span>
          <input type="text" placeholder="Email Address" v-model="authRequest.email" @keyup.enter="authenticate()">
          <input type="password" placeholder="Password" v-model="authRequest.password" @keyup.enter="authenticate()">
		  <button class="cp-button-standard" @click="authenticate()">Sign in</button>
          <a href="/password-reset-send">Forgot Password?</a>
      </section>
    </div>
  </div>
</template>
<script id="CpLoginLayout">
  const Auth = require('auth')
  const querystring = require('querystring')
  const env = require('env')

  module.exports = {
    routing: [
      { name: 'login', path: '/login', meta: { noauth: true } },
      { name: 'auth', path: '/auth', meta: { noauth: true, nosubscription: true } }
    ],
    data () {
      return {
        env: env.name === 'Production' ? '' : env.name,
        logout: this.$route.query.logout !== undefined,
        returnPath: querystring.get('return'),
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
    props: ['facebookOauthUrl'],
    mounted () {
      if (this.logout) {
        Auth.logout()
      } else if (Auth.isLoggedIn()) {
        var redirect = window.cpHelpers.getParameterByName('redirect')
        if (redirect) {
          Auth.redirect({ url: redirect })
        } else {
          this.$router.push('/dashboard')
        }
      }
    },
    methods: {
      authenticate () {
        var redirect = window.cpHelpers.getParameterByName('redirect')
        this.authRequest.redirect = redirect
        Auth.login(this.authRequest)
          .then((res) => {
            let path = '/'
            if (this.returnPath) {
              path = this.returnPath
            } else if ((Auth.hasAnyRole('Rep') && Auth.getClaims().sellerType === 'Affiliate' && !this.$getGlobal('affiliate_custom_order').show) ||
                       (!Auth.hasAnyRole('Admin', 'Superadmin') && (!res.activeSubscription || !res.termsAccepted))) {
              // If an affiliate can't make orders then we want to just send them to settings
              // Users who aren't set up should go to settings
              path = '/my-settings'
            }

            this.$router.push(path)
          }).catch((res) => {
            const err = res.body
            return this.$toast(err.message, { error: true, dismiss: false })
          })
      }
    }
}
</script>
<style lang="scss">
 html,body,#vue-app{
    height:100%;
    padding:0;
    margin:0;
    overflow:hidden;
    -webkit-overflow-scrolling: touch;
  }
  [layout="public-layout-wrapper-login"]{
    height: 100%;
    background: $cp-main;  /* fallback for old browsers */
    background: -webkit-linear-gradient(to right, $cp-main-light, $cp-main);  /* Chrome 10-25, Safari 5.1-6 */
    background: linear-gradient(to right, $cp-main-light, $cp-main); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
      [layout="public-layout-main-content-login"]{
        height:100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: auto;
          span {
            color: white;
            font-weight: bold;
            font-size: 24px;
            align-self: center;
            margin-top: 20px;
            text-transform: uppercase;
          }
          section{
            background: $cp-lighterGrey;
            border: solid 1px $cp-lightGrey;
            padding: 25px 35px;
            width: 95%;
            max-width: 300px;
            text-align: center;
            align-self: center;
              .logo{
                img{
                  max-height: 60px;
                  max-width: 250px;
                  margin-bottom: 25px;
                }
              }
            input{
              max-width: 100%;
              border-radius: 0;
              border: none;
              height: 40px;
              width: 100%;
              text-indent: 10px;
              margin: 10px auto;
              font-size: 16px;
            }
            button{
              margin: 10px 10px 0 0;
              width:100%;
              height: 40px;
              color: #fff;
              font-size: 16px;
            }
            a,a:active,a:visited{
              display: inline-block;
              margin: 16px 0;
              color: $cp-main;
            }
          }
    }
  }

</style>
