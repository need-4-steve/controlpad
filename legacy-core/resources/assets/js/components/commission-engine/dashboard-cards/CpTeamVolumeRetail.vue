<template>
    <div class="">
        <h5>{{title}}</h5>
          <h3>{{myStats.groupretailsales | currency}}</h3>
    </div>
</template>
 <script id="team-volume-retail">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['myStats', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
