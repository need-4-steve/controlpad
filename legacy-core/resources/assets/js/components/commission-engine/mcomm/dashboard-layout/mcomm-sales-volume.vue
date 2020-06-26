<template>
  <div class="cp-dashboard-mcomm-sales-volume-wrapper">
    <div class="report-wrapper">
      <h2>Sales Period Performance Report for {{mcommValues.periodName}}</h2>
      <section class="stats">
        <div class="stats-group">
          <h4>Current Title</h4>
          <div class="stats" v-if="loading">
            <div class="stat odd">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="stats" v-else>
            <div class="stat odd">
              <p v-text="mcommValues.rank || '0.00'"></p>
              <p>MTD</p>
            </div>
          </div>
        </div>
        <div class="stats-group">
          <h4>Personal Volume</h4>
          <div class="stats" v-if="loading">
            <div class="stat even">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="stats" v-else>
            <div class="stat even">
              <p>${{mcommValues.personalVolume || '0.00'}}<p>
              <p>MTD</p>
            </div>
          </div>
        </div>
        <div class="stats-group">
          <h4>Customer Count</h4>
          <div class="stats" v-if="loading">
            <div class="stat odd">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="stats" v-else>
            <div class="stat odd">
              <p>{{mcommValues.customerCount || '0'}}</p>
              <p>MTD</p>
            </div>
          </div>
        </div>
      </section>
      <section class="stats">
        <div class="stats-group">
          <h4>Career Title</h4>
          <div class="stats" v-if="loading">
            <div class="stat even">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="stats" v-else>
            <div class="stat even">
              <p>{{mcommValues.careerTitle || '0.00'}}</p>
              <p>MTD</p>
            </div>
          </div>
        </div>
        <div class="stats-group">
          <h4>Team Group Volume (TGV)</h4>
          <div class="stats" v-if="loading">
            <div class="stat odd">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="stats" v-else>
            <div class="stat odd">
              <p>${{mcommValues.teamGroupVolume || '0.00'}}</p>
              <p>MTD</p>
            </div>
          </div>
        </div>
        <div class="stats-group">
          <h4>Commissionable Retail Volume</h4>
          <div class="stats" v-if="loading">
            <div class="stat even">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="stats" v-else>
            <div class="stat even">
              <p>${{mcommValues.commissionableRetailVolume || '0.00'}}</p>
              <p>MTD</p>
            </div>
          </div>
        </div>
      </section>
      <section class="stats">
        <div class="stats-group">
          <h4>Residual Bonus</h4>
          <div class="stats" v-if="loading">
            <div class="stat odd">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="stats" v-else>
            <div class="stat odd">
              <p v-text="mcommValues.residualBonus || '0.00'"></p>
              <p>MTD</p>
            </div>
          </div>
        </div>
        <div class="stats-group">
          <h4>Fast Start Commissions</h4>
          <div class="stats" v-if="loading">
            <div class="stat even">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="stats" v-else>
            <div class="stat even">
              <p>${{mcommValues.fastStartCommissions}}<p>
              <p>MTD</p>
            </div>
          </div>
        </div>
        <div class="stats-group">
          <h4>Retail Bonus</h4>
          <div class="stats" v-if="loading">
            <div class="stat odd">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="stats" v-else>
            <div class="stat odd">
              <p>${{mcommValues.retailBonus}}</p>
              <p>MTD</p>
            </div>
          </div>
        </div>
      </section>
      <section class="levels" v-if="$getGlobal('replicated_site').show || Auth.hasAnyRole('Admin')">
        <h4>Level Volumes</h4>
        <div class="level-volumes-container">
          <div class="grid-row" v-if="loading">
            <div class="stat odd">
              <p style="font-style:italics;">Loading...</p>
            </div>
          </div>
          <div class="grid-row" v-else>
            <div v-for="(item, name) in mcommValues.levelVolumes" class="grid-item" v-bind:key="`levelVolume-${name}`">
              <div class="grid-item-wrapper">
                <div class="grid-item-container">
                  <p>${{item || '0.00'}}</p>
                  <p>Level ${{name}}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
<script>
// include vue-d3 plugin
const McommAPI = require('../../../../resources/MCommEngineAPI.js')
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      Auth: Auth,
      userId: 0,
      _sales: [],
      loading: true,
      mcommValues:{
        rank:0,
        personalVolume:0,
        customerCount:0,
        careerTitle:0,
        teamGroupVolume:0,
        commissionableRetailVolume:0,
        residualBonus:0,
        fastStartCommissions:0,
        retailBonus:0,
        periodName:0,
        levelVolumes:[],
      }
    }
  },
  created(){
      this.userId = Auth.getAuthId()
  },
  props: ['period'],
  computed: {},
  mounted () {
    this.salesVolume()
  },
  methods: {
    salesVolume: function () {
      return McommAPI.salesVolume(this.userId)
        .then((response) => {
          // console.log(McommAPI)
          // console.log(response)
          this.handleSalesVolumeResponse(response)
          this.mcommValues.rank = response.rank
          this.mcommValues.personalVolume = response.personalVolume.toFixed(2)
          this.mcommValues.customerCount = response.customerCount.toFixed(2)
          this.mcommValues.careerTitle = response.careerTitle
          this.mcommValues.teamGroupVolume = response.teamGroupVolume.toFixed(2)
          this.mcommValues.commissionableRetailVolume = response.commissionableRetailVolume.toFixed(2)
          this.mcommValues.residualBonus = response.residualBonus.toFixed(2)
          this.mcommValues.fastStartCommissions = response.fastStartCommissions.toFixed(2)
          this.mcommValues.retailBonus = response.retailBonus.toFixed(2)
          this.mcommValues.periodName = response.periodName
          Object.keys(response.levelVolumes).map(function(key, index) {
            response.levelVolumes[key]=response.levelVolumes[key].toFixed(2);
          });
          this.mcommValues.levelVolumes = response.levelVolumes
        }).finally(()=>{this.loading=false})
    },
    // handle response
    handleSalesVolumeResponse: function (response) {
      if (response.error) {
        console.log(this.$toast)
        this.$toast(response.message, {error: true})
      }
    }
  },
  watch: { 
    period: function(newVal, oldVal) { // watch it
      this.loading=true
      console.log('Prop changed: ', newVal, ' | was: ', oldVal)
      McommAPI.mcommVolume(this.userId,newVal)
        .then((response) => {
          // console.log(McommAPI)
          // console.log(response)
          this.handleSalesVolumeResponse(response)
          this.mcommValues.rank = response.rank
          this.mcommValues.personalVolume = response.personalVolume.toFixed(2)
          this.mcommValues.customerCount = response.customerCount.toFixed(2)
          this.mcommValues.careerTitle = response.careerTitle
          this.mcommValues.teamGroupVolume = response.teamGroupVolume.toFixed(2)
          this.mcommValues.commissionableRetailVolume = response.commissionableRetailVolume.toFixed(2)
          this.mcommValues.residualBonus = response.residualBonus.toFixed(2)
          this.mcommValues.fastStartCommissions = response.fastStartCommissions.toFixed(2)
          this.mcommValues.retailBonus = response.retailBonus.toFixed(2)
          this.mcommValues.periodName = response.periodName
          Object.keys(response.levelVolumes).map(function(key, index) {
            response.levelVolumes[key]=response.levelVolumes[key].toFixed(2);
          });
          this.mcommValues.levelVolumes = response.levelVolumes
        }).finally(()=>{this.loading=false})
    }
  }
}
</script>
<style lang="scss">
  .cp-dashboard-mcomm-sales-volume-wrapper {
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
            .odd{
              background: $cp-LightBlue;
            }
            .even{
              background: $cp-main;
            }
          }
        }
      }
      section.levels{
          max-width: 1335px;
          margin: 0 auto;
          .grid-row {
            display: flex;
            flex-flow: row wrap;
            justify-content: center;
            .grid-item {
              height: 100px;
              flex-basis: 16%;
              -ms-flex: auto;
              width: 167px;
              position: relative;
              box-sizing: border-box;
              padding: 7px;
              border: #4e95f4 1px solid;
              .grid-item-wrapper {
                -webkit-box-sizing: initial;
                -moz-box-sizing: initial;
                box-sizing: initial;
                margin: 0;
                height: 100%;
                width: 100%;
                overflow: hidden;
                -webkit-transition: padding 0.15s cubic-bezier(0.4,0,0.2,1), margin 0.15s cubic-bezier(0.4,0,0.2,1), box-shadow 0.15s cubic-bezier(0.4,0,0.2,1);
                transition: padding 0.15s cubic-bezier(0.4,0,0.2,1), margin 0.15s cubic-bezier(0.4,0,0.2,1), box-shadow 0.15s cubic-bezier(0.4,0,0.2,1);
                position: relative;
                .grid-item-container {
                  text-align: center;
                  padding-top:16px;
                  height: 100%;
                  width: 100%;
                  position: relative;
                  p{
                    margin-top:0!important;
                    margin-bottom:0!important;
                  }
                  p:first-child{
                    font-weight:400;
                  }
                }
                .grid-item-container:nth-child(2n+1){
                  background: #b8d1f3;
                }
                .grid-item-container:nth-child(2n){
                  background: #dae5f4;
                }
              }
            }
          }
      }
    }
  }
</style>
