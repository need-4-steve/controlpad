<template lang="html">
  <div class="sales-tax-owed-wrapper">
    <br>
    <a v-if="!showDetails" class='cp-button-link' download :href="'/api/v1/report/csv/taxOwedCSV?per_page=' + indexRequest.per_page">Sales Tax Owed CSV</a>

      <cp-totals-banner v-if="!showDetails" totals-title="Taxes Owed By Rep"></cp-totals-banner>
      <!-- data table  -->
      <div class="" v-if="!showDetails">
      <table class="cp-table-standard">
        <thead>
          <th>User ID</th>
          <th>Rep Name</th>
          <th>Total Taxes Owed</th>
        </thead>
        <tbody>
          <tr v-for="(oweTax, index) in taxOwedTotals">
            <td>{{ oweTax.userId }}</td>
            <td><a href="javascript:void(0)" @click="OwedTaxDetails(oweTax)">{{ oweTax.name }}</a></td>
            <td>{{ oweTax.taxOwed | currency }}</td>
          </tr>
        </tbody>
      </table>
      <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination :pagination="pagination" :callback="getTaxOwedTotals" :offset="2"></cp-pagination>
      </div>
  </div>
      <div v-if="showDetails" class="show-details-report">
          <div>
              <h3>{{ selectedUser.name }}</h3>
          </div>
            <div>
                <button class="cp-button-standard" v-if="showDetails" @click="hideOwedTaxDetails()"><i class="mdi mdi-chevron-left"></i>
                Back To Sales Tax Owed
            </button>
          </div>
      </div>
      <cp-sales-tax-owed-details-report v-if="showDetails" :user-id="selectedUser.userId"></cp-sales-tax-owed-details-report>
  </div>
</template>

<script>
const Sales = require('../../resources/sales.js')
const Auth = require('auth')

module.exports = {
  data () {
    return {
      noResults: false,
      loading: false,
      selectedUser: null,
      showDetails: false,
      pagination: {
        per_page: 15
      },
      reverseSort: false,
      indexRequest: {
        order: 'asc',
        column: 'taxOwed',
        per_page: 15,
        page: 1
      },
      taxOwedTotals: {}
    }
  },

  mounted () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getTaxOwedTotals()
  },
  methods: {
    getTaxOwedTotals () {
      this.loading = true
      this.indexRequest.page = this.pagination.current_page
      Sales.getSalesTaxOwedTotals(this.indexRequest)
          .then((response) => {
            if (!response.error) {
              this.pagination = response
              this.taxOwedTotals = response.data
              this.loading = false
              return response
            }
          })
    },
    OwedTaxDetails (oweTax) {
      this.selectedUser = oweTax
      this.showDetails = true
      return
    },
    hideOwedTaxDetails () {
      this.showDetails = false
      return
    }
  },
  components: {
    CpSalesTaxOwedDetailsReport: require('../reports/CpSalesTaxOwedDetailsReport.vue'),
    CpTotalsBanner: require('../reports/CpTotalsBanner.vue')

  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
.sales-tax-owed-wrapper {
    .show-details-report {
      padding: 10px 5px;
      display: flex;
      justify-content: space-between;
      h3 {
          margin:0px;
      }
    }
}
</style>
