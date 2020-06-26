<template>
    <div class="CpEnterpriseVolume">
        <h5>{{title}}</h5>
        <h3>{{parseFloat(myStatsLevelOne.mywholesalesales) + parseFloat(myStats.groupwholesalesales) | currency}}</h3>
     </div>
</template>
 <script id="enterprise-volume">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['myStatsLevelOne', 'myStats', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
