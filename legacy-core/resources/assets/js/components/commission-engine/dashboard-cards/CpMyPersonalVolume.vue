<template>
    <div class="CpMyDashboardVolume">
        <h5>{{title}}</h5>
            <h3>{{myStatsLevelOne.mywholesalesales | currency}}</h3>
    </div>
</template>
 <script id="my-personal-volume">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['myStatsLevelOne', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
