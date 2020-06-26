<template lang="html">
  <cp-rep-search-typeahead @rep-selected="loginAs" label="Sign in as: "></cp-rep-search-typeahead>
</template>

<script>
const Auth = require('auth')

module.exports = {
  methods: {
    loginAs (rep) {
      Auth.loginAs(rep.id).then(res => {
        if (res.error) {
          this.errorMessages = res.message
          return this.$toast(res.message, { error: true, dismiss: false })
        }
        this.$events.$emit('login-as-change')
        this.$router.go()
      })
    }
  },
  components: {
    CpRepSearchTypeahead: require('../../cp-components-common/inputs/CpRepSearchTypeahead.vue')
  }
}
</script>
