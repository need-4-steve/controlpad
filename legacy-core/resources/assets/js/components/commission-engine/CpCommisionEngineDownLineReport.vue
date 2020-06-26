<template>
    <div class='commission-engine-downline-report'>
      <div class="custom-table-navigation">
        <div class="user">
        <button v-show="isEmptyQuery" class="cp-button-standard" @click="goBack()"><i class="mdi mdi-chevron-left"></i></button>
        <h3> {{ currentUser }}'s Downline Report</h3>
        </div>
        <div class="navigation">
        <cp-select-2
        :options="batches"
        :key-value="{name: 'startdate', value: 'id'}"
        v-model="indexRequest.batchid"></cp-select-2><button class="cp-button-standard" @click="getDownlineReport()"><i class="mdi mdi-refresh"></i></button>
        </div>
      </div>
        <cp-data-table
        :table-columns="tableColumns"
        :table-data="downlineReport"
        :recall-data="getDownlineReport"
        :request-params="indexRequest"
        :pagination="pagination"
        :loading="isloading"
        :options="{
          searchBox: false,
          tableControls: true,
          datePicker: false,
          dateRange: dates
          }">
        <template  slot="ufirstname" slot-scope="{row}">
          <router-link v-if="row.resellercount > 0" :to="{ path: `/commission-engine/downline-report`, query: { 'user_id': row.userid, 'name': row.ufirstname} }"> {{row.ufirstname }} {{row.ulastname }}</router-link>
          <span v-else> {{ row.ufirstname }} {{ row.ulastname }} </span>
        </template>
        <template  slot="sfirstname" slot-scope="{row}">
          <span> {{ row.sfirstname }} {{row.slastname }} </span>
        </template>
        <template  slot="afirstname" slot-scope="{row}">
          <span> {{ row.afirstname }} {{ row.alastname}} </span>
        </template>
        <template  slot="mywholesalesales" slot-scope="{row}">
          <span> {{ row.mywholesalesales | currency }} </span>
        </template>
        <template  slot="teamwholesalesales" slot-scope="{row}">
          <span> {{ parseFloat(row.teamwholesalesales) + parseFloat(row.mywholesalesales) | currency }} </span>
        </template>
        <template  slot="groupwholesalesales" slot-scope="{row}">
          <span> {{  row.groupwholesalesales | currency }} </span>
        </template>
        </cp-data-table>
    </div>
</template>

<script id="CpCommissionEngineDownLineReport">
    const Commission = require('../../resources/CommissionEngineAPIv0.js')
    const CePaginate = require('cepaginate')
    const Moment = require('moment')
    const Auth = require('auth')
    const _ = require('lodash')

    module.exports = {
      routing: [
        {
          name: 'site.CpCommisionEngineDownLineReport',
          path: '/commission-engine/downline-report',
          meta: {
            title: 'Downline Report'
          }
        }
      ],
      data () {
        return {
          Auth: Auth,
          isloading: true,
          currentUser: Auth.getClaims().fullName,
          userid: Auth.getAuthId().toString(),
          downlineReport: [],
          batches: [],
          query: {},
          isEmptyQuery: false,
          tableColumns: [
            { header: 'User', field: 'ufirstname', sortable: true },
            { header: 'Advisor', field: 'sfirstname', sortable: true },
            { header: 'Sponsor', field: 'afirstname', sortable: true },
            { header: 'Enroll Date', field: 'usignupdate', sortable: true },
            { header: 'Level', field: 'level', sortable: true },
            { header: 'Career Title', field: 'carrertitle', sortable: true },
            { header: 'Current Title', field: 'currenttitle', sortable: true },
            { header: 'Personal Volume', field: 'mywholesalesales', sortable: true },
            { header: 'P.S.Q.', field: 'psq', sortable: true },
            { header: 'Team Volume', field: 'teamwholesalesales', sortable: true },
            { header: 'Enterprise volume', field: 'groupwholesalesales', sortable: true }

          ],
          indexBatch: {
            command: 'querybatches',
            systemid: '1',
            orderby: 'id',
            userid: this.userid,
            orderdir: 'desc',
            offset: '0',
            limit: '100'
          },
          indexRequest: {
            start_date: Moment().subtract(10, 'days').format('YYYY-MM-DD'),
            end_date: Moment().format('YYYY-MM-DD'),
            order: 'asc',
            column: 'id',
            limit: '15',
            per_page: 15,
            search_term: '',
            page: 1,
            command: 'mydownstatsfull',
            systemid: '1',
            orderdir: 'asc',
            offset: '0',
            orderby: 'id',
            userid: this.userid,
            batchid: ''
          },
          dates: {
            start_date: Moment().subtract(10, 'days').format('YYYY-MM-DD'),
            end_date: Moment().format('YYYY-MM-DD')
          },
          pagination: {
            current_page: 1
          }
        }
      },
      mounted () {
        this.indexRequest.userid = this.userid
        // gives you a true of false value to render 'go back' button
        this.isEmptyQuery = !_.isEmpty(this.$route.query)
        this.query = this.$route.query
        // checks if query, then sets data, makes call
        if (!_.isEmpty(this.query)) {
          this.currentUser = this.query.name
          this.indexRequest.userid = this.query.user_id
          this.indexBatch.userid = this.query.user_id
          this.getBatches()
        } else {
          this.getBatches()
        }
      },
      methods: {
        goBack () {
          this.$router.go(-1)
        },
        getBatches () {
          Commission.runCommand(this.indexBatch)
            .then(res => {
              this.batches = res.batches
              // Parses date and adds formatting
              this.batches.forEach(e => {
                e.startdate = Moment(e.startdate).format('MMMM YYYY')
              })
              // Sets latest batch recieved to be default batch id
              this.indexRequest.batchid = this.batches[0].id
              this.getDownlineReport()
            })
        },
        getDownlineReport () {
          this.isloading = true
          // reformatting index-request for commission engine
          this.indexRequest.orderdir = this.indexRequest.order
          this.indexRequest.orderby = this.indexRequest.column
          this.indexRequest.offset = this.indexRequest.limit * (this.pagination.current_page - 1)
          this.indexRequest.limit = this.indexRequest.per_page.toString()
          this.indexRequest.per_page = this.indexRequest.per_page.toString()
          if (this.indexRequest.page) {
            let num = this.indexRequest.page
            let offset = this.indexRequest.offset
            this.indexRequest.offset = offset.toString()
            this.indexRequest.page = num.toString()
          }
          Commission.runCommand(this.indexRequest)
            .then(res => {
              if (!res.errors) {
                this.pagination.limit = this.indexRequest.limit
                this.pagination = CePaginate.paginate(this.pagination, res)
                this.downlineReport = res.userstatsfull
                this.isloading = false
              } else {
                this.isloading = false
                this.downlineReport = []
                this.pagination.last_page = 0
                this.pagination.current_page = 0
                this.$toast(res.errors.detail, {error: true})
              }
            })
        }
      }
    }
</script>

<style lang='scss' scoped>
    .commission-engine-downline-report {
      .cp-table-component  {
          td,th {
          white-space: normal !important;
        }
      }
        display: flex;
        justify-content: center;
        flex-direction: column;
        .custom-table-navigation {
          display: flex;
          justify-content: space-between;
          .user {
            width: 50%;
            display: flex;
            justify-content: flex-start;
            flex-direction: row;
            button {
              height: 30px;
              margin: 0px 10px 0px 0px;
              padding: 0px;
              align-self: center;
            }
          }
          .navigation {
            display: flex;
            justify-content: flex-end;
            width: 50%;
            align-items: center;
            button {
              height: 30px;
              select {
              width: 100%;
            }
          }
        }
      }
    }
</style>