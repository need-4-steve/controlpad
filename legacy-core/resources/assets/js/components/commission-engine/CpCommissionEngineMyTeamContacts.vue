<template>
    <div class="CpCommissionEngineMyTeamContactsWrapper">
      <cp-data-table
            :table-data="team"
            :table-columns="tableColumns"
            :pagination="pagination"
            :recall-data="getTeam"
            :request-params="indexRequest"
            :options="{
                searchBox: false,
                tableControls: true,
                datePicker: false,
            }">
              <span slot="designer" slot-scope="{row}">{{row.ufirstname}} {{row.ulastname}} ({{row.userid}})</span>
              <span slot="advisor" slot-scope="{row}">{{row.afirstname}}  {{row.alastname}} ({{row.auserid}})</span>
              <template slot="udatelastearned" slot-scope="{row}">
                <span v-if="row.udatelastearned === ''" >N/A</span>
                <span v-else>{{row.udatelastearned}}</span>
              </template>
            </cp-data-table>
             <div class="align-center">
                <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            </div>

    </div>
</template>
<script id="CpCommissionEngineMyTeamContacts">
const config = require('env').commissionBuild
const commission = require('../../resources/CommissionEngineAPIv0.js')
const cePaginate = require('cepaginate')
const Auth = require('auth')

module.exports = {
  routing: [{
    name: 'site.CpCommissionEngineMyTeamContacts',
    path: '/commission-engine/my-team-contacts',
    meta: {
      title: 'My Team Contacts'
    }
  }],
  data () {
    return {
      indexRequest: {
        order: 'DESC',
        column: 'id',
        per_page: 15,
        search_term: '',
        page: 1,
        command: 'mydownstatsfull',
        systemid: '1',
        orderdir: 'asc',
        offset: '0',
        orderby: 'id',
        userid: '',
        batchid: '',
        limit: '15'
      },
      userid: '',
      pagination: {
        current_page: 1
      },
      tableColumns: [
        { header: 'Designer', field: 'designer', sortable: false },
        { header: 'Advisor', field: 'advisor', sortable: false },
        { header: 'Level', field: 'level', sortable: true },
        { header: 'Career Title', field: 'carrertitle', sortable: false },
        { header: 'Date Last Earned', field: 'udatelastearned', filter: 'cpStandardDate', sortable: true },
        { header: 'Enrollment Date', field: 'usignupdate', filter: 'cpStandardDate', sortable: true },
        { header: 'Phone', field: 'ucell', filter: 'phone', sortable: true },
        { header: 'Email', field: 'uemail', sortable: true }
      ],
      team: [],
      loading: false
    }
  },
  mounted () {
    this.userid = Auth.getAuthId().toString()
    this.indexRequest.userid = this.userid
    // console.log(config.toString().localeCompare('MCOM')===0)
    if (config.toString().localeCompare('MCOM')!==0) {
      this.getBatch()
    } else {
      commission.mcommDownline(this.userid)
    }
  },
  methods: 
  {
    getBatch () {
      this.loading = true
      let request = {
        command: 'querybatches',
        systemid: '1',
        orderdir: 'desc',
        offset: '0',
        orderby: 'id',
        userid: this.userid,
        limit: '15'
      }
      commission.runCommand(request).then(response => {
        this.indexRequest.batchid = response.batches[0].id
        this.getTeam()
      })
    },
    getTeam () {
      if (this.indexRequest.current_page) {
        this.indexRequest.current_page = this.indexRequest.current_page.toString()
      }
      this.indexRequest.search = this.indexRequest.search_term
      this.loading = true
      this.indexRequest.orderby = this.indexRequest.column
      this.indexRequest.orderdir = this.indexRequest.order
      this.indexRequest.offset = this.indexRequest.limit * (this.pagination.current_page - 1)
      if (this.indexRequest.per_page) {
        this.indexRequest.limit = this.indexRequest.per_page.toString()
        this.indexRequest.per_page = this.indexRequest.per_page.toString()
      }
      if (this.indexRequest.page) {
        let num = this.indexRequest.page
        let offset = this.indexRequest.offset
        this.indexRequest.offset = offset.toString()
        this.indexRequest.page = num.toString()
      }
      commission.runCommand(this.indexRequest).then(response => {
        this.loading = false
        if (response.errors) {
          this.pagination.last_page = 0
          this.pagination.current_page = 0
          return this.$toast(response.errors.detail, { error: true, dismiss: false })
        }
        this.pagination.limit = this.indexRequest.limit
        this.pagination = cePaginate.paginate(this.pagination, response)
        this.team = response.userstatsfull
        response.total = response.count
      })
    }
  }
}
</script>

<style lang="scss">

</style>
