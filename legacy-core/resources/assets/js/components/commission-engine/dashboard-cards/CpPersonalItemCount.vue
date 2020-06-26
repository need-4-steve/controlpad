<template>
    <div class="">
        <h5>{{title}}</h5>
          <h3>{{myStats.itemcountretail}}</h3>
    </div>
</template>
 <script id="personal-item-count">
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
