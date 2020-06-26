<template>
    <div class="returns-show-wrapper">
      <div v-if="!loading">
      <cp-returned-detail :returns="pendingReturn"></cp-returned-detail>
      <cp-return-history v-if="pendingReturn" :order-id="pendingReturn.order_id"></cp-return-history>
        <h3>Pending Return</h3>
        <table class="cp-table-standard">
            <thead>
              <th @click="sortColumn('pendingReturn.created_at')">Date Requested
                  <span v-show="indexRequest.column == 'pendingReturn.created_at'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('name')">Item Name
                  <span v-show="indexRequest.column == 'name'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th>Price</th>
              <th>Item Id</th>
              <th>Quantity Requested</th>
              <th>Reason for Return</th>
              <th>Comments</th>
              <th>Note</th>
              <th>Inventory to Return </th>
              <th>Refund Amount</th>
              <th><!-- submit --></th>
            </thead>
            <tbody>
                <tr v-if="pendingReturn.return_status_id !== 3" v-for="r in pendingReturn.lines">
                  <td>{{ r.created_at | cpStandardDate }}</td>
                  <td>{{ r.name }}</td>
                  <td>{{ r.price | currency}}</td>
                  <td>{{ r.item_id }}</td>
                  <td>{{ r.quantity }}</td>
                  <td>{{ r.reason.name }}</td>
                  <td>{{ r.comments || 'None'}}</td>
                  <td>
                    <textarea v-model="r.notes" autofocus></textarea>
                  </td>
                  <td><input type="number" name="" value="" v-model.number="r.inventoryQuantity"></td>
                  <td><span>$</span><input type="number" name="" value="" v-model="r.returnAmount"></td>
                </tr>
                <tr v-if="pendingReturn.return_status_id === 3">
                  <td>This Return Ticket is Closed</td>
                </tr>
            </tbody>
        </table>
        <button v-if="pendingReturn.return_status_id !== 3" class="cp-button-standard" type="button" name="button"  @click="showConfirm = true">Submit Return</button>

        <cp-confirm
        :message="'Are you sure you want to submit this return?'"
        v-model="showConfirm"
        :callback="confirmReturn"
        :show="showConfirm"
        :params="pendingReturn"></cp-confirm>
      </div>
        <div class="align-center">
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        </div>
    </div>
</template>

<script>
const Returns = require('../../resources/returns.js')

module.exports = {
  data: function () {
    return {
      pendingReturn: {},
      showConfirm: false,
      indexRequest: {},
      reverseSort: true,
      loading: true
    }
  },
  computed: {},
  mounted: function () {
    var returnedId = this.$pathParameter() // workaround for routing without props
    this.getReturned(returnedId)
  },
  methods: {
    getReturned: function (id) {
      this.loading = true
      Returns.returnedShow(id)
        .then((response) => {
          if (response.error) {
            return
          }
          this.pendingReturn = response
          this.loading = false
        })
    },
    submitReturn: function () {

    },
    confirmReturn: function (items) {
      Returns.refund(items)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, { error: true, dismiss: false })
          }
          if (response === false) {
            return this.$toast('There is a problem with your amounts', { error: true, dismiss: false })
          } else {
            this.$toast('You have refunded $' + response, { error: false, dismiss: false })
          }
          this.getReturned(this.$pathParameter())
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
      this.getReturns()
    }
  },
  events: {
    'search_term': function (term) {
      this.indexRequest.search_term = term
      this.pagination.current_page = 1
      this.getReturns()
    }
  },
  components: {
    'CpReturnedDetail': require('./partials/CpReturnedDetail.vue'),
    'CpConfirm': require('../../cp-components-common/CpConfirm.vue'),
    'CpReturnHistory': require('./CpReturnHistory.vue')
  }
}
</script>

<style lang="scss">
.returns-show-wrapper {
    textarea {
        border: 1px solid #eee;
    }
    .cp-button-standard {
        float: right;
        margin: 5px;
    }
}

</style>
