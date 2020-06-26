<template>
    <div class="CpCareerTitle">
        <h5>{{title}}</h5>
          <h3>{{ evFullRetail | currency }}</h3>
    </div>
</template>
 <script id="enterprise-volume-retail">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['myStatsLevelOne', 'myStats', 'title'],
  mounted () {
  },
  methods: {
  },
  computed: {
    evFullRetail () {
      if (this.myStatsLevelOne && this.myStats) {
        return parseFloat(this.myStatsLevelOne.myretailsales) + parseFloat(this.myStats.groupretailsales)
      }
      return 0.00
    }
  }
}
</script>
 <style lang="scss">
 </style>
