<template>
    <div class="CpSiteSales">
        <h5>{{title}}</h5>
    <h3>{{myRecieptSum | currency}}</h3>
    </div>
</template>

<script id="site-sales">
const commission = require('../../../resources/CommissionEngineAPIv0.js')

module.exports = {
  data () {
    return {
      myRecieptSum: {}
    }
  },
  props: ['title', 'userid', 'batchid', 'batches'],
  mounted () {
    this.getMyRecieptSum()
  },
  methods: {
    getMyRecieptSum () {
      let batch = this.batches.find(x => x.id === this.batchid)
      commission.runCommand({
        command: 'myreceiptsum',
        systemid: '1',
        userid: this.userid.toString(),
        orderdir: 'asc',
        offset: '0',
        limit: '10',
        orderby: 'id',
        invtype: '5',
        startdate: batch.startdate,
        enddate: batch.enddate,
        generation: '1'
      }).then(response => {
        this.myRecieptSum = response.receiptsum
      })
    }
  },
  watch: {
    batchid () {
      this.getMyRecieptSum()
    }
  }
}
</script>

<style lang="scss">

</style>
