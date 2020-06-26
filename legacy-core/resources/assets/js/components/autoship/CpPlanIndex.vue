<template lang="html">
  <div class="plan-index">
    <button class="cp-button-standard" @click="planModal = true, edit = false">New Plan</button>
    <cp-data-table
        :table-data="plans"
        :table-columns="tableColumns"
        :pagination="pagination"
        :recall-data="getPlans"
        :request-params="indexRequest"
        :options="{
            tableControls: true,
            datePicker: false,
        }">
        <span slot="view" slot-scope="{row}"><button @click="selectedPlan = row; planModal = true; edit = true" class="cp-button-standard">View</button></span>
        <span slot="title" slot-scope="{row}">{{row.title}}</span>
        <span slot="frequency" slot-scope="{row}">{{ row | frequency }} </span>
    <template slot="discounts" slot-scope="{row}">
        <table class="cp-table-inverse discount-table">
            <tr>
              <td>Minimum Item Quantity</td>
              <td>Discount</td>
            </tr>
            <tr v-for="(discount, index) in row.discounts" :key="index">
              <td>{{discount.min_quantity}}</td>
              <td>{{discount.percent}}%</td>
            </tr>
        </table>
    </template>
    <template slot="delete" slot-scope="{row}">
      <i v-if="Auth.hasAnyRole('Superadmin','Admin')" class="mdi mdi-close pointer" @click="confirmAndDelete(row.id)"></i>
    </template>
      <template slot="visibilities" slot-scope="{row}">
        <ul v-for="(visibilitie, index) in row.visibilities" :key="index">
          <li v-if="visibilitie.name === 'Rep'">{{$getGlobal('title_rep').value}}</li>
          <li v-else >{{visibilitie.name}}</li>
        </ul>
      </template>
    </cp-data-table>
    <cp-dialog :open="planModal" @close="clearModal()">
      <h2 slot="header">
        {{ edit && planModal ? 'Edit' : 'Create' }}
      </h2>
      <template slot="content">
        <cp-autoship-plan-form v-if="planModal" :plan="selectedPlan" @close="clearModal" @add-plan="addPlan" :edit="edit"></cp-autoship-plan-form>
      </template>
    </cp-dialog>
       <cp-confirm
        :message="'Are you sure you want to delete this plan? This can\'t be undone.'"
        v-model="showConfirm"
        :show="showConfirm"
        :callback="deletePlan"
        :onCancelled="undoDelete"
        :params="{id:planId}"></cp-confirm>
  </div>
</template>

<script>
const Autoship = require('../../resources/AutoshipAPIv0.js')
const Auth = require('auth')

module.exports = {
  routing: [
    { name: 'site.CpPlanIndex', path: '/autoship', meta: { title: '' } }
  ],
  data () {
    return {
      Auth,
      planModal: false,
      edit: false,
      showConfirm: false,
      planId: null,
      selectedPlan: {
        discounts: [],
        duration: 'Select a Duration',
        disable: true
      },
      indexRequest: {
        expands: ['visibilities'],
        show_disabled: 1
      },
      tableColumns: [
        { header: '', field: 'view' },
        { header: 'Title', field: 'title' },
        { header: 'Frequency', field: 'frequency' },
        { header: 'Discounts', field: 'discounts' },
        { header: 'Visable To', field: 'visibilities' },
        { header: '', field: 'delete' }
      ],
      plans: [],
      pagination: {
        per_page: 100
      }
    }
  },
  props: {},
  mounted () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getPlans()
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
  methods: {
    getPlans () {
      if (this.indexRequest.column) {
        if (this.indexRequest.order === 'asc') {
          this.indexRequest.sort_by = this.indexRequest.column
        } else {
          this.indexRequest.sort_by = '-' + this.indexRequest.column
        }
      }
      Autoship.getPlans(this.indexRequest)
        .then((response) => {
          this.plans = response.data
          this.pagination = response
        })
    },
    addPlan (plan) {
      this.plans.push(plan)
      this.planModal = false
      this.getPlans()
    },
    deletePlan () {
      Autoship.deletePlan(this.planId)
        .then((response) => {
          this.getPlans()
        })
    },
    undoDelete () {
      this.showConfirm = false
    },
    clearModal () {
      this.planModal = false
      this.selectedPlan = {
        discounts: [],
        duration: 'Select a Duration',
        disable: true
      }
    },
    confirmAndDelete (id) {
      this.planId = id
      this.showConfirm = true
    }
  }
}
</script>

<style lang="scss">
.plan-index {
   ul {
     margin: 0;
     list-style: none;
   }
   .discount-table {
    border-style: solid;
    border-width: 1px;
    background-color: white;
    td {
      background-color: white;
    }
  }
}
</style>
