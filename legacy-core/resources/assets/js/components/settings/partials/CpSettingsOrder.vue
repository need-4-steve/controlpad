<template lang="html">
    <div class="order-settings-wrapper">
        <div class="">
            <div class="panel item-list">
                <div class="panel-heading">
                    <h2 class="panel-title align-left">Order Status Settings</h2>
                </div>
                <table class="cp-table-standard desktop table-setting">
                    <thead>
                        <th>Position</th>
                        <th>Name</th>
                        <th>Display</th>
                        <th><!-- delete --></th>
                    </thead>
                    <tbody>
                        <tr v-for="(status, index) in orderStatuses">
                            <td><input class="align-center status-position input-class" type="number" v-model="status.position" @keyup="updateStatus(status)"></td>
                            <td><span v-if="status.default"><input class="input-class" type="text" v-model="status.name" disabled></span><span v-else><input class="input-class" type="text" v-model="status.name" @keyup="updateStatusName(status)"></span></td>
                            <td><input class="toggle-switch" type="checkbox" v-model="status.visible" @change="updateStatus(status)" @keyup="updateStatus(status)"></td>
                            <td v-if="!status.default" class="align:right"><i class="mdi mdi-close pointer" @click="deleteStatus(status.id, index)"></i></td>
                            <td v-else class="delete-column"></td>
                        </tr>
                        <tr style="background-color:white">
                            <td colspan="5"><button class="cp-button-standard" @click="addNew">+ Add New Status</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</template>

<script>
const Settings = require('../../../resources/settings.js')
const OrderStatus = require('../../../resources/order-status.js')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      orderStatuses: {}
    }
  },
  mounted: function () {
    this.getOrderStatus()
  },
  methods: {
    getOrderStatus: function () {
      OrderStatus.getIndex()
      .then((response) => {
        this.orderStatuses = response
      })
    },
    addNew: function () {
        var position = 0;
        for (index = 0; index < this.orderStatuses.length; ++index) {
            if (position < this.orderStatuses[index].position) {
                position = this.orderStatuses[index].position
            }
        }
        this.orderStatuses.push({
            id: null,
            name: "",
            default: false,
            position: Number(position) + 1,
            visible: false
        })
    },
    updateStatusName: _.debounce(function (status) {
        this.updateStatus(status)
    }, 1000),
    updateStatus: function(status) {
        if (!status.id && status.name !== "") {
            OrderStatus.create(status)
            .then((response) => {
                if (response.error) {
                    this.$toast(response.message, { error: true, dismiss: true })
                    return
                }
                this.$toast('Order Status Created', { error: false, dismiss: false })
                status.id = response.id
            })
        } else if(status.id) {
            OrderStatus.update(status.id, status)
            .then((response) => {
                if (response.error) {
                    this.$toast(response.message, { error: true, dismiss: true })
                    return
                }
                this.$toast('Order Status Updated', { error: false, dismiss: false })
            })
        }
    },
    deleteStatus: function (id, index) {
        if (id) {
            OrderStatus.delete(id)
            .then((response) => {
                if (response.error) {
                    this.$toast(response.message, { error: true, dismiss: true })
                    return
                }
                this.orderStatuses.splice(index, 1)
                this.$toast('Order Status Deleted', { error: false, dismiss: false })
            })
        } else {
            this.orderStatuses.splice(index, 1)
        }
    }
  },
  components: {
  }
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .order-settings-wrapper {
        .cp-table-standard {
            padding: 0px;
            .status-position {
                width: 30px;
            }
            .delete-column {
                width: 50px;
                align: right;
            }
        }
        .table-setting {
            padding: 0px;
            border-top: 0px;
            th {
                background-color: $cp-lightGrey;
                color: black;
            }
        }
    }
</style>
