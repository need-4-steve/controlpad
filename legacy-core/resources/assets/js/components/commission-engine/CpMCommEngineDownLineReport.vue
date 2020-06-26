<template>
  <div class="commission-engine-downline-report">
    <h3>{{ currentUser }}'s Downline Report</h3>
    <div class="overlay" v-if="isloading">
      <div class="lds-spinner">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
    <div v-else>
      <div id="app" class="container">
        <div class="box m-form">
          <label class="label">Filter By Name</label>
          <div class="control is-grouped">
            <p class="control is-expanded">
              <input
                class="input"
                v-model="searchItem"
                v-on:keyup="searchInTheList(searchItem)"
                type="text"
                placeholder="Find a person"
              />
              <span class="help is-dark">
                <strong>{{filteredItems.length }}</strong>
                of {{items.length }} person found
              </span>
            </p>
            <p class="control">
              <a
                class="button is-info"
                v-on:click="clearSearchItem"
                v-bind:class="{'is-disabled': searchItem==''}"
              >Clear</a>
            </p>
          </div>
        </div>
        <div class="box m-tags">
          <span>
            <strong>{{selectedItems.length}}</strong> person selected
          </span>
          <div class="m-tags-items">
            <a
              v-for="item in selectedItems"
              v-on:click="removeSelectedItem(item)"
              class="tag is-dark is-small"
            >
              {{item.name}}
              <button class="delete is-small"></button>
            </a>
          </div>
        </div>
        <nav class="pagination m-pagination">
          <a
            class="button"
            v-on:click="selectPage(pagination.currentPage-1)"
            v-bind:class="{'is-disabled': pagination.currentPage==pagination.items[0] || pagination.items.length==0}"
          >Previous</a>
          <a
            class="button"
            v-on:click="selectPage(pagination.currentPage+1)"
            v-bind:class="{'is-disabled': pagination.currentPage==pagination.items[pagination.items.length-1] || pagination.items.length==0}"
          >Next page</a>
          <ul>
            <li>
              <a
                class="button"
                v-on:click="selectPage(pagination.items[0])"
                v-bind:class="{'is-disabled': pagination.currentPage==pagination.items[0] || pagination.items.length==0}"
              >First</a>
            </li>
            <li class="is-space"></li>
            <li v-for="item in pagination.filteredItems">
              <a
                class="button"
                v-on:click="selectPage(item)"
                v-bind:class="{'is-info': item == pagination.currentPage}"
              >{{item}}</a>
            </li>
            <li class="is-space"></li>
            <li>
              <a
                class="button"
                v-on:click="selectPage(pagination.items[pagination.items.length-1])"
                v-bind:class="{'is-disabled': pagination.currentPage==pagination.items[pagination.items.length-1] || pagination.items.length==0}"
              >Last</a>
            </li>
          </ul>
        </nav>
        <div class="m-table">
          <table class="table is-bordered is-striped is-narrow">
            <tr>
              <th class="m-table-index">#</th>
              <th>Name</th>
              <th>Advisor</th>
              <th>Enroll Date</th>
              <th>Level</th>
              <th>Career Title</th>
              <th>Rank</th>
              <th>Personal Volume</th>
              <th>PSQ</th>
              <th>Team Volume</th>
              <th>Enterprise Volume</th>
            </tr>
            <tr v-for="(genes,indices) in items" v-bind:key="indices">
              <td>{{indices}}</td>
              <td>{{ genes.name }}</td>
              <td>{{ genes.advisor_name }}</td>
              <td>{{ genes.enrollment_date }}</td>
              <td>{{ genes.level }}</td>
              <td>{{genes.careerTitle}}</td>
              <td>{{genes.rank}}</td>
              <td>{{genes.personalVolume}}</td>
              <td>{{genes.customerCount}}</td>
              <td>{{genes.teamGroupVolume}}</td>
              <td>{{genes.commissionableRetailVolume}}</td>
              <!--<td>{{genes.enterpriseVolume}}</td>-->
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script id="CpMCommEngineDownLineReport">
const Commission = require("../../resources/MCommEngineAPI.js");
const Moment = require("moment");
const Auth = require("auth");
const _ = require("lodash");

module.exports = {
  routing: [
    {
      name: "site.CpMCommEngineDownLineReport",
      path: "/commission-engine/mcomm-downline-report",
      meta: {
        title: "Downline Report"
      }
    }
  ],
  data() {
    return {
      Auth: Auth,
      isloading: true,
      currentUser: Auth.getClaims().fullName,
      userid: Auth.getAuthId().toString(),
      searchItem: "",
      items: [],
      filteredItems: [],
      paginatedItems: [],
      selectedItems: [],
      pagination: {
        range: 5,
        currentPage: 1,
        itemPerPage: 5,
        items: [],
        filteredItems: []
      }
    };
  },
  mounted() {
    this.mcomm();
    this.filteredItems = this.items;
    this.buildPagination();
    this.selectPage(1);
  },
  methods: {
    mcomm: function() {
      return Commission.mcommDownline(this.userid)
        .then(response => {
          console.log(response);
          this.items = response;
          this.handleSalesVolumeResponse(response);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    goBack() {
      this.$router.go(-1);
    },
    // handle response
    handleSalesVolumeResponse: function(response) {
      this.isloading = false;
      if (response.error) {
        console.log(this.$toast);
        this.$toast(response.message, { error: true });
      }
    },
    clearSearchItem() {
      this.searchItem = undefined;
      this.searchInTheList("");
    },
    searchInTheList(searchText, currentPage) {
      if (_.isUndefined(searchText)) {
        this.filteredItems = _.filter(this.items, function(v, k) {
          return !v.selected;
        });
      } else {
        this.filteredItems = _.filter(this.items, function(v, k) {
          return (
            !v.selected &&
            v.name.toLowerCase().indexOf(searchText.toLowerCase()) > -1
          );
        });
      }
      this.filteredItems.forEach(function(v, k) {
        v.key = k + 1;
      });
      this.buildPagination();

      if (_.isUndefined(currentPage)) {
        this.selectPage(1);
      } else {
        this.selectPage(currentPage);
      }
    },
    buildPagination() {
      let numberOfPage = Math.ceil(
        this.filteredItems.length / this.pagination.itemPerPage
      );
      this.pagination.items = [];
      for (var i = 0; i < numberOfPage; i++) {
        this.pagination.items.push(i + 1);
      }
    },
    selectPage(item) {
      this.pagination.currentPage = item;

      let start = 0;
      let end = 0;
      if (this.pagination.currentPage < this.pagination.range - 2) {
        start = 1;
        end = start + this.pagination.range - 1;
      } else if (
        this.pagination.currentPage <= this.pagination.items.length &&
        this.pagination.currentPage >
          this.pagination.items.length - this.pagination.range + 2
      ) {
        start = this.pagination.items.length - this.pagination.range + 1;
        end = this.pagination.items.length;
      } else {
        start = this.pagination.currentPage - 2;
        end = this.pagination.currentPage + 2;
      }
      if (start < 1) {
        start = 1;
      }
      if (end > this.pagination.items.length) {
        end = this.pagination.items.length;
      }

      this.pagination.filteredItems = [];
      for (var i = start; i <= end; i++) {
        this.pagination.filteredItems.push(i);
      }

      this.paginatedItems = this.filteredItems.filter((v, k) => {
        return (
          Math.ceil((k + 1) / this.pagination.itemPerPage) ==
          this.pagination.currentPage
        );
      });
    },
    selectItem(item) {
      item.selected = true;
      this.selectedItems.push(item);
      this.searchInTheList(this.searchItem, this.pagination.currentPage);
    },
    removeSelectedItem(item) {
      item.selected = false;
      this.selectedItems.$remove(item);
      this.searchInTheList(this.searchItem, this.pagination.currentPage);
    }
  }
};
</script>

<style lang='scss' scoped>
.commission-engine-downline-report {
  .m-pagination {
    .is-space {
      width: 20px;
    }
  }

  .m-table {
    margin-top: 20px;

    tr th {
      background: #c0c0c0;
      color: #393939;
    }
  }

  .m-table-index {
    width: 20px;
  }

  .m-tags-items {
    margin-top: 5px;
    height: 60px;
    overflow-y: scroll;

    .tag {
      margin-bottom: 5px;
      margin-right: 3px;
    }
  }

  table {
    font-family: "Open Sans", sans-serif;
    width: 750px;
    border-collapse: collapse;
    border: 3px solid #44475c;
    margin: 10px 10px 0 10px;
  }

  table th {
    text-transform: uppercase;
    text-align: left;
    background: #44475c;
    color: #fff;
    padding: 8px;
    min-width: 30px;
  }

  table td {
    text-align: center;
    padding: 8px;
    border-right: 2px solid #7d82a8;
  }
  table td:last-child {
    border-right: none;
  }
  table tbody tr:nth-child(2n) td {
    background: #d4d8f9;
  }
  .overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    display: flex;
    align-content: center;
    align-items: center;
    justify-content: center;
    justify-items: center;
    background-color: rgba(0, 0, 0, 0.3);
    .lds-spinner {
      color: official;
      display: inline-block;
      position: relative;
      width: 64px;
      height: 64px;
    }
    .lds-spinner div {
      transform-origin: 32px 32px;
      animation: lds-spinner 1.2s linear infinite;
    }
    .lds-spinner div:after {
      content: " ";
      display: block;
      position: absolute;
      top: 3px;
      left: 29px;
      width: 5px;
      height: 14px;
      border-radius: 20%;
      background: #fff;
    }
    .lds-spinner div:nth-child(1) {
      transform: rotate(0deg);
      animation-delay: -1.1s;
    }
    .lds-spinner div:nth-child(2) {
      transform: rotate(30deg);
      animation-delay: -1s;
    }
    .lds-spinner div:nth-child(3) {
      transform: rotate(60deg);
      animation-delay: -0.9s;
    }
    .lds-spinner div:nth-child(4) {
      transform: rotate(90deg);
      animation-delay: -0.8s;
    }
    .lds-spinner div:nth-child(5) {
      transform: rotate(120deg);
      animation-delay: -0.7s;
    }
    .lds-spinner div:nth-child(6) {
      transform: rotate(150deg);
      animation-delay: -0.6s;
    }
    .lds-spinner div:nth-child(7) {
      transform: rotate(180deg);
      animation-delay: -0.5s;
    }
    .lds-spinner div:nth-child(8) {
      transform: rotate(210deg);
      animation-delay: -0.4s;
    }
    .lds-spinner div:nth-child(9) {
      transform: rotate(240deg);
      animation-delay: -0.3s;
    }
    .lds-spinner div:nth-child(10) {
      transform: rotate(270deg);
      animation-delay: -0.2s;
    }
    .lds-spinner div:nth-child(11) {
      transform: rotate(300deg);
      animation-delay: -0.1s;
    }
    .lds-spinner div:nth-child(12) {
      transform: rotate(330deg);
      animation-delay: 0s;
    }
    @keyframes lds-spinner {
      0% {
        opacity: 1;
      }
      100% {
        opacity: 0;
      }
    }
  }
  display: flex;
  justify-content: center;
  flex-direction: column;
  .custom-table-navigation {
    display: flex;
    justify-content: space-between;
    .user {
      width: 50%;
      display: flex;
      justify-content: flex-start;
      flex-direction: row;
      button {
        height: 30px;
        margin: 0px 10px 0px 0px;
        padding: 0px;
        align-self: center;
      }
    }
    .navigation {
      display: flex;
      justify-content: flex-end;
      width: 50%;
      align-items: center;
      button {
        height: 30px;
        select {
          width: 100%;
        }
      }
    }
  }
}
</style>