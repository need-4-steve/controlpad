<template lang="html">
  <div class="">
    <cp-tabs
     :items="[
       { name: 'ALL', active: true },
       { name: 'ADMINS', active: false },
       { name: $getGlobal('title_rep').value.toUpperCase() + 'S', active: false },
       { name: 'CUSTOMERS', active: false },
     ]"
     :callback="selectEmails"></cp-tabs>
   <cp-table-controls
     :date-picker="true"
     :date-range="indexRequest"
     :index-request="indexRequest"
     :resource-info="pagination"
     :get-records="selectEmails">
   </cp-table-controls>
   <table class="cp-table-standard desktop">
       <thead>
         <th @click="sortColumn('created_at')">Date
             <span v-show="indexRequest.column == 'created_at'">
                 <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                 <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
             </span>
         </th>
         <th>User ID</th>
         <th @click="sortColumn('to')">Recipient Email Address
             <span v-show="indexRequest.column == 'to'">
                 <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                 <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
             </span>
         </th>
         <th @click="sortColumn('subject')">Email Subject
             <span v-show="indexRequest.column == 'subject'">
                 <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                 <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
             </span>
         </th>
       </thead>
           <tr v-for="email in emails">
               <td>{{ email.created_at | cpStandardDate('time')}}</td>
               <td>{{ email.user_id }}</td>
               <td >{{ email.to }}</td>
               <td>{{ email.subject }}</td>
           </tr>
       </tbody>
   </table>
   <section  class="cp-table-mobile">
     <div v-for="email in emails">
     <div><span>Date: </span><span>{{ email.created_at | cpStandardDate('time')}}</span></div>
     <div><span>User ID: </span><span>{{ email.user_id }}</span></div>
     <div><span>Recipient Email Address: </span><span>{{ email.to }}</span></div>
     <div><span>Email Subject: </span><span>{{ email.subject }}</span></div>
   </div>
   </section>
   <div class="align-center">
     <div class="no-results" v-if="noResults">
         <span>No results for this timeframe</span>
     </div>
     <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
     <cp-pagination :pagination="pagination" :callback="getEmails" :offset="2"></cp-pagination>
   </div>
 </div>
</template>

<script>
const moment = require('moment')
const Emails = require('../../resources/email-reports.js')

module.exports = {
  data () {
    return {
      noResults: false,
      loading: false,
      reverseSort: false,
      indexRequest: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'DESC',
        column: 'created_at',
        search_term: '',
        status: 'all',
        per_page: 15,
        page: 1
      },
      pagination: {
        per_page: 15
      },
      emails: {}
    }
  },
  mounted () {
    this.getEmails()
  },
  methods: {
    selectEmails (name) {
      switch (name) {
        case 'ALL':
          this.indexRequest.status = 'all'
          break
        case 'ADMINS':
          this.indexRequest.status = '7'
          break
        case this.$getGlobal('title_rep').value.toUpperCase() + 'S':
          this.indexRequest.status = '5'
          break
        case 'CUSTOMERS':
          this.indexRequest.status = '3'
          break
        default: }
      this.getEmails()
    },
    getEmails () {
      this.indexRequest.page = this.pagination.current_page
      Emails.index(this.indexRequest)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message)
          }
          this.emails = response.data
          this.pagination = response
        })
    },
    sortColumn: function (column) {
      this.reverseSort = !this.reverseSort
      this.indexRequest.column = column
      this.asc = !this.asc
      if (this.asc === true) {
        this.indexRequest.order = 'asc'
      } else {
        this.indexRequest.order = 'desc'
      }
      this.getEmails()
    }
  },
  components: {
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

</style>
