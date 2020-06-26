<template>
    <div class="">
        <h5>{{title}}</h5>
          <h3>{{myStats.affiliateretailsales | currency}}</h3>
    </div>
</template>
 <script id="affiliate-sales">
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
