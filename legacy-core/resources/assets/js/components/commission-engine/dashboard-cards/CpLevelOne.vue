<template>
    <div class="CpLevelOneMentors">
        <h5>{{title}}</h5>
          <h3 v-if="!loading && mydownranksumlvl1[0]">{{mydownranksumlvl1[0].total}}</h3>
    </div>
</template>

<script id="level-one-mentors">
const commission = require('../../../resources/CommissionEngineAPIv0.js')

module.exports = {
  data () {
    return {
      loading: false,
      mydownranksumlvl1: {}
    }
  },
  props: ['title', 'userid', 'batchid'],
  mounted () {
    this.myDownRank()
  },
  methods: {
    myDownRank () {
      this.loading = true
      commission.runCommand({
        command: 'mydownranksumlvl1',
        systemid: '1',
        userid: this.userid.toString(),
        batchid: this.batchid,
        orderdir: 'asc',
        offset: '0',
        limit: '10',
        orderby: 'id'
      }).then(response => {
        this.loading = false
        this.mydownranksumlvl1 = response.ranksumlvl1
      })
    }
  },
  watch: {
    batchid () {
      this.myDownRank()
    }
  }
}
</script>

<style lang="scss">

</style>
