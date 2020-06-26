<template>
  <div class="cp-dashboard-sales-volume-wrapper">
    <div class="report-wrapper">
      <section class="stats">
        <div class="stats-group">
          <h4>Orders</h4>
          <div class="stats">
            <div class="stat today">
              <p v-text="newOrders.total || '0'"></p>
              <p>New</p>
            </div>
            <div class="stat mtd">
              <p v-text="newOrders.unshipped || '0'"></p>
              <p>Not Shipped</p>
            </div>
          </div>
        </div>
        <div class="stats-group">
          <h4>Sales Volume</h4>
          <div class="stats">
            <div class="stat today">
              <p>${{monthlySalesVolume.today}}</p>
              <p>Today</p>
            </div>
            <div class="stat mtd">
              <p>${{monthlySalesVolume.month}}<p>
              <p>MTD</p>
            </div>
          </div>
        </div>
        <div class="stats-group">
          <h4>Orders Volume</h4>
          <div class="stats">
            <div class="stat today">
              <p>${{monthlySalesVolume.today}}</p>
              <p>Today</p>
            </div>
            <div class="stat mtd">
              <p v-text="monthlyOrderVolume.month"></p>
              <p>MTD</p>
            </div>
          </div>
        </div>
      </section>
      <section class="chart" v-if="$getGlobal('replicated_site').show || Auth.hasAnyRole('Admin')">
        <h4>Last 12 Months Volume</h4>
        <svg id="salesGraph" viewBox="0 0 620 320"></svg>
        <div class="legend">
          <span class="sales">Sales Volume</span>
          <span class="orders">Orders Volume</span>
        </div>
      </section>
    </div>
    <cp-recent-announcements :announcements="announcements" :allLink="allLink"></cp-recent-announcements>
  </div>
</template>
<script>
// include vue-d3 plugin
const Dashboard = require('../../resources/dashboard.js')
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      Auth: Auth,
      _sales: [],
      loading: true,
      newOrders: {
        total: 0,
        unshipped: 0
      },
      monthlyOrderVolume: {
        today: 0,
        month: 0
      },
      monthlySalesVolume: {
        today: 0,
        month: 0
      },
      pageViews: {
        today: 0,
        month: 0
      }
    }
  },
  props: ['announcements', 'allLink'],
  computed: {},
  mounted () {
    this.salesVolume()
  },
  methods: {
      // use dashboard resource to pull sales
    make_x_gridlines: function (xScale) {
      return this.$d3.axisBottom(xScale).ticks(14)
    },
    make_y_gridlines (yScale) {
      return this.$d3.axisLeft(yScale).ticks(8)
    },
    salesVolume: function () {
      return Dashboard.salesVolume()
        .then((response) => {
          this.handleSalesVolumeResponse(response)
          this.newOrders = response.newOrders
          this.monthlyOrderVolume = response.monthlyOrderVolume
          this.monthlySalesVolume = response.monthlySalesVolume
          this.pageViews = response.pageViews

          var graph = this.$d3.select('#salesGraph')
          var WIDTH = 600
          var HEIGHT = 300
          var MARGINS = {
            top: 20,
            right: 20,
            bottom: 20,
            left: 50
          }
          var xScale = this.$d3.scaleTime().range([MARGINS.left, WIDTH - MARGINS.right])
                        .domain([new Date(this._sales.minDate), new Date(this._sales.maxDate)])
          var yScale = this.$d3.scaleLinear().range([HEIGHT - MARGINS.top, MARGINS.bottom])
                        .domain([this._sales.minVolume, this._sales.maxVolume])
          var xAxis = this.$d3.axisBottom().scale(xScale)
          var yAxis = this.$d3.axisLeft().scale(yScale)

            // define the fill area
          var area = this.$d3.area()
          .curve(this.$d3.curveBasis)
          .x(function (d) {
            return xScale(new Date(d.date))
          })
          .y0(HEIGHT - MARGINS.bottom)
          .y1(function (d) {
            return yScale(d.volume)
          })

            // append the headers
          graph.append('svg:g')
              .attr('transform', 'translate(0,' + (HEIGHT - MARGINS.bottom) + ')')
              .call(xAxis)

          graph.append('svg:g')
              .attr('transform', 'translate(' + (MARGINS.left) + ',0)')
              .call(yAxis)

        // function for making lines
          var volumeLine = this.$d3.line()
          .curve(this.$d3.curveBasis)
          .x(function (d) {
            return xScale(new Date(d.date))
          })
          .y(function (d) {
            return yScale(d.volume)
          })

          // sales area fill
          graph.append('path')
              .datum(this._sales.salesVolume)
              .attr('class', 'salesArea')
              .attr('d', area)

          // order area fill
          graph.append('path')
              .datum(this._sales.orderVolume)
              .attr('class', 'orderArea')
              .attr('d', area)

          // sales volume line
          graph.append('svg:path')
            .attr('d', volumeLine(this._sales.salesVolume))
            .attr('class', 'salesLine')

          // order volume line
          graph.append('svg:path')
              .attr('d', volumeLine(this._sales.orderVolume))
              .attr('class', 'orderLine')

          // x grid lines
          graph.append('g')
                  .attr('class', 'grid')
                  .attr('transform', 'translate(0,' + HEIGHT + ')')
                  .call(this.make_x_gridlines(xScale)
                      .tickSize(-HEIGHT)
                      .tickFormat('')
                  )
          // y grid lines
          graph.append('g')
                  .attr('class', 'grid')
                  .call(this.make_y_gridlines(yScale)
                      .tickSize(-WIDTH)
                      .tickFormat('')
                  )
        })
    },
    // handle response
    handleSalesVolumeResponse: function (response) {
      this.loading = false
      if (response.error) {
        this.$toast(response.message, {error: true})
      } else {
        this._sales = response
      }
    }
  },
  components: {
    CpRecentAnnouncements: require('./CpRecentAnnouncements.vue')
  }
}
</script>
<style lang="scss">
  .cp-dashboard-sales-volume-wrapper{
    display: flex;
    flex-direction: row;
    @media(max-width: 1360px){
      flex-direction: column;
    }
    .report-wrapper{
      flex: 1;
      margin-right: 20px;
      @media(max-width: 1360px){
        margin-right: 0;
      }

      section.stats{
        display: flex;
        flex-direction: row;
        @media(max-width: 790px){
          flex-direction: column;
        }

        .stats-group{
          flex: 1;
          display: flex;
          flex-direction: column;
          text-align: center;
          margin: 8px;

          h4{
            display: inline-block;
          }
          .stats{
            flex: 1;
            display: flex;
            flex-direction: row;

            .stat{
              flex: 1 1 50%;
              display: inline-block;
              white-space: nowrap;
              color: #fff;
              padding: 6px 10px;
              @media(max-width: 790px){
                flex: 1 1 50%;
              }
            }
            .today{
              background: $cp-LightBlue;
            }
            .mtd{
              background: $cp-main;
            }
          }
        }
      }
      section.chart{
        .legend{
          font-size: 12px;
          display: flex;
          justify-content: flex-end;
          span{
            margin: 6px 8px;
            &::before{
              display: inline-block;
              content: '';
              width: 20px;
              height: 20px;
              vertical-align: text-bottom;
              margin-right: 8px;
            }
            &.sales::before{
              background: blue;
            }
            &.orders::before{
              background: black;
            }
          }
        }
        #salesGraph{
          /**/.axis--x path {
            display: none;
          }
          /**/.orderLine {
            fill: none;
            stroke: $cp-NavyBlue;
            stroke-width: 1.5px;
          }
          /**/.orderArea {
            fill: $cp-NavyBlue;
            stroke-width: 0;
          }
          .salesLine {
            fill: none;
            stroke: $cp-LightBlue;
            stroke-width: 1.5px;
          }
          .salesArea {
            fill: $cp-LightBlue;
            stroke-width: 0;
          }
          /**/rect {
            stroke-width: 2;
          }
          .grid line {
            stroke: lightgrey;
            stroke-opacity: .7;
            shape-rendering: crispEdges;
          }
          .grid path {
            stroke-width: 0;
          }
        }
      }
    }
  }
</style>
