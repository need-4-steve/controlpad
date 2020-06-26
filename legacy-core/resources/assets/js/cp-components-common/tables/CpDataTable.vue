<template>
  <div class="cp-table-wrapper">
    <cp-table-controls
      v-if="options.tableControls"
      :search-box="options.searchBox"
      :date-picker="options.datePicker"
      :date-range="options.dateRange"
      :place-holder="options.searchPlaceHolder"
      :index-request="requestParams"
      :resource-info="pagination"
      :get-records="recallData"></cp-table-controls>
    <table class="cp-table-component">
      <thead>
        <tr>
          <th
            v-for="(item, index) in tableColumns"
            :key="index"
            @click=" item.sortable ? sortColumn(item.field, item.sortable): null">{{ item.header }}
              <span v-show="requestParams.column == item.field && item.sortable">
                <span v-show="asc"><i class='mdi mdi-sort-ascending'></i></span>
                <span v-show="!asc"><i class='mdi mdi-sort-descending'></i></span>
              </span>
            </th>
        </tr>
      </thead>
      <tbody>
      <tr v-for="row in tableData" :key="row.id">
        <td v-for="(item, index) in tableColumns" :key="index" :data-header="item.header" :class="item.htmlclass">
          <slot :name="item.field" :row="row">
            {{ dynamicFilter(row[item.field], item.filter) }}
          </slot>
        </td>
      </tr>
      </tbody>
    </table>
      <div v-if="loading" class="center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
      </div>
    <cp-pagination v-if="pagination" :pagination="pagination" :callback="recallData" :offset="2"></cp-pagination>
  </div>
</template>

<script id="CpDataTable">
  module.exports = {
    data () {
      return {
        asc: true
      }
    },
    props: {
      loading: {type: Boolean},
      pagination: {
        type: Object,
        required: false,
        default: null
      },
      recallData: {
        type: Function,
        required: false,
        default: function () {
          console.warn('Pagination is true. No recall function provided.')
        }
      },
      tableData: {
        type: Array,
        required: false,
        default () {
          return []
        }
      },
      tableColumns: {
        type: Array,
        required: false
      },
      requestParams: {
        // type: Object,
        required: false,
        default () {
          return null
        }
      },
      options: {
        type: Object,
        required: false,
        default () {
          return {
            tableControls: false,
            datePicker: false,
            dateRange: null,
            searchPlaceHolder: '',
            requestParams: {}
          }
        }
      }
    },
    methods: {
      dynamicFilter: function (value, filter) {
        if (filter) {
          let args = []
          if (filter.includes('|')) {
            args = filter.substring(filter.indexOf('|') + 1)
            args = args.split(',')
            filter = filter.substring(0, filter.indexOf('|'))
            for (let i = 0; i < args.length; i++) {
              if (args[i] === 'false') {
                args[i] = false
              } else if (args[i] === 'true') {
                args[i] = true
              }
            }
          }
          return this.applyFilter(value, filter, args)
        }
        return value
      },
      applyFilter (value, filter, args) {
        try {
          return window.Vue.filter(filter)(value, ...args)
        } catch (error) {
          console.warn('Not a valid filter string.')
          return value
        }
      },
      sortColumn (column, sortable) {
        if (sortable) {
          this.requestParams.column = column
          this.asc = !this.asc
          if (this.asc === true) {
            this.requestParams.order = 'asc'
            this.requestParams.sort_by = column
          } else {
            this.requestParams.sort_by = '-' + column
            this.requestParams.order = 'desc'
          }
          this.recallData()
        }
      }
    }
  }
</script>

<style lang="scss">
  .cp-table-wrapper {
    .center {
      align-self: center;
    }
    table.cp-table-component {
      width: 100%;
      border-collapse:collapse;
      td,th{
        padding: 10px;
        white-space: nowrap;
        text-align: left;
      }
      th {
        &:first-child {
          border-top-left-radius: 3px;
        }
        &:last-child {
          border-top-right-radius: 3px;
        }
        cursor: pointer;
        font-weight: 400;
        background-color: $cp-main;
        color: $cp-main-inverse;
      }
      thead{
        @media(max-width: 760px) {
          display: none;
        }
        tr{
          th{
            text-align: left;
          }
        }
      }
      tbody{
        tr{
          &:nth-child(even) {background: $cp-lighterGrey}
          @media(max-width: 760px) {
            display: flex;
            flex-direction: column;
            border: 1px solid $cp-lightGrey;
            border-radius: 3px;
            margin: 5px 0;
            padding: 5px;
            td::before{
              content: attr(data-header);
              display: block;
              font-weight: bold;
            }
            td {
              padding: 5px;
            }
          }
        }
      }
    }
  }
</style>
