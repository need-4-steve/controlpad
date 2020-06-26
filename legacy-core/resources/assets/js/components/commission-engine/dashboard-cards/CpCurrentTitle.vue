<template>
    <div class="CpCurrentTitle">
        <h5>{{title}}</h5>
        <h3>{{myTitle.currenttitle}}</h3>
    </div>
</template>
 <script id="current-title">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['myTitle', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
