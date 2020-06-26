<template>
    <div class="Autoship-subscriptions-index-wrapper">
        <cp-tabs
        :items="[
        { name: 'All', active: true },
        { name: 'Active', active: false },
        { name: 'Failed', active: false },
        { name: 'Renewing Soon', active: false },
        { name: 'Canceled', active: false },
        ]"
        :callback="getReports"
        v-model="indexRequest.filter">

        </cp-tabs>
        <cp-data-table
        v-if="!loading"
            :table-data="reports"
            :table-columns="tableColumns"
            :pagination="pagination"
            :recall-data="getReports"
            :request-params="indexRequest"
            :options="{
                tableControls: true,
            }">
            <template slot="pid" slot-scope="{row}">
              <span class='cp-button-standard' @click="showSubscription=true; selectedSubscription = row; getCardToken(row.buyer_pid)">show</span>
            </template>
            <template slot="frequency" slot-scope="{row}">
              <span>{{ row | frequency }}</span>
            </template>
            <template slot="price" slot-scope="{row}">
              <span>{{row.subtotal - row.discount | currency}}</span>
            </template>
            <template slot="next_billing_at" slot-scope="{row}">
              <div>{{row.next_billing_at | cpStandardDate }}</div>
              <div class="cp-button-standard renew" v-if="moment().utc().isAfter(moment(row.next_billing_at).utc()) && row.disabled_at === null" @click="showRenew = true; selectedSubscription = row">renew</div>
            </template>
            <template slot="disabled_at" slot-scope="{row}">
              <span v-if="row.disabled_at"> {{row.disabled_at | cpStandardDate}}</span>
              <span class="cp-button-standard" v-else @click="showDisable = true; selectedSubscription = row">cancel</span>
            </template>
        </cp-data-table>
        <!--  MODAL -->
          <transition>
            <section class="cp-modal-standard" v-if="Vue.options.components.CpAutoshipSubscriptionDetails && showSubscription" :subscription="selectedSubscription && showSubscription" @click="showSubscription = false">
              <div class="cp-modal-body">
                <cp-autoship-subscription-details :subscription="selectedSubscription" :cardToken="cardToken"></cp-autoship-subscription-details>
              </div>
            </section>
          </transition>
        <cp-confirm
            :message="'Are you sure you want to cancel this ' + $getGlobal('autoship_display_name').value +'?'"
            v-model="showDisable"
            :show="showDisable"
            :callback="disableSubscription"
            :config-options="{ buttonTextOne: 'Yes', buttonTextTwo: 'No'}"
            :params="selectedSubscription"></cp-confirm>
        <cp-confirm
            :message="'Are you sure you want to manually renew this ' + $getGlobal('autoship_display_name').value +'?'"
            v-model="showRenew"
            :show="showRenew"
            :callback="renewSubscription"
            :config-options="{ buttonTextOne: 'Yes', buttonTextTwo: 'No'}"
            :params="selectedSubscription"></cp-confirm>
    </div>
</template>
<script>
const Autoship = require('../../resources/AutoshipAPIv0.js')
const Auth = require('auth')
const Users = require('../../resources/UsersAPIv0.js')
const moment = require('moment')

module.exports = {
  routing: [
    { name: 'site.CpAutoshipSubscriptionsIndex', meta: { title: 'Subscriptions' }, path: 'autoship/subscriptions' }
  ],
  data () {
    return {
      Auth: Auth,
      moment: moment,
      Vue: Vue,
      cardToken: {},
      selectedSubscription: {},
      showSubscription: false,
      showDisable: false,
      showRenew: false,
      title: 'Subscriptions',
      loading: true,
      reports: {},
      indexRequest: {
        expands: ['last_attempt'],
        filter: 'All',
        per_page: 15,
        search_term: '',
        sort_by: 'created_at',
        page: 1
      },
      pagination: {
        per_page: 15
      },
      tableColumns: [
        { header: 'Details', field: 'pid', sortable: false },
        { header: 'Buyer First Name', field: 'buyer_first_name', sortable: true },
        { header: 'Buyer Last Name', field: 'buyer_last_name', sortable: true },
        { header: 'Frequency', field: 'frequency' },
        { header: 'Total', field: 'subtotal', filter: 'currency' },
        { header: 'Created At', field: 'created_at', filter: 'cpStandardDate', sortable: true },
        { header: 'Next Billing At', field: 'next_billing_at', filter: 'cpStandardDate', sortable: true },
        { header: 'Canceled At', field: 'disabled_at', sortable: true }
      ]
    }
  },
  filters: {
    frequency (row) {
      if (row.frequency == 1) {
        if (row.duration === 'Days') {
          return 'Daily'
        }
        return row.duration.replace('s', 'ly')
      }
      return row.frequency + ' ' + row.duration
    }
  },
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getReports()
  },
  methods: {
    getCardToken (buyerPid) {
      Users.getCardToken(buyerPid)
        .then((response) => {
          if (!response.error) {
            this.cardToken = response
          } else {
            this.cardToken = {}
          }
        })
    },
    getReports () {
      if (this.indexRequest.filter === 'Renewing Soon') {
        this.indexRequest.filter = 'renewing_soon'
      } else if (this.indexRequest.filter === 'Canceled') {
        this.indexRequest.filter = 'disabled'
      } else {
        this.indexRequest.filter = this.indexRequest.filter.toLowerCase()
      }
      if (this.indexRequest.filter === 'failed') {
        this.tableColumns.push({
          header: 'Failure Description', field: 'last_attempt_description'
        })
        this.tableColumns.splice(9, 1)
      } else {
        this.tableColumns.splice(8, 1)
      }
      this.indexRequest.page = this.pagination.current_page
      Autoship.getSubscriptions(this.indexRequest)
        .then((response) => {
          this.loading = false
          if (!response.error) {
            this.reports = response.data
            response.per_page = parseInt(response.per_page)
            this.pagination = response
          } else {
            this.$toast(response.message, { dismiss: false })
          }
        })
    },
    disableSubscription (subscripition) {
      Autoship.disableSubscription(subscripition.id)
        .then((response) => {
          this.loading = false
          if (!response.error) {
            this.$toast('successfully canceled', { dismiss: false })
            this.getReports()
          } else {
            this.$toast(response.message, { dismiss: false, error: true })
          }
          this.getReports()
        })
    },
    renewSubscription (subscripition) {
      Autoship.processSubscription(subscripition.id)
        .then((response) => {
          this.loading = false
          if (!response.error) {
            this.$toast('successfully renewed', { dismiss: false })
            this.getReports()
          } else {
            this.$toast('renew failed', { dismiss: false, error: true })
          }
          this.getReports()
        })
    }
  }
}
</script>

<style lang="scss">
.Autoship-subscriptions-index-wrapper {
  .renew {
    max-width: 42px;
  }
}
</style>
