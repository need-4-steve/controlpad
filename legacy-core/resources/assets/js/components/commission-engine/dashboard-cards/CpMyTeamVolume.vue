<template>
    <div class="CpMyTeamVolume">
        <h5>{{title}}</h5>
        <h3>{{parseFloat(myStatsLevelOne.mywholesalesales) + parseFloat(myStats.teamwholesalesales) | currency}}</h3>
    </div>
</template>
 <script id="my-team-volume">
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
