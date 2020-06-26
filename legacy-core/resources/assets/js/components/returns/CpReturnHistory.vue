<template lang="html">
    <div class="return-history-wrapper">
      <div v-show='returnHistory.length > 0'>
          <h3>Order Return History</h3>
          <table  class="cp-table-standard" >
              <thead>
                  <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Return Id</th>
                    <th>Previous Status</th>
                    <th>Status</th>
                    <th>Notes</th>
                  </tr>
              </thead>
              <tbody v-for= "returns in returnHistory">
                  <tr v-for="history in returns.history">
                       <td>{{ history.created_at | cpStandardDate}}</td>
                       <td>{{ returns.returnline.name }}</td>
                       <td>{{ returns.id}}</td>
                       <td>{{ history.old_status.name}}</td>
                       <td>{{ history.new_status.name }}</td>
                       <td>{{ history.comments }}</td>
                  </tr>
              </tbody>
          </table>
      </div>
    </div>
</template>

<script>
const Returns = require('../../resources/returns.js')

module.exports = {
  data: function () {
    return {
      returnHistory: {}
    }
  },
  props: {
    orderId: {
      type: Number,
      required: true
    }
  },

  mounted: function () {
    this.getReturnHistory(this.orderId)
  },
  methods: {
    getReturnHistory: function (orderId) {
      Returns.history(orderId)
      .then((response) => {
        this.returnHistory = response
      })
    }
  },
  components: {
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
.return-history-wrapper {

}
</style>
