<template>
    <div class="CpRingbombEnterpriseVolume">
        <h5>{{title}}</h5>
        <h3>{{myStats.groupwholesalesales | currency}}</h3>
    </div>
</template>
 <script id="ringbomb-enterprise-volume">
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
