<template>
    <div class="CpMCommEngineDashboardWrapper">
        <select class="form-control" @change="updatePeriod($event)">
            <option value="" selected disabled>Choose</option>
            <option v-for="p in periods" :value="p.period" :key="p.period">{{ p.periodName }}</option>
        </select>
        <!--:period="currentPeriod"-->
      <cp-dashboard-mcomm-sales-volume :period="currentPeriod"></cp-dashboard-mcomm-sales-volume>    
    </div>
</template>

<script id="CpMCommEngineDashboard">
    const commission = require('../../resources/MCommEngineAPI.js')
    const Auth = require('auth')

    module.exports = {
      routing: [{
        name: 'site.CpMCommEngineDashboard',
        path: '/commission-engine/mcomm-dashboard',
        meta: {
          title: 'Multi-Com Engine'
        }
      }],
      data () { 
          return {
                    Auth: Auth,
                    userid: null,
                    loading: 0,
                    currentPeriod: null,
                    periods:[]
                }
      },
      components: {
        CpDashboardMcommSalesVolume: require('./mcomm/dashboard-layout/mcomm-sales-volume.vue')
      },
      created () {
        this.userid = Auth.getAuthId()
          this.currentPeriod=commission.getCurrentPeriod()
          this.getHistory()
      },
      methods: 
      {
        getHistory(){
          commission.activeHistory(this.userid).then(response=>{
              console.log(response)
            this.periods=response
          })
        },
        updatePeriod(event){
          this.currentPeriod= event.target.options[event.target.options.selectedIndex].value
        //   this.$dispatch('changePeriod', this.currentPeriod)
        //   this.$emit('changePeriod', this.currentPeriod)
        console.log('updatePeriod(event)')
        console.log(event)
        console.log('this.currentPeriod=>'+this.currentPeriod)
          this.$emit('update:period', event)

         } 
      }
    }
</script>

<style lang="scss">
.CpMCommEngineDashboardWrapper {
}
</style>