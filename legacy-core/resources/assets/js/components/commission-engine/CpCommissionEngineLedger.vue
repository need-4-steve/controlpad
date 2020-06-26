<template>
    <div class="CpCommissionEngineLedgerWrapper">
    <cp-data-table
            :table-data="ledgerData"
            :table-columns="tableColumns"
            :pagination="pagination"
            :recall-data="getLedger"
            :request-params="indexRequest"
            :options="{
                searchBox: false,
                tableControls: true,
                datePicker: false,
            }">
             </cp-data-table>
    </div>
</template>

<script id="CpCommissionEngineLedger">
const moment = require('moment')
const commission = require('../../resources/CommissionEngineAPIv0.js')
const cePaginate = require('cepaginate')
const Auth = require('auth')

module.exports = {
  routing: [{
    name: 'site.CpCommissionEngineLedger',
    path: '/commission-engine/my-ledger',
    meta: {
      title: 'My Ledger'
    }
  }],
  data () {
    return {
      Auth: Auth,
      indexRequest: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'desc',
        column: 'createdat',
        limit: '15',
        per_page: 15,
        search_term: '',
        page: 1,
        current_page: 1,
        command: 'myledger',
        systemid: '1',
        orderdir: 'desc',
        offset: '0',
        orderby: 'createdat',
        userid: ''
      },
      dates: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD')
      },
      tableColumns: [
        { header: 'ID', field: 'id', sortable: true },
        { header: 'System ID', field: 'systemid', sortable: true },
        { header: 'Batch ID', field: 'batchid', sortable: true },
        { header: 'Ref ID', field: 'refid', sortable: false },
        { header: 'Ledger Type', field: 'ledgertype', sortable: true, filter: 'ceLedgerType' },
        { header: 'Amount', field: 'amount', sortable: true, filter: 'currency' },
        { header: 'Event Date', field: 'eventdate', sortable: false, filter: 'cpStandardDate' },
        { header: 'Created', field: 'createdat', sortable: true, filter: 'cpStandardDate' }
      ],
      pagination: {
        current_page: 1
      },
      ledgerData: []
    }
  },
  mounted () {
    this.indexRequest.userid = Auth.getAuthId().toString()
    this.getLedger()
  },
  methods: {
    getLedger () {
      this.indexRequest.orderdir = this.indexRequest.order
      this.indexRequest.offset = this.indexRequest.limit * (this.pagination.current_page - 1)
      this.indexRequest.limit = this.indexRequest.limit.toString()
      this.indexRequest.per_page = this.indexRequest.per_page.toString()
      this.indexRequest.page = this.indexRequest.page.toString()
      this.indexRequest.current_page = this.indexRequest.current_page.toString()
      commission.runCommand(this.indexRequest).then(response => {
        if (response.errors) {
          this.pagination.last_page = 0
          this.pagination.current_page = 0
          return this.$toast(response.errors.detail, { error: true, dismiss: false })
        }
        this.pagination.limit = this.indexRequest.limit
        this.pagination = cePaginate.paginate(this.pagination, response)
        this.ledgerData = response.ledger
        response.total = response.count
      })
    }
  }
}
</script>

<style lang="scss">
    .CpCommissionEngineLedgerWrapper {}
</style>
