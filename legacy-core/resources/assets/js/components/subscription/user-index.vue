<template lang="html">
  <div class="subscription-index-wrapper">
    <cp-tabs
   :items="[
     { name: 'Active', active: true },
     { name: 'Expired', active: false },
     { name: 'Expiring Soon', active: false },
     { name: 'All', active: false }
   ]"
   :callback="selectSubscriptions"></cp-tabs>
   <br>
     <a class="cp-button-link" download :href="'/api/v1/subscriptions/csv-download?status=' + indexRequest.status
         + '&search_term=' + indexRequest.search_term">Download CSV</a>
    <div>
      <cp-table-controls
        :date-picker="false"
        :index-request="indexRequest"
        :resource-info="pagination"
        :get-records="userIndex"></cp-table-controls>
    </div>
    <table class="cp-table-standard desktop">
      <thead>
        <th @click="sortColumn('user_id')">User ID
            <span v-show="indexRequest.column == 'user_id'">
                <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
        </th>
        <th @click="sortColumn('first_name')">User
            <span v-show="indexRequest.column == 'first_name'">
                <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
        </th>
        <th @click="sortColumn('title')">Subscription Plan
            <span v-show="indexRequest.column == 'title'">
                <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
        </th>
        <th  @click="sortColumn('price')">Price
            <span v-show="indexRequest.column == 'price'">
                <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
        </th>
        <th v-if="indexRequest.status === 'expired'">Last Attempt</th>
        <th @click="sortColumn('created_at')">Date Started
            <span v-show="indexRequest.column == 'created_at'">
                <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
        </th>
        <th @click="sortColumn('ends_at')">Next Billing Date
            <span v-show="indexRequest.column == 'ends_at'">
                <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
        </th>
        <th v-if="indexRequest.status === 'expired'" @click="sortColumn('last_fail_attempt')">Date Last Auto Renew Tried
            <span v-show="indexRequest.column == 'last_fail_attempt'">
                <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
        </th>
        <th @click="sortColumn('auto_renew')">Auto Renew
            <span v-show="indexRequest.column == 'auto_renew'">
                <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
        </th>
        <th v-if="indexRequest.status === 'expired'">Update User Subscription</th>
      </thead>
      <tbody>
        <tr v-for="sub in user_subscription">
          <td>{{ sub.user_id}}</td>
          <td><a @click="showReceipt = true, userReceipt = sub">{{ sub.first_name }} {{sub.last_name}}</a></td>
          <td>{{ sub.title }}</td>
          <td>{{ sub.price | currency }}</td>
          <td  v-if="indexRequest.status === 'expired' && sub.description">{{sub.description}}</td>
          <td v-else-if="indexRequest.status === 'expired'&& !sub.description">No response available</td>
          <td>{{ sub.created_at | cpStandardDate}}</td>
          <td v-if="Auth.hasAnyRole('Superadmin') && sub.billing_date != null">
            <input type="date" v-model="sub.billing_date" @blur="showConfirm = true, newDate = sub">
          </td>
          <td v-else>{{ sub.billing_date | cpStandardDate}}</td>
          <td  v-if="indexRequest.status === 'expired' && sub.last_fail_attempt !== '0000-00-00 00:00:00'">{{sub.last_fail_attempt | cpStandardDate}}</td>
          <td v-if="indexRequest.status === 'expired' && sub.last_fail_attempt === '0000-00-00 00:00:00'"></td>
          <td><input :disabled="sub.billing_date == null" class="toggle-switch" type="checkbox" v-model="sub.auto_renew" @change="updateAutoRenewal(sub)"></td>
          <td v-if="sub.token && indexRequest.status === 'expired' "><button class="cp-button-standard"
              @click="PayEditModal = true, payUser=sub">Update Subscription</button></td>
        </tr>
        </tbody>
    </table>

    <cp-confirm
    :show="showConfirm"
    v-model="showConfirm"
    :message="'Are you sure you what to change the billing date of ' + newDate.first_name + ' ' + newDate.last_name + ' to '+ moment(newDate.billing_date).format('L')+'?'"
    :callback="saveEndsAt"
    :params="{}"></cp-confirm>

    <section class="cp-table-mobile">
      <div v-for="sub in user_subscription">
        <div><span>User ID: </span><span>{{ sub.user_id}}</span></div>
        <div><span>User: </span><span><a @click="showReceipt = true, userReceipt = sub">{{ sub.first_name }} {{sub.last_name}}</a></span></div>
        <div><span>Subscription Plan: </span><span>{{ sub.title }}</span></div>
        <div><span>Price: </span><span>{{ sub.price | currency }}</span></div>
        <div  v-if="indexRequest.status === 'expired'"><span>Fail Description</span><span>{{sub.description}}</span></div>
        <div><span>Date Started: </span><span>{{ sub.created_at | cpStandardDate}}</span></div>
        <div><span>Next Billing Date: </span><span>{{ sub.ends_at | cpStandardDate}}</span></div>
        <div v-if="sub.description != ''"><span>Last Response: </span><span>{{sub.description}}</span></div>
        <td v-else><span></span><span>No Result Found</span></td>
        <div><span></span><span><input class="toggle-switch" type="checkbox" v-model="sub.auto_renew" @change="updateAutoRenewal(sub)"></span></div>
      </div>
    </section>
    <div class="align-center">
      <div class="no-results" v-show="this.user_subscription && this.user_subscription.length === 0">
          <span>No results</span>
      </div>
      <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
      <cp-pagination :pagination="pagination" :callback="userIndex" :offset="2"></cp-pagination>
    </div>
    <transition name=modal>
      <section class="cp-modal-standard" v-if="PayEditModal">
        <div>
          <cp-subscription-renewable-index :user="payUser" :modal-show="PayEditModal" @close="PayEditModal=false; userIndex()"></cp-subscription-renewable-index >
        </div>
      </section>
      <section class="cp-modal-standard" v-if="showReceipt" @click="showReceipt = false">
        <div class="cp-modal-body">
          <cp-show-receipt :user="userReceipt" :indexRequest="indexRequest"></cp-show-receipt>
        </div>
      </section>
  </transition>

  </div>
</template>

<script>
const moment = require('moment')
const Subscription = require('../../resources/subscription.js')
const Auth = require('auth')

module.exports = {
  name: 'CpSubscriptionUserIndex',
  routing: [
    {
      name: 'site.CpSubscriptionUserIndex',
      path: 'subscriptions',
      meta: {
        title: 'Subscriptions'
      },
      props: true
    }
  ],
  data: function () {
    return {
      moment: moment,
      Auth: Auth,
      showConfirm: false,
      showReceipt: false,
      loading: false,
      user_subscription: {
      },
      payUser: {
      },
      PayEditModal: false,
      pagination: {
        per_page: 15
      },
      newDate: [],
      asc: false,
      indexRequest: {
        order: 'ASC',
        column: 'id',
        per_page: 15,
        search_term: '',
        status: 'active',
        page: 1
      },
      reverseSort: false,
      userReceipt: {}
    }
  },
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.selectSubscriptions('Active')
  },
  methods: {
    selectSubscriptions (name) {
      switch (name) {
        case 'All':
          this.subStatus('all')
          break
        case 'Active':
          this.subStatus('active')
          break
        case 'Expired':
          this.subStatus('expired')
          break
        case 'Expiring Soon':
          this.subStatus('expiring')
          break
        default: }
    },
    userIndex: function () {
      this.loading = true
      this.indexRequest.page = this.pagination.current_page
      Subscription.getUserIndex(this.indexRequest)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, {error: true})
          }
          this.loading = false
          this.user_subscription = response.data
          response.per_page = parseInt(response.per_page)
          this.pagination = response
        })
    },
    updateAutoRenewal: function (sub) {
      Subscription.postAutoRenewalUpdate(sub)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, { error: true })
          }
          this.userIndex()
          return this.$toast('You have changed Auto renew for ' + sub.first_name + ' ' + sub.last_name + ' to ' + sub.auto_renew)
        })
    },
    saveEndsAt: function () {
      Subscription.updateUserEndsAt(this.newDate)
        .then((response) => {
          if (!response.error) {
            return this.$toast('You have changed the billing date')
          }
          this.newDate = []
          this.userIndex()
        })
    },
    subStatus: function (status) {
      this.indexRequest.status = status
      this.pagination.current_page = 1
      this.userIndex()
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
      this.userIndex()
    }
  },
  events: {
    'search_term': function (term) {
      this.indexRequest.search_term = term
      this.pagination.current_page = 1
      this.userIndex()
    }
  },
  components: {
    'CpSubscriptionRenewableIndex': require('./CpSubscriptionRenewableIndex.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    'CpConfirm': require('../../cp-components-common/CpConfirm.vue'),
    'CpShowReceipt': require('./CpShowReceipt.vue')
  }
}
</script>

<style lang="sass">
  .subscription-index-wrapper {
    .cp-button-standard {
      padding: 5px 10px;
      &:hover {
          color: white;
      }
      &:visited {
          color: white;
      }
      &:focus {
          text-decoration: none;
      }
    }
  }
</style>
