<template>
    <div class="">
        <h5>{{title}}</h5>
          <h3>{{myStatsLevelOne.myretailsales | currency}}</h3>
    </div>
</template>
 <script id="personal-volume-retail">
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
