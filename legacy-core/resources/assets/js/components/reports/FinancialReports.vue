<template lang="html">
<div>
  <cp-tabs
   :items="tabs"
   :callback="selectReport">
  </cp-tabs>
  <div>
    <cp-corp-sales-report v-if="componentVisibility['Corporate Sales']" :dates="dates"></cp-corp-sales-report>
    <cp-rep-sales-report v-if="componentVisibility['Representative Sales']" active: true :dates="dates"></cp-rep-sales-report>
    <cp-sales-tax-report v-if="componentVisibility['Sales Tax']" :dates="dates"></cp-sales-tax-report>
    <cp-sales-tax-owed-report v-if="componentVisibility['Sales Tax Owed']"></cp-sales-tax-owed-report>
    <cp-fbc-sales-report v-if="componentVisibility['FBC Sales']" :dates="dates"></cp-fbc-sales-report>
    <cp-affiliate-sales-report v-if="componentVisibility['Affiliate Sales']" :dates="dates"></cp-affiliate-sales-report>
    <cp-rep-transfers-report v-if="componentVisibility['Rep Transfers']" :dates="dates"></cp-rep-transfers-report>
  </div>
</div>
</template>

<script>
const moment = require('moment')
const Sales = require('../../resources/sales.js')

module.exports = {
  name: 'CpFinancialReports',
  routing: [
    {
      name: 'site.CpFinancialReports',
      path: 'reports/financial',
      meta: {
        title: 'Financial Reports'
      },
      props: true
    }
  ],
  data () {
    return {
      dates: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD')
      },
      tabs: [
        { name: 'Corporate Sales', active: true },
        { name: 'Representative Sales', active: false },
        { name: 'Sales Tax', active: false },
        { name: 'Sales Tax Owed', active: false },
        { name: 'Affiliate Sales', active: false }
      ],
      componentVisibility: {
        'Corporate Sales': true,
        'Representative Sales': false,
        'Sales Tax': false,
        'Sales Tax Owed': false,
        'Affiliate Sales': false,
        'Rep Transfers': false
      }
    }
  },
  created: function () {
    if (this.$getGlobal('rep_transfer').show) {
      this.tabs.push({name: 'Rep Transfers', active: false})
    }
  },
  mounted: function () {
    this.getIsAffiliate()
  },
  methods: {
    getIsAffiliate () {
      Sales.isAffiliate()
        .then((response) => {
          this.isAffiliate = response
        })
    },
    selectReport (name) {
      Object.keys(this.componentVisibility).forEach((name)=>{
        this.componentVisibility[name] = false
      })
      this.componentVisibility[name] = true
    }
  }
}
</script>

<style lang="scss">
  .report-tab {
    margin-right: 15px;
    padding: 5px 0px;
    color: $cp-main;
    &:hover {
      color: blue;
      cursor: pointer;
    }
  }
  .active-tab {
    color: blue;
    border-bottom: 1px solid blue;
    &:hover {
      color: $cp-main;
    }
  }
</style>
