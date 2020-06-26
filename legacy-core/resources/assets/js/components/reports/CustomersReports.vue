<template lang="html">
<div>
  <div>
    <cp-corp-customers-report v-if="componentVisibility['Notes']" active: true :dates="dates"></cp-corp-customers-report>
  </div>
</div>
</template>


<script>
const moment = require('moment')
const Sales = require('../../resources/sales.js')
const Users = require('../../resources/users.js')

module.exports = {
  name: 'CpCustomersReports',
  routing: [
    {
      name: 'site.CpCustomersReports',
      path: 'reports/customers',
      meta: {
        title: 'Customer Reports'
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
        { name: 'Notes', active: true }
      ],
      componentVisibility: {
        'Notes': true
      }
    }
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
