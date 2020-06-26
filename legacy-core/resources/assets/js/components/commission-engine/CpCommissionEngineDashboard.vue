<template>
    <div class="CpCommissionEngineDashboardWrapper">
      <cp-select-2
        v-if="batches.length > 0"
        :disabled="loading > 0"
        :options="batches"
        :key-value="{name: 'formatedstartdate', value: 'id'}"
        v-model="dynamicProps.batchid"
        @input="updateDashboard"></cp-select-2>
      <div class="cp-grid-standard">
        <div v-for="(item, index) in layout" :key="index" class="cp-cell-3" v-if="item.enabled !== false">
          <component v-if="!loading" :is="item.cleanedName" v-bind="dynamicProps" :title="item.name" :userid="userid"></component>
        </div>
      </div>
        <div class="align-center">
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading > 0">
        </div>
    </div>
</template>

<script id="CpCommissionEngineDashboard">
    const commission = require('../../resources/CommissionEngineAPIv0.js')
    const Auth = require('auth')
    const Moment = require('moment')
    module.exports = {
      routing: [{
        name: 'site.CpCommissionEngineDashboard',
        path: '/commission-engine/dashboard',
        meta: {
          title: 'Commission Engine'
        }
      }],
      data () {
        return {
          layout: [],
          layoutIds: [],
          dynamicProps: {
            myStatsLevelOne: {},
            myStats: {},
            layout: [],
            userInfor: {},
            batchid: '',
            leadershipBonus: null,
            mentorBonus: [],
            myfaststartbonus: [],
            levelbonus: [],
            retailBonus: {},
            mydsvperlevel: [],
            mylegs: []
          },
          Auth: Auth,
          userid: null,
          loading: 0,
          repCommissions: [],
          batches: [],
          selectedBatch: null
        }
      },
      created () {
        this.userid = Auth.getAuthId()
        this.dynamicProps.userid = this.userid.toString()
        this.getDashboardSettings()
      },
      methods: {
        getBatch () {
          this.loading++
          let request = {
            command: 'querybatches',
            systemid: '1',
            orderdir: 'desc',
            offset: '0',
            orderby: 'id',
            userid: this.userid.toString(),
            limit: '100'
          }
          commission.runCommand(request).then(response => {
            this.loading--
            if (response.success) {
              this.batches = response.batches
              this.batches.forEach(e => {
                e.formatedstartdate = Moment(e.startdate).format('MMMM YYYY')
              })
              this.dynamicProps.batchid = this.batches[0].id
              this.selectedBatch = this.batches[0]
              this.dynamicProps.batches = this.batches
              this.updateDashboard()
            } else {
              // TODO do something?
            }
          })
        },
        getDashboardSettings () {
          this.loading++
          commission.runCommand({
            command: 'settingsget',
            systemid: '1',
            varname: 'affiliatehome'
          }).then(response => {
            this.loading--
            if (response.success) {
              let json = JSON.parse(response.settings[0].value)
              this.layout = json.layout
              for (let k = 0; k < this.layout.length; k++) {
                this.layoutIds.push(this.layout[k].i)
                this.layout[k].cleanedName = this.setName(this.layout[k].i)
              }
              this.dynamicProps.layout = this.layout
              this.getBatch()
            } else {
              this.dynamicProps.layout = null
            }
          })
        },
        myStatsLevelOne () {
          this.loading++
          commission.runCommand({
            command: 'mystatslvl1',
            systemid: '1',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id'
          }).then(response => {
            this.loading--
            if (response.success) {
              for (let index = 0; index < response.userstatslvl1.length; index++) {
                if (this.dynamicProps.batchid == response.userstatslvl1[index].batchid) {
                  this.dynamicProps.myStatsLevelOne = response.userstatslvl1[index]
                }
              }
            } else {
              this.dynamicProps.myStatsLevelOne = {}
            }
          })
        },
        myStats () {
          this.loading++
          commission.runCommand({
            command: 'mystats',
            systemid: '1',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id'
          }).then(response => {
            this.loading--
            if (response.success) {
              for (let index = 0; index < response.userstats.length; index++) {
                if (this.dynamicProps.batchid == response.userstats[index].batchid) {
                  this.dynamicProps.myStats = response.userstats[index]
                }
              }
            } else {
              this.dynamicProps.myStats = {}
            }
          })
        },
        myTitle () {
          this.loading++
          commission.runCommand({
            command: 'mytitle',
            systemid: '1',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id',
            batchid: this.dynamicProps.batchid
          }).then(response => {
            this.loading--
            if (response.success) {
              this.dynamicProps.myTitle = response.mytitle
            } else {
              this.dynamicProps.myTitle = null
            }
          })
        },
        myLegs () {
          if (!this.layoutIds.includes('18')) {
            return
          }
          this.loading++
          commission.runCommand({
            command: 'mylegs',
            systemid: '1',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id',
            batchid: this.dynamicProps.batchid
          }).then(response => {
            this.loading--
            if (response.success) {
              this.dynamicProps.mylegs = response.mylegs
            } else {
              this.dynamicProps.mylegs = []
            }
          })
        },
        levelBonus () {
          if (this.selectedBatch == null || !this.layoutIds.includes('22')) {
            return
          }
          this.loading++
          commission.runCommand({
            command: 'mygensalesvolume',
            systemid: '1',
            commrulegroup: '1',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id',
            startdate: this.selectedBatch.startdate,
            enddate: this.selectedBatch.enddate,
            batchid: this.dynamicProps.batchid
          }).then(response => {
            this.loading--
            if (response.success) {
              this.dynamicProps.levelbonus = response.mygensalesvolume
            } else {
              this.dynamicProps.levelbonus = []
            }
          })
        },
        retailBonus () {
          if (this.selectedBatch == null || !this.layoutIds.includes('21')) {
            return
          }
          this.loading++
          commission.runCommand({
            command: 'mygensalesvolume',
            systemid: '1',
            commrulegroup: '-1',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id',
            startdate: this.selectedBatch.startdate,
            enddate: this.selectedBatch.enddate,
            batchid: this.dynamicProps.batchid
          }).then(response => {
            this.loading--
            if (response.mygensalesvolume && response.mygensalesvolume.length > 0) {
              this.dynamicProps.retailBonus = response.mygensalesvolume[0]
            } else {
              this.dynamicProps.retailBonus = {amount: 0}
            }
          })
        },
        leadershipBonus () {
          if (!this.layoutIds.includes('23')) {
            return
          }
          this.loading++
          commission.runCommand({
            command: 'mygensalesvolume',
            systemid: '1',
            commrulegroup: '2',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id',
            startdate: this.selectedBatch.startdate,
            enddate: this.selectedBatch.enddate,
            batchid: this.dynamicProps.batchid
          }).then(response => {
            this.loading--
            if (response.mygensalesvolume) {
              let total = 0.00
              response.mygensalesvolume.forEach((r) => { total += parseFloat(r.amount) })
              this.dynamicProps.leadershipBonus = total
            } else {
              this.dynamicProps.leadershipBonus = 0.00
            }
          })
        },
        myDSVPerLevel () {
          if (this.selectedBatch == null || !this.layoutIds.includes('17')) {
            return
          }
          this.loading++
          commission.runCommand({
            command: 'mydsvperlevel',
            systemid: '1',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id',
            startdate: this.selectedBatch.startdate,
            enddate: this.selectedBatch.enddate,
            batchid: this.dynamicProps.batchid
          }).then(response => {
            this.loading--
            if (response.mydsvperlevel) {
              this.dynamicProps.mydsvperlevel = response.mydsvperlevel
            } else {
              this.dynamicProps.mydsvperlevel = []
            }
          })
        },
        myFastStartBonus () {
          if (!this.layoutIds.includes('24')) {
            return
          }
          this.loading++
          commission.runCommand({
            command: 'myfaststartbonus',
            systemid: '1',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id',
            batchid: this.dynamicProps.batchid
          }).then(response => {
            this.loading--
            if (response.success) {
              this.dynamicProps.myfaststartbonus = response.myfaststartbonus[0]
            } else {
              this.dynamicProps.myfaststartbonus = {}
            }
          })
        },
        mentorBonus () {
          if (!this.layoutIds.includes('26')) {
            return
          }
          this.loading++
          commission.runCommand({
            command: 'mygensalesvolume',
            systemid: '1',
            commrulegroup: '3',
            userid: this.userid.toString(),
            orderdir: 'asc',
            offset: '0',
            limit: '100',
            orderby: 'id',
            batchid: this.dynamicProps.batchid
          }).then(response => {
            this.loading--
            if (response.mygensalesvolume) {
              this.dynamicProps.mentorBonus = response.mygensalesvolume
            } else {
              this.dynamicProps.mentorBonus = []
            }
          })
        },
        updateDashboard (batchId) {
          this.batches.forEach((b) => { if (b.id == batchId) { this.selectedBatch = b}})
          this.myTitle()
          this.myStatsLevelOne()
          this.myStats()
          this.myLegs()
          this.levelBonus()
          this.myDSVPerLevel()
          this.myFastStartBonus()
          this.leadershipBonus()
          this.retailBonus()
          this.mentorBonus()
        },
        setName (nameId) {
          switch (parseInt(nameId)) {
            case 1:
              return this.cleanName('My Personal Volume')
            case 2:
              return this.cleanName('Personally Sponsored Qualified')
            case 3:
              return this.cleanName('Site Sales') // Inventory Type ID Defined //
            case 4:
              return this.cleanName('My Team Volume')
            case 5:
              return this.cleanName('Level One Mentors') // Rank defined";
            case 6:
              return this.cleanName('Career Title')
            case 7:
              return this.cleanName('Enterprise Volume')
            case 8:
              return this.cleanName('Rank Name Legs') // Rank and generation defined //
            case 9:
              return this.cleanName('Current Title')
            case 10:
              return this.cleanName('Ringbomb Enterprise Volume')
            case 11:
              return this.cleanName('Personal Volume Retail')
            case 12:
              return this.cleanName('Team Volume Retail')
            case 13:
              return this.cleanName('Personal Item Count')
            case 14:
              return this.cleanName('Team Item Count')
            case 15:
              return this.cleanName('Affiliate Sales')
            case 16:
              return this.cleanName('Team Volume Wholesale')
            case 17:
              return this.cleanName('Downline Sales Volume') // mydsvperlevel
            case 18:
              return this.cleanName('My Legs') // mylegs
            case 19:
              return this.cleanName('My Legs Max') // mylegsmax
            case 20:
              return this.cleanName('Enterprise Volume Retail')
            case 21:
              return this.cleanName('Retail Bonus')
            case 22:
              return this.cleanName('Level Bonus')
            case 23:
              return this.cleanName('Leadership Bonus')
            case 24:
              return this.cleanName('Fast Start Bonus')
            case 25:
              return this.cleanName('Sponsor Bonus')
            case 26:
              return this.cleanName('Mentor Bonus')
            default:
              return 'unknown'
          }
        },
        cleanName (name) {
          name = name.toLowerCase().replace(/\s/g, '-')
          return name
        }
      }
    }
</script>

<style lang="scss">
.CpCommissionEngineDashboardWrapper {

}
</style>
