<template>
  <v-cp-error404>
    <h1>404</h1>
    <p>You look lost. Would you like to go <router-link to="/">home</router-link>?</p>
  </v-cp-error404>
</template>
<script id="CpError404">
module.exports = {
  routing: {
    path: '*',
    meta: {
      noauth: true
    }
  }
}
</script>
<style lang="scss">
  v-cp-error404{
    display:block;
    padding:1em;
    a,a:hover{
      text-decoration:underline;
    }
  }
</style>
