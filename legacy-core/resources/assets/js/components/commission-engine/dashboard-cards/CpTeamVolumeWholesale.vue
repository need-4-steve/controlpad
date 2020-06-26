<template>
    <div class="">
        <h5>{{title}}</h5>
          <h3>{{myStats.teamwholesalesales | currency}}</h3>
    </div>
</template>
 <script id="team-volume-wholesale">
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
