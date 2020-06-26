<template>
    <div class="returns-wrapper">
        <cp-return-history :order-id="order.id"></cp-return-history>

        <div v-if="returned.length >0">
            <h3>Pending Return</h3>
            <table class="cp-table-standard">
                <thead>
                    <tr>
                      <th>Date</th>
                      <th>Products Returned</th>
                      <th>Return Id</th>
                      <th>Price</th>
                      <th>Request Amount</th>
                      <th>Status</th>
                    </tr>
                </thead>
                <tbody v-for="returns in returned">
                    <tr v-for="lines in returns.lines">
                      <td>{{ returns.updated_at | cpStandardDate}}</td>
                       <td>{{ lines.name || '' }}</td>
                       <td>{{ returns.id }}</td>
                       <td>{{ lines.price | currency }}</td>
                       <td>{{ lines.quantity }}</td>
                       <td>{{ returns.status.name || '' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
          <h3>Request Return</h3>
            <table class="cp-table-standard">
                <thead>
                    <tr>
                        <th></th>
                        <th>Products</th>
                        <th>Variant</th>
                        <th>Option</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Amount to be Returned</th>
                        <th>Reason for Return</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody v-for="line in orderReturned.lines" v-if="line.quantity_remaining > 0 && line.type !== 'Bundle'">
                    <tr>
                      <td>
                        <input type="checkbox" v-model="line.checked" :true-value="1" :false-value="0">
                      </td>
                        <td>{{ line.name}}</td>
                        <td>{{ line.item.print }}</td>
                        <td>{{ line.item.size }}</td>
                        <td>{{ line.price | currency }}</td>
                        <td>{{ line.quantity }}</td>
                        <td>
                          <input
                          :placeholder="line.quantity_remaining + ' or less'"
                          type='number'
                          @keyup="quantityCheck(line)"
                          :max="line.quantity"
                          v-model='line.return_quantity'
                          required
                          :disabled="!line.checked">
                        </td>
                        <td>
                            <div v-for="(reason, index) in reasons">
                                <input
                                :name="'reason-selection' + line.id"
                                :id="reason.id"
                                type="radio"
                                :value="reason.id"
                                v-model="line.reasonForReturn"
                                :disabled="!line.checked">
                                <label for="reason.id"> {{reason.name}}</label>
                            </div>
                        </td>
                        <td>
                            <textarea v-model="line.comment" :disabled="!line.checked"></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="return-btn">
            <button @click="addReturnItems()" class="cp-button-standard send-request">Send Return Request</button>
        </div>
    </div>
</template>

<script>
const Returns = require('../../../resources/returns.js')
const Orders = require('../../../resources/orders.js')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      loading: false,
      returned: {},
      reasons: [],
      reasonForReturn: '',
      returnedQuantity: 0,
      totalReturned: 0,
      validationErrors: {},
      orderReturned: {}
    }
  },
  props: {
    order: {
      type: Object,
      required: true
    }
  },
  mounted () {
    this.previousReturn(this.order.id)
    this.returnReason()
    this.getOrder(this.order.receipt_id)
  },
  methods: {
    addReturnItems: function () {
      var request = { return_items: [] }
      var quantity = 0
      var items = JSON.parse(JSON.stringify(this.orderReturned))
      for (var i = 0; i <= items.lines.length - 1; i++) {
        if (items.lines[i].checked === undefined) {
          items.lines[i].checked = 0
          this.$set(items.lines[i], 'checked', 0)
        }
        if (items.lines[i].checked === 1) {
          if (items.lines[i].return_quantity === undefined) {
            quantity = 0
          } else {
            quantity = items.lines[i].return_quantity
          }
          if (quantity === 0) {
            this.$toast('Quantity cannot equal 0', { error: true })
            return
          }
          if (items.lines[i].reasonForReturn == null) {
            this.$toast('You need a reason for the return ' + items.lines[i].name, {error: true})
            return
          }
          request.return_items.push({
            orderline_id: items.lines[i].id,
            return_quantity: quantity,
            comments: items.lines[i].comment,
            order_id: items.id,
            reason_id: items.lines[i].reasonForReturn,
            customer: items.customer_id
          })
          items.lines[i].comment = ''
          items.lines[i].reasonForReturn = ''
          items.lines[i].checked = 0
          this.$set(items.lines[i], 'checked', 0)
          items.lines[i].quantity_remaining = items.lines[i].quantity_remaining - quantity
          items.lines[i].return_quantity = ''
        }
      }
      this.orderReturned = items
      this.sendRequest(request)
    },
    sendRequest: function (request) {
      Returns.return(request)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            this.$toast(response.message.return_items[0], { error: true })
          } else {
            this.$toast('Successful return request made.')
            this.previousReturn(this.order.id)
          }
        })
    },
    getOrder: function (receiptId) {
      Orders.show(receiptId)
        .then((response) => {
          if (response.error) {
          } else {
            this.orderReturned = response
            for (var i = 0; i < this.orderReturned.lines.length; i++) {
              this.$set(this.orderReturned.lines[i], 'checked', 0)
            }
          }
        })
    },
    previousReturn: function (orderId) {
      Returns.returned(orderId)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          } else {
            this.returned = response
          }
        })
    },
    returnReason: function () {
      Returns.reason()
        .then((response) => {
          this.reasons = response
        })
    },
    quantityCheck: _.debounce(function (line) {
      if (line.quantity_remaining < line.return_quantity) {
        this.$toast(line.quantity_remaining + ' is the max quantity that can be returned.', {dismiss: true, error: true})
        line.return_quantity = line.quantity_remaining
      }
    }, 800)
  },
  components: {
    'CpReturnHistory': require('../../returns/CpReturnHistory.vue')
  }
}
</script>

<style lang="sass">
    .returns-wrapper {
        .input[type=radio] {
            border: 0px;
            width: 10%;
            height: 12px;
        }
        .send-request {
          float: right;
        }
    }
</style>
