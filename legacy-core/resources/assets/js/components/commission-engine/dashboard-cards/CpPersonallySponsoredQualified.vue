<template>
    <div class="CpPersonallySponsoredQualified">
        <h5>{{title}}</h5>
        <h3 v-if="myStatsLevelOne != null">{{ myStatsLevelOne.psqwholesale }}</h3>
        <h3 v-else> 0 </h3>
    </div>
</template>
 <script id="personally-sponsored-qualified">
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
