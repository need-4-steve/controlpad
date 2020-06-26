<template>
  <div layout="public-layout-wrapper">
    <header>
      <img :src="$getGlobal('back_office_logo').value" :alt="$getGlobal('company_name').value" />
      <span v-if="env">{{env}} Server</span>
    </header>
    <div layout="public-layout-main-content">
      <main>
        <router-view :key="$route.fullPath"></router-view>
      </main>
      <footer>
        <div v-if="$getGlobal('return_policy').show"><a :href="$getGlobal('return_policy').value">Return Policy</a></div>
        <div v-if="$getGlobal('terms').show"><a href="/terms-conditions/rep">Terms &amp; Conditions</a></div>
        <div>&copy; {{ year }} - {{$getGlobal('company_name').value}}</div>
        <div v-if="$getGlobal('address').show">{{$getGlobal('address').value}}</div>
        <div v-if="$getGlobal('phone').show">{{$getGlobal('phone').value}}</div>
      </footer>
    </div>
  </div>
</template>
<script id="CpPublicLayout">
const moment = require('moment')
const env = require('env')
module.exports = {
  routing: {
    name: 'public',
    path: '/public'
  },
  data () {
    return {
      env: env.name === 'Production' ? '' : env.name,
      year: moment().format('YYYY')
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
  [layout="public-layout-wrapper"]{
    display: flex;
    flex-direction: column;
    height: 100%;

    header{
      background: $cp-lighterGrey;
      border: solid 0 $cp-lightGrey;
      border-bottom-width: 1px;
      box-shadow: none;
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: center;
      img,span{
        margin: 0 15px;
      }
      img{
        max-height: 55px;
        max-width: 300px;
      }
      span{
        color: red;
        font-weight: bold;
        font-size: 18px;
        text-transform: uppercase;
        transform: rotate(-5deg);
      }
    }
    [layout="public-layout-main-content"]{
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: auto;
      main{
        flex: 1;
        align-items: center;
        justify-content: center;
      }
      footer{
        border-top-width: 1px;
        text-align:center;
        padding: 15px;
      }
    }
  }
</style>
