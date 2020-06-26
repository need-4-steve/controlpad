<template>
  <div id="mcomm-downline-report">
    <h3 class="title">{{ currentUser }}'s Downline Report</h3>
    <data-table
      :header-fields="headerFields"
      :sort-field="sortField"
      :sort="sort"
      :data="data || []"
      :is-loading="isLoading"
      :css="datatableCss"
      not-found-msg="Items not found"
      @onUpdate="dtUpdateSort"
      track-by="name"
      tableHeight="400px"
    ><span slot="created:header">Created Custom</span>
      <!-- custom header usgin string -->
      <template v-slot:UpdatedHeader>
        <span>Updated Custom</span>
      </template>
      <template v-slot:pagination>
        <pagination
          :page="currentPage"
          :total-items="totalItems"
          :items-per-page="itemsPerPage"
          :css="paginationCss"
          @on-update="changePage"
          @update-current-page="updateCurrentPage"
        />
      </template>
      <div class="items-per-page" slot="ItemsPerPage">
        <label>Items per page</label>
        <items-per-page-dropdown
          :list-items-per-page="listItemsPerPage"
          :items-per-page="itemsPerPage"
          :css="itemsPerPageCss"
          @on-update="updateItemsPerPage"
        />
      </div>
        
    </data-table>
  </div>
</template>

<script>
const Commission = require("../../resources/MCommEngineAPI.js");
const Moment = require("moment");
const Auth = require("auth");
const _ = require("lodash");
const orderBy = _.orderBy
const addZero = value => ("0" + value).slice(-2);
const formatDate = value => {
  if (value) {
    const dt = new Date(value);
    return `${addZero(dt.getDate())}/${addZero(
      dt.getMonth() + 1
    )}/${dt.getFullYear()}`;
  }
  return "";
};

module.exports = {
  routing: [
    {
      name: "site.CpMCommEngineDownLineReport",
      path: "/commission-engine/mcomm-downline-report-dt",
      meta: {
        title: "Downline Report"
      }
    }
  ],
  components: {
    'data-table': require('./mcomm/components/DataTable.vue'),
    'items-per-page-dropdown': require('./mcomm/components/ItemsPerPageDropdown.vue'),
    'pagination': require('./mcomm/components/Pagination.vue')
  },
  created(){
    this.mcomm();
  },
  data: function() {
    return {
      headerFields: [
        {
          name: "name",
          label: "Name",
          width: '200px',
          sortable: true
        },
        {
          name: "advisor_name",
          label: "Advisor Name",
          sortable: true
        },
        {
          name: "enrollment_date",
          label: "Enrollment Date",
          sortable: true,
            format: formatDate
        },
        {
          name: "level",
          label: "Level",
          sortable: true
        },
        {
          name: "careerTitle",
          label: "Career Title",
          sortable: true,
        },
        {
          name: "rank",
          label: "Rank",
          sortable: false
        },
        {
          name: "personalVolume",
          label: "Personal Volume",
          sortable: true
        },
        {
          name: "customerCount",
          label: "Customer Count",
          sortable: true
        },
        {
          name: "teamGroupVolume",
          label: "Team Volume",
          sortable: true
        },
        {
          name: "commissionableRetailVolume",
          label: "Enterprise Volume",
          sortable: true
        },
      ],
      initialData:[],
      Auth: Auth,
      isLoading: true,
      currentUser: Auth.getClaims().fullName,
      userid: Auth.getAuthId().toString(),
      data: [],
      datatableCss: {
        table: 'table table-bordered table-hover table-striped table-center',
        th: 'header-item',
        thWrapper: 'th-wrapper',
        thWrapperCheckboxes: 'th-wrapper checkboxes',
        arrowsWrapper: 'arrows-wrapper',
        arrowUp: 'arrow up',
        arrowDown: 'arrow down',
        footer: 'footer'
      },
      paginationCss: {
        paginationItem: 'pagination-item',
        moveFirstPage: 'move-first-page',
        movePreviousPage: 'move-previous-page',
        moveNextPage: 'move-next-page',
        moveLastPage: 'move-last-page',
        pageBtn: 'page-btn',
      },
      itemsPerPageCss: {
        select: 'item-per-page-dropdown'
      },
      sort: "asc",
      sortField: "name",
      listItemsPerPage: [5, 10, 20, 50, 100],
      itemsPerPage: 10,
      currentPage: 1,
      totalItems: 0,
      createHeaderName: "created:header"
    };
  },
  methods: {
    dtUpdateSort: function({ sortField, sort }) {
      const sortedData = _.orderBy(this.initialData, [sortField],[sort]);
      const start = (this.currentPage -1) * this.itemsPerPage;
      const end = this.currentPage * this.itemsPerPage;
      this.data = sortedData.slice(start, end)
      console.log('load data based on new sort', this.currentPage)
    },
    updateItemsPerPage: function(itemsPerPage) {
      this.itemsPerPage = itemsPerPage
      if (itemsPerPage >= this.initialData.length) {
        this.data = this.initialData
      } else {
        this.data = this.initialData.slice(0, itemsPerPage)
      }
      console.log('load data with new items per page number', itemsPerPage)
    },
    changePage: function(currentPage) {
      this.currentPage = currentPage
      const start = (currentPage -1) * this.itemsPerPage;
      const end = currentPage * this.itemsPerPage;
      this.data = this.initialData.slice(start, end)
      console.log('load data for the new page', currentPage)
    },
    updateCurrentPage: function(currentPage) {
      this.currentPage = currentPage
      console.log('update current page without need to load data', currentPage)
    },
    mcomm: function() {
      return Commission.mcommDownline(this.userid)
        .then(response => {
          this.initialData = response;
            this.data=this.initialData.slice(0, 10)
            this.totalItems=this.initialData.length
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    goBack() {
      this.$router.go(-1);
    }
  }
}
</script>
<style>
#mcomm-downline-report {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
#mcomm-downline-report .title {
  margin-bottom: 30px;
}
#mcomm-downline-report .items-per-page {
  height: 100%;
  display: flex;
  align-items: center;
  color: #337ab7;
}
#mcomm-downline-report .items-per-page label {
  margin: 0 15px;
}
/* Datatable CSS */
.v-datatable-light {
  width: 1167px;
}
.v-datatable-light .header-item {
  cursor: pointer;
  color: #337ab7;
  transition: color 0.15s ease-in-out;
}
.v-datatable-light .header-item:hover {
  color: #ed9b19;
}
.v-datatable-light .header-item.no-sortable{
  cursor: default;
}
.v-datatable-light .header-item.no-sortable:hover {
  color: #337ab7;
}
.v-datatable-light .header-item .th-wrapper {
  display: flex;
  width: 100%;
  height: 100%;
  font-weight: bold;
}
.v-datatable-light .header-item .th-wrapper.checkboxes {
  justify-content: center;
}
.v-datatable-light .header-item .th-wrapper .arrows-wrapper {
  display: flex;
  flex-direction: column;
  margin-left: 10px;
  justify-content: space-between;
}
.v-datatable-light .header-item .th-wrapper .arrows-wrapper.centralized {
  justify-content: center;
}
.v-datatable-light .arrow {
  transition: color 0.15s ease-in-out;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
}
.v-datatable-light .arrow.up {
  border-bottom: 8px solid #337ab7;
}
.v-datatable-light .arrow.up:hover {
  border-bottom: 8px solid #ed9b19;
}
.v-datatable-light .arrow.down {
  border-top: 8px solid #337ab7;
}
.v-datatable-light .arrow.down:hover {
  border-top: 8px solid #ed9b19;
}
.v-datatable-light .footer {
  display: flex;
  justify-content: space-between;
  width: 500px;
}
/* End Datatable CSS */
/* Pagination CSS */
 .v-datatable-light-pagination {
    list-style: none;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin: 0;
    padding: 0;
    width: 300px;
    height: 30px;
  }
  .v-datatable-light-pagination .pagination-item {
    width: 30px;
    margin-right: 5px;
    font-size: 16px;
    transition: color 0.15s ease-in-out;
  }
  .v-datatable-light-pagination .pagination-item.selected {
    color: #ed9b19;
  }
  .v-datatable-light-pagination .pagination-item .page-btn {
    background-color: transparent;
    outline: none;
    border: none;
    color: #337ab7;
    transition: color 0.15s ease-in-out;
  }
  .v-datatable-light-pagination .pagination-item .page-btn:hover {
    color: #ed9b19;
  }
  .v-datatable-light-pagination .pagination-item .page-btn:disabled{
    cursor: not-allowed;
    box-shadow: none;
    opacity: .65;
  }
  /* END PAGINATION CSS */
  
  /* ITEMS PER PAGE DROPDOWN CSS */
  .item-per-page-dropdown {
    background-color: transparent;
    min-height: 30px;
    border: 1px solid #337ab7;
    border-radius: 5px;
    color: #337ab7;
  }
  .item-per-page-dropdown:hover {
    cursor: pointer;
  }
  /* END ITEMS PER PAGE DROPDOWN CSS */
</style>