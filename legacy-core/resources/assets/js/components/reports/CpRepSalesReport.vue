<template lang="html">
  <div class="rep-sales-report">
    <br>
    <a v-if="!showDetails" class="cp-button-link" download :href="'/api/v1/report/csv/repTotal?search_term=' + indexRequest.search_term + '&column=' + indexRequest.column + '&order=' + indexRequest.order + '&start_date=' + indexRequest.start_date + '&end_date=' + indexRequest.end_date">{{$getGlobal('title_rep').value}} Sales Totals CSV</a>
    <a v-if="!showDetails" class="cp-button-link" download :href="'/api/v1/report/csv/repOrder?search_term=' + indexRequest.search_term + '&column=' + indexRequest.column + '&order=' + indexRequest.order + '&start_date=' + indexRequest.start_date + '&end_date=' + indexRequest.end_date + '&name=' + indexRequest.name">{{$getGlobal('title_rep').value}} Sales Orders CSV</a>


      <div class="" v-if="!showDetails">
        <!-- totals banner -->
        <cp-totals-banner
        totals-title="All Representative Sales"
        :totals="[
        {title:'Total Rep Sales', amount: salesTotal}]"></cp-totals-banner>
        <!--date picker -->
        <cp-table-controls
        :date-picker="true"
        :date-range="dates"
        :index-request="indexRequest"
        :resource-info="pagination"
        :get-records="getRepIndexandTotals"></cp-table-controls>

      <!-- data table  -->
      <table class="cp-table-standard">
        <thead>
          <th @click="sortColumn('id')">ID
            <span v-show="indexRequest.column == 'id'">
              <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
              <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
          </th>
          <th @click="sortColumn('last_name')">{{$getGlobal('title_rep').value}}
            <span v-show="indexRequest.column == 'last_name'">
              <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
              <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
          </th>
          <th @click="sortColumn('retail_total')">Total Retail Sales
            <span v-show="indexRequest.column == 'retail_total'">
              <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
              <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
          </th>
        </thead>
        <tbody>
          <tr v-for="rep in reps" >
            <td>{{ rep.id }}</td>
            <td><a @click="showRepDetails(rep)">{{ rep.last_name + ', ' + rep.first_name }}</a></td>
            <td>{{ rep.retail_total | currency }}</td>
          </tr>
        </tbody>
      </table>
      <div class="align-center">
        <div class="no-results" v-if="noResults">
          <span>No results for this timeframe</span>
        </div>
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination
        :pagination="pagination"
        :callback="getRepIndex"
        :offset="2"></cp-pagination>
      </div>
    </div>
    <div v-if="showDetails" class="show-details">
      <h3>{{ selectedUser.full_name }}</h3>
      <button class="cp-button-standard" v-if="showDetails" @click="hideRepDetails()"><i class="mdi mdi-chevron-left"></i>
Back to all Rep Sales</button>
    </div>
    <cp-rep-details-report v-if="showDetails" :user-id="selectedUser.id"></cp-rep-details-report>
  </div>
</template>

<script>
const Sales = require('../../resources/sales.js')
const moment = require('moment')
const Auth = require('auth')

module.exports = {
  data () {
    return {
      noResults: false,
      loading: true,
      reps: [],
      showDetails: false,
      selectedUser: null,
      salesTotal: null,
      pagination: {
        per_page: 15
      },
      reverseSort: false,
      indexRequest: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'ASC',
        column: 'last_name',
        per_page: 15,
        search_term: '',
        page: 1,
        name: 'rep'
      }
    }
  },
  props: {
    dates: {}
  },
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getRepTotals()
    this.getRepIndex()
  },
  methods: {
    getRepIndexandTotals () {
      this.getRepTotals()
      this.getRepIndex()
    },
    getRepTotals () {
      this.indexRequest.start_date = moment(this.dates.start_date).format('YYYY-MM-DD')
      this.indexRequest.end_date = moment(this.dates.end_date).format('YYYY-MM-DD')
      Sales.getRepTotals(this.indexRequest)
        .then((response) => {
          this.salesTotal = response
        })
    },
    getRepIndex () {
      this.loading = true
      this.indexRequest.start_date = moment(this.dates.start_date).format('YYYY-MM-DD')
      this.indexRequest.end_date = moment(this.dates.end_date).format('YYYY-MM-DD')
      this.indexRequest.page = this.pagination.current_page
      this.reps = {}
      Sales.getRepIndex(this.indexRequest)
      .then((response) => {
        if (!response.error) {
          this.reps = response.data
          this.pagination = response
        }
        if (response.total === 0) {
          this.noResults = true
          setTimeout(function () {
            this.noResults = false
          }.bind(this), 3000)
        }
        this.loading = false
      })
    },
    showRepDetails (rep) {
      this.selectedUser = rep
      this.showDetails = true
    },
    hideRepDetails () {
      this.showDetails = false
    },
    sortColumn (column) {
      this.reverseSort = !this.reverseSort
      this.indexRequest.column = column
      this.asc = !this.asc
      if (this.asc === true) {
        this.indexRequest.order = 'asc'
      } else {
        this.indexRequest.order = 'desc'
      }
      this.getRepIndex()
    }
  },
  events: {
    'search_term': function (term) {
      this.indexRequest.search_term = term
      this.pagination.current_page = 1
      this.getRepIndex()
      this.getRepTotals()
    }
  },
  components: {
    CpTotalsBanner: require('../reports/CpTotalsBanner.vue'),
    CpRepDetailsReport: require('../reports/CpRepDetailsReport.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
.rep-sales-report {
  .show-details {
    padding: 5px 0px;
    overflow: hidden;
    h3 {
      float: left;
    }
    button {
      margin-top: 20px;
      float: right;
    }
  }
}
</style>
