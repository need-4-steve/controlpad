<template>
    <div class="CpRankNameLegs">
        <h5>{{title}}</h5>
        <div>
          <div>
            <h3 >Gen 1</h3>
            <h3>{{ gen1.total}}</h3>
          </div>
          <div>
            <h3 >Gen 2</h3>
            <h3>{{ gen2.total}}</h3>
          </div>
          <div>
            <h3 >Gen 3</h3>
            <h3>{{ gen3.total}}</h3>
          </div>
        </div>
    </div>
</template>

<script id="rank-name-legs">
const commission = require('../../../resources/CommissionEngineAPIv0.js')

module.exports = {
  data () {
    return {
      gen1: { total: 0},
      gen2: { total: 0},
      gen3: { total: 0}
    }
  },
  props: ['title', 'userid', 'batchid'],
  mounted () {
    this.getMyDownRankSum()
  },
  methods: {
    getMyDownRankSum () {
      commission.runCommand({
        command: 'mydownranksum',
        systemid: '1',
        userid: this.userid.toString(),
        orderdir: 'asc',
        offset: '0',
        limit: '10',
        orderby: 'id',
        batchid: this.batchid,
        generation: '1'
      }).then(response => {
        if (response.ranksum) {
          if (response.ranksum['6']) {
            this.gen1 = response.ranksum['6']
          }
        }
      })
      commission.runCommand({
        command: 'mydownranksum',
        systemid: '1',
        userid: this.userid.toString(),
        orderdir: 'asc',
        offset: '0',
        limit: '10',
        orderby: 'id',
        batchid: this.batchid,
        generation: '2'
      }).then(response => {
        if (response.ranksum) {
          if (response.ranksum['6']) {
            this.gen2 = response.ranksum['6']
          }
        }
      })
      commission.runCommand({
        command: 'mydownranksum',
        systemid: '1',
        userid: this.userid.toString(),
        orderdir: 'asc',
        offset: '0',
        limit: '10',
        orderby: 'id',
        batchid: this.batchid,
        generation: '3'
      }).then(response => {
        if (response.ranksum) {
          if (response.ranksum['6']) {
            this.gen3 = response.ranksum['6']
          }
        }
      })
    }
  },
  watch: {
    batchid () {
      this.getMyDownRankSum()
    }
  }
}
</script>

<style lang="scss">

</style>
