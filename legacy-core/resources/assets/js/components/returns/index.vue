<template>
    <div class="returns-wrapper">
      <cp-tabs
         :select-value="indexRequest.status"
         :items="[
           { name: 'Open Returns', active: true },
           { name: 'Pending Returns', active: false },
           { name: 'Closed Returns', active: false },
           { name: 'All Returns', active: false }
         ]"
         :callback="selectOrders"></cp-tabs>
        <cp-table-controls
          :date-picker="true"
          :date-range="indexRequest"
          :index-request="indexRequest"
          :resource-info="pagination"
          :get-records="getReturns"></cp-table-controls>
        <table class="cp-table-standard desktop">
            <thead>
              <th @click="sortColumn('returns.created_at')">Date Requested
                  <span v-show="indexRequest.column == 'returns.created_at'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('returns.id')">Return Ticket ID
                  <span v-show="indexRequest.column == 'returns.id'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('order_id')">Receipt ID
                  <span v-show="indexRequest.column == 'order_id'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('last_name')">Customer Name
                  <span v-show="indexRequest.column == 'last_name'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th> Requested Quantity</th>
              <th> Notes</th>
              <th @click="sortColumn('return_status_id')">Status
                  <span v-show="indexRequest.column == 'return_status_id'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th> Return Ticket</th>
            </thead>
            <tbody v-if="!loading">
                <tr v-for="r in returns">
                  <td>{{ r.created_at | cpStandardDate }}</td>
                  <td>{{r.id}}</td>
                  <td><a :href="`/orders/${ r.order.receipt_id }`">{{ r.order.receipt_id }}</a></td>
                  <td>{{ r.first_name }} {{r.last_name}}</td>
                  <td>{{r.requested_quantity}}</td>
                  <td v-if="r.return_status_id !== 3">
                    <textarea v-model="r.notes" autofocus></textarea>
                  </td>
                  <td v-else></td>
                  <td v-if="r.return_status_id !== 3">
                    <div class="cp-select-standard select-outline" v-if="!loading">
                      <select v-model="statusIds[r.id]" @change="checkStatus(r); pendingClose = r">
                        <option v-for="rStatus in returnStatuses" :value="rStatus.id">{{ rStatus.name }}</option>
                      </select>
                    </div>
                  </td>
                  <td v-else>This return is closed</td>
                  <td><a :href="`/return/${r.id}`" class="cp-button-link pull-right">Return</a></td>
                </tr>
            </tbody>
        </table>
        <section class="cp-table-mobile">
          <div v-for="r in returns">
          <div><span>Date Requested: </span><span>{{ r.created_at | cpStandardDate }}</span></div>
          <div><span>Return Ticket ID: </span><span>{{r.id}}</span></div>
          <div><span>Receipt ID: </span><span><a :href="`/orders/${ r.order.receipt_id }`">{{ r.order.receipt_id }}</a></span></div>
          <div><span>Customer Name: </span><span>{{ r.first_name }} {{r.last_name}}</span></div>
          <div><span>Requested Quantity: </span><span>{{r.requested_quantity}}</span></div>
          <div v-if="r.return_status_id !== 3"><span>Notes: </span><span><textarea v-model="r.notes" autofocus></textarea></span></div>
          <div v-else><span>Notes: </span><span>{{r.comments | cpStandardDate }}</span></div>
          <div v-if="r.return_status_id !== 3"><span>Status: </span><span>
            <div class="cp-select-standard select-outline" v-if="!loading">
              <select v-model="statusIds[r.id]" @change="updateStatus(r); pendingClose = r">
                <option v-for="rStatus in returnStatuses" :value="rStatus.id">{{ rStatus.name }}</option>
              </select>
            </div></span></div>
          <div v-else><span>Status: </span><span>This return is closed</span></div>
          <div><span></span><span><a :href="`/return/${r.id}`" class="cp-button-link pull-right">Return</a></span></div>
        </div>
        </section>
        <cp-confirm
        :show="showConfirm"
        :message="'Are you sure you want to close this return?'"
        v-model="showConfirm"
        :callback="updateStatus"
        :params="pendingClose"
        :on-cancelled="resetStatus"></cp-confirm>
        <div class="align-center">
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getReturns" :offset="2"></cp-pagination>
        </div>
    </div>
</template>

<script>
const Returns = require('../../resources/returns.js')
const Auth = require('auth')
const moment = require('moment')

module.exports = {
  data: function () {
    return {
      statusIds: {},
      pendingClose: {},
      showConfirm: false,
      returnStatuses: '',
      loading: false,
      returns: [],
      pagination: {
        per_page: 15
      },
      activeStatus: 'Open',
      asc: false,
      reverseSort: false,
      auth: Auth,
      returnsActive: {
        all: false,
        open: true,
        closed: false,
        pending: false
      },
      indexRequest: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'DESC',
        column: 'returns.created_at',
        per_page: 15,
        search_term: '',
        page: 1,
        status: 'Open'
      }
    }
  },
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getReturns()
    this.getReturnStatuses()
  },
  methods: {
    checkStatus (r) {
      if (this.statusIds[r.id] === 3) {
        this.showConfirm = true
        return
      }
      this.updateStatus(r)
    },
    selectOrders (name) {
      switch (name) {
        case 'Open Returns':
          this.changeStatus('open')
          break
        case 'Pending Returns':
          this.changeStatus('pending')
          break
        case 'Closed Returns':
          this.changeStatus('closed')
          break
        case 'All Returns':
          this.changeStatus('all')
          break
        default:
      }
    },
    getReturns: function () {
      this.indexRequest.page = this.pagination.current_page
      this.loading = true
      Returns.index(this.indexRequest)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          }
          this.buildReturnsObject(response.data)
          this.returns = response.data
          this.pagination = response
          this.loading = false
        })
    },
    getReturnStatuses: function () {
      this.loading = true
      Returns.returnStatuses()
        .then(response => {
          if (!response.error) {
            this.returnStatuses = response
            this.loading = false
          } else {
            this.$toast('Returns are unavailable, please contact support', {error: true})
          }
        })
    },
    buildReturnsObject: function (r) {
      for (var i = 0; i < r.length; i++) {
        this.statusIds[r[i].id] = r[i].return_status_id
      }
    },
    updateStatus: function (r) {
      r.return_status_id = this.statusIds[r.id]
      Returns.updateStatus(r.id, r)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          }
          this.getReturns()
          r.notes = ''
        })
    },
    resetStatus (r) {
      this.statusIds[r.id] = r.return_status_id
    },
    changeStatus: function (status) {
      this.returnsActive.open = false
      this.returnsActive.closed = false
      this.returnsActive.pending = false
      this.returnsActive.all = false
      switch (status) {
        case 'open':
          this.activeStatus = 'Open'
          this.returnsActive.open = true
          break
        case 'closed':
          this.activeStatus = 'Closed'
          this.returnsActive.closed = false
          break
        case 'pending':
          this.activeStatus = 'Pending'
          this.returnsActive.pending = true
          break
        default:
          this.activeStatus = 'all'
          this.returnsActive.all = true
      }
      this.indexRequest.status = status
      this.indexRequest.current_page = 1
      this.getReturns()
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
      this.getReturns()
    }
  },
  components: {
    CpConfirm: require('../../cp-components-common/CpConfirm.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
.select-outline {
  select {
    border: 1px solid !important;
    min-width: 86px;
    text-indent: 2px;
  }
}
.order-nav {
    background-color: $cp-main;
    overflow: hidden;
    width: 100%;
    margin-top: 15px;
    margin-bottom: 15px;
    button {
        float: left;
        border: none;
        font-size: 15px;
        padding: 15px;
        color: white;
        background-color: $cp-main;
        transition: all 0.3s ease 0s;
        -webkit-transition: all 0.3s ease 0s;
        &.active {
            color: $cp-main;
            background-color: $cp-lighterGrey;
        }
    }
    button:hover {
        background-color: lighten($cp-main, 15%);
        &.active {
            color: $cp-main;
            background-color: $cp-lighterGrey;
        }
    }
    .order-update-button {
        float: right;
    }
}

</style>
