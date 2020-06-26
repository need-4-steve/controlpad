<template>
    <div class="CpCareerTitle">
        <h5>{{title}}</h5>
          <h3 v-if="mylegs.length == 0">None</h3>
          <h3 v-for="leg in mylegs">{{leg.ufirstname + ' ' + leg.ulastname + ' (' + leg.userid + '):'}} {{ leg.evretail | currency }}</h3>
    </div>
</template>
 <script id="my-legs">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['title', 'mylegs'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
