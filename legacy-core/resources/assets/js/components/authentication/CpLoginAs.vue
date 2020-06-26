<!--
  This component isn't being used in the SPA (or shouldn't be, at least)
-->

<template lang="html">
  <button class="logout-as list-group-item" @click="loginAs()">
          <i class="mdi mdi-chevron-left"></i>
          Return to Admin</button>
</template>

<script>
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
    }
  },
  mounted: function () {
  },
  methods: {
    loginAs () {
      Auth.loginAs(Auth.getClaims().actualUserId)
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message
            return this.$toast(response.message, { error: true, dismiss: false })
          }
          Jwt.setToken(response.jwtToken)
          window.location = '/dashboard'
        })
    }
  },
  props: ['session'],
  components: {}
}
</script>

<style lang="sass">
</style>
