<template lang="html">
  <div class="rep-inventory-manager">
    <!-- INDEX OF REPS  -->
    <div class="index-of-reps" v-show="!showInventory">

        <cp-table-controls
        :date-picker="false"
        :index-request="indexRequest"
        :resource-info="pagination"
        :get-records="getReps"></cp-table-controls>

      <table class="cp-table-standard">
        <thead>
          <th @click="sortColumn('id')">ID
            <span v-show="indexRequest.column == 'id'">
              <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
              <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
          </th>
          <th @click="sortColumn('last_name')">Representative
            <span v-show="indexRequest.column == 'last_name'">
              <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
              <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
            </span>
          </th>
          <th>
            <!-- edit -->
          </th>
        </thead>
        <tbody v-if="!loading">
          <tr v-for="rep in reps" @click="">
            <td>{{ rep.id }}</td>
            <td>{{ rep.last_name }}, {{ rep.first_name }}</td>
            <td class="edit-button"><button class="cp-button-standard" @click="showInventory = true, selectedRep = rep">Edit</button></td>
          </tr>
        </tbody>
      </table>
      <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination
        :pagination="pagination"
        :callback="getReps"
        :offset="2"></cp-pagination>
      </div>
    </div>
    <!-- INVENTORY -->
    <div v-if="showInventory && selectedRep">
      <div class="rep-inventory-header">
        <button class="cp-button-standard back-button" @click="showInventory = false">BACK</button>
        <h3>{{ selectedRep.first_name }} {{ selectedRep.last_name }}'s Inventory</h3>
      </div>
      <div class="">
        <cp-inventory v-if="showInventory && selectedRep" :selectedRep="selectedRep"></cp-inventory>
      </div>
    </div>
  </div>
</template>

<script>
const Users = require('../../resources/users.js')

module.exports = {
  data () {
    return {
      reps: [],
      loading: false,
      selectedRep: null,
      showInventory: false,
      indexRequest: {
        column: 'last_name',
        role: 5, // rep - reseller and affiliate
        search_term: '',
        page: 1,
        order: 'asc',
        per_page: 15
      },
      pagination: {},
      asc: true,
      reverseSort: true
    }
  },
  mounted () {
    this.getReps()
  },
  methods: {
    getReps () {
      this.loading = true
      this.indexRequest.page = this.pagination.current_page
      Users.index(this.indexRequest)
        .then((response) => {
          this.loading = false
          if (!response.error) {
            this.pagination = response
            this.reps = response.data
          }
        })
    },
    sortColumn (column) {
      this.reverseSort = !this.reverseSort
      this.indexRequest.column = column
      this.asc = !this.asc
      if (this.asc === true) {
        this.indexRequest.order = 'asc'
      } else {
        this.indexRequest.order = 'desc'
      }
      this.getReps()
    }
  }
}
</script>

<style lang="scss" scoped>
.rep-inventory-manager {
  .rep-inventory-header {
    display: block;
    width: 100%;
    .back-button {
      float: right;
    }

  }
  .edit-button {
    text-align: right;
  }
}
</style>
