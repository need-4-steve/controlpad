<template lang="html">
<div class="cp-table-controls">
  <div class="date-time-record-wrapper">
    <div class="table-date-picker">
      <span class="date-picker" v-if="datePicker">
        <cp-datetime v-model="dateRange.start_date" @input="getRecordsWithDelay()"></cp-datetime>
        <cp-datetime v-model="dateRange.end_date" @input="getRecordsWithDelay()"></cp-datetime>
      </span>
    </div>
    <span v-if="resourceInfo">
      <label>Total Records: </label> {{ resourceInfo.total }}
    </span>
  </div>
    <div class="table-control-wrapper">
    <div v-if="searchBox === true">
    <cp-search-box
      @search-term="runSearch"
      :placeholder="searchPlaceHolder"></cp-search-box>
    </div>
      <cp-select
      class="limit-select"
      label="Records per page"
      :hide-label="true"
      :options="[
      { name: '15', value: 15 },
      { name: '25', value: 25 },
      { name: '50', value: 50 },
      { name: '100', value: 100 }
      ]"
      v-model="indexRequest.per_page"
      @input="function (val) { indexRequest.per_page = val; getRecords() }"></cp-select>
  </div>
</div>
</template>

<script>
const _ = require('lodash')

module.exports = {
  props: {
    datePicker: {
      type: Boolean,
      required: false,
      default () {
        return false
      }
    },
    indexRequest: {
      required: true,
      type: Object
    },
    dateRange: {
      type: Object
    },
    resourceInfo: {
      type: Object,
      required: false,
      default () {
        return null
      }
    },
    getRecords: {
      type: Function,
      required: false,
      default () {
        // do nothing
      }
    },
    searchPlaceHolder: {
      type: String,
      default () {
        return 'Search'
      }
    },
    searchBox: {
      type: Boolean,
      required: false,
      default: true
    }
  },
  methods: {
    runSearch (val, clicked) {
      if (!clicked && val.length > 0 && val.length < 3) {
        return false
      }
      this.indexRequest.search_term = val
      this.indexRequest.current_page = 1
      this.getRecords()
    },
    getRecordsWithDelay: _.debounce(function () {
      this.indexRequest.current_page = 1
      this.resourceInfo.current_page = 1
      this.getRecords()
    }, 700)
  },
  components: {
    CpSearchBox: require('../inputs/CpSearchBox.vue'),
    CpSelect: require('../inputs/CpSelect.vue')
  }
}
</script>

<style lang="scss">
.cp-table-controls {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  .date-time-record-wrapper {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
      span {
        align-self: center;
        margin: 0px 5px;
    }
    .date-picker {
      display: flex;
      justify-content: space-between;
      i {
        width: 26px !important;
        align-self: center;
      }
      .mdi::before {
        padding: 0px;
        padding-top: 0px;
      }
    }
  }
  .table-control-wrapper {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    .limit-select {
      margin-right: 5px;
      select {
        margin-top: 0px;
        width: 60px;
        min-width: 60px;
      }
    }
  }
}

  @media (max-width: 476px) {
  .cp-table-controls{
    display: block;
    }
  }
@media (max-width: 768px) {
    .cp-table-controls {
      display: block;
      .table-control-wrapper {
        display: block;
        input {
          width: 100%;
        }
      }
      .cp-search-box {
            padding: 0px 0px 0px 10px;
          & > input {
        width: 80% !important;
      }
    }
    .date-time-record-wrapper {
      display: block;
      .date-picker {
        display: block;
        input {
          width: 100%;
        }
      }
    }
    .limit-select {
      select {
        width: 100%;
      }
    }
  }
}
</style>
