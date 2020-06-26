<template lang="html">
  <div class="rep-sales-report">
    <br>
    <a class="cp-button-link" v-if="!showDetails" download :href="'/api/v1/report/csv/affiliateTotal?search_term=' + indexRequest.search_term + '&column=' + indexRequest.column + '&order=' + indexRequest.order + '&start_date=' + indexRequest.start_date + '&end_date=' + indexRequest.end_date">Affiliate Sales Totals CSV</a>
    <a class="cp-button-link" v-if="!showDetails" download :href="'/api/v1/report/csv/affiliateOrder?search_term=' + indexRequest.search_term + '&column=' + indexRequest.column + '&order=' + indexRequest.order + '&start_date=' + indexRequest.start_date + '&end_date=' + indexRequest.end_date + '&name=affiliate'">Affiliate Sales Orders CSV</a>
       <div class="" v-if="!showDetails">
        <cp-totals-banner
        totals-title="All Affiliate Sales"
        :totals="[
        {title:'Total Affiliate Sales', amount: salesTotal}]"></cp-totals-banner>
          <cp-table-controls
          :date-picker="true"
          :date-range="dates"
          :index-request="indexRequest"
          :resource-info="pagination"
          :get-records="getRepIndexandTotals"></cp-table-controls>
      <!-- totals banner -->
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
            <td><a @click="showAffiliateDetails(rep)">{{ rep.full_name }}</a></td>
            <td>{{ rep.retail_total | currency }}</td>
          </tr>
        </tbody>
      </table>
      <div class="align-center">
        <div class="no-results" v-if="noResults">
          <span>No results</span>
        </div>
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination :pagination="pagination" :callback="getAffiliateIndex" :offset="2"></cp-pagination>
      </div>
    </div>
    <div v-if="showDetails" class="show-details">
      <h3>{{ selectedUser.full_name }}</h3>
      <button class="cp-button-standard" v-if="showDetails" @click="hideAffiliateDetails()"><i class="mdi mdi-chevron-left"></i>
Back to all Affiliate Sales</button>
    </div>
    <cp-affiliate-details-report v-if="showDetails" :user-id="selectedUser.id"></cp-affiliate-details-report>
  </div>
</template>
 <script>
const Sales = require('../../resources/sales.js')
const moment = require('moment')
const Auth = require('auth')
module.exports = {
  data () {
    return {
      loading: true,
      noResults: false,
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
        name: 'affiliate'
      }
    }
  },
  props: {
    dates: {}
  },
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getAffiliateTotals()
    this.getAffiliateIndex()
  },
  methods: {
    getRepIndexandTotals () {
      this.getAffiliateTotals()
      this.getAffiliateIndex()
    },
    getAffiliateTotals () {
      this.indexRequest.start_date = moment(this.dates.start_date).format('YYYY-MM-DD')
      this.indexRequest.end_date = moment(this.dates.end_date).format('YYYY-MM-DD')
      Sales.getAffiliateTotals(this.indexRequest)
        .then((response) => {
          this.salesTotal = response
        })
    },
    getAffiliateIndex () {
      this.loading = true
      this.indexRequest.start_date = moment(this.dates.start_date).format('YYYY-MM-DD')
      this.indexRequest.end_date = moment(this.dates.end_date).format('YYYY-MM-DD')
      this.indexRequest.page = this.pagination.current_page
      this.reps = {}
      Sales.getAffiliateIndex(this.indexRequest)
        .then((response) => {
          if (!response.error) {
            this.reps = response.data
            this.pagination = response
          }
          this.loading = false
        })
    },
    showAffiliateDetails (rep) {
      this.selectedUser = rep
      this.showDetails = true
    },
    hideAffiliateDetails () {
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
      this.getAffiliateIndex()
    }
  },
  events: {
    'search_term': function (term) {
      this.indexRequest.search_term = term
      this.pagination.current_page = 1
      this.getAffiliateIndex()
      this.getAffiliateTotals()
    }
  },
  components: {
    CpTotalsBanner: require('../reports/CpTotalsBanner.vue'),
    CpAffiliateDetailsReport: require('../reports/CpAffiliateDetailsReport.vue'),
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