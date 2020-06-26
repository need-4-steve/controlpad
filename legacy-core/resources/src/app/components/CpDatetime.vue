<script id="CpDatetime">
/**
 * NOTES:
 *
 * Display modes include 'years', 'month', 'time'
 */
const moment = require('moment')
const DISPLAYS = {date: 'MM/DD/YYYY', datetime: 'MM/DD/YYYY h:mm A', time: 'h:mm A'}
const OUTPUTS = {date: 'YYYY-MM-DD', datetime: 'YYYY-MM-DDTHH:mm:ss.SSS', time: 'h:mm A'}

module.exports = {
  props: {
    value: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default () {
        return 'date'
      },
      validator: value => {
        return ['date', 'datetime', 'time'].includes(value)
      }
    },
    placeholder: {
      type: String,
      default () {
        return DISPLAYS[this.type]
      }
    }
  },
  data () {
    return {
      displayMode: 'month',
      selected: moment(),
      hideUpBtn: false,
      popupVisible: false,
      years: [],
      month: [],
      hours: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
      minutes: ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60'],
      periods: ['AM', 'PM']
    }
  },
  created () {
    this.generateMonth()
    if (this.type === 'time') {
      this.hideUpBtn = true
      this.displayMode = 'time'
    }
  },
  mounted () {
    if (this.value) {
      this.$el.querySelector('input').value = moment(this.value, OUTPUTS[this.type]).format(DISPLAYS[this.type])
    }
  },
  methods: {
    onInput (e) {
      this.update(e.target.value)
    },
    update (value, format) {
      let mValue = value
      if (!moment.isMoment(mValue)) {
        mValue = moment(mValue, format)
      }
      if (mValue.isValid()) {
        this.$emit('input', mValue.format(OUTPUTS[this.type]))
      }
    },
    openPopup () {
      this.popupVisible = true
    },
    closePopup (value) {
      value = value || this.selected
      this.cancelPopup()
      if (value !== undefined) {
        this.$el.querySelector('input').value = value.format(DISPLAYS[this.type])
        this.update(value)
      }
    },
    cancelPopup () {
      this.popupVisible = false
      this.displayMode = 'month'
    },
    goUp () {
      switch (this.displayMode) {
        case 'month':
          this.generateYears()
          this.displayMode = 'year'
          this.hideUpBtn = true
          break
        case 'time':
          this.generateMonth()
          this.displayMode = 'month'
          break
      }
    },
    selectYear (year) {
      this.selected.year(year)
      this.displayMode = 'month'
      this.generateMonth()
    },
    prevMonth () {
      this.selected.add(-1, 'month')
      this.generateMonth()
    },
    nextMonth () {
      this.selected.add(1, 'month')
      this.generateMonth()
    },
    selectDay (date) {
      this.selected.year(date.year())
      this.selected.month(date.month())
      this.selected.date(date.date())
      if (this.type === 'date') {
        this.closePopup(this.selected)
      } else {
        this.displayMode = 'time'
      }
    },
    selectHour (hour) {
      hour = +hour
      const period = this.selected.format('A')
      if (period === 'AM' && hour === 12) {
        this.selected.set('hour', 0)
      } else if (period === 'PM') {
        this.selected.set('hour', hour + 12)
      } else {
        this.selected.set('hour', hour)
      }
      this.$forceUpdate()
    },
    selectMinute (minute) {
      this.selected.minute(minute)
      this.$forceUpdate()
    },
    selectPeriod (period) {
      if (period === 'AM' && this.selected.hour() === 12) {
        this.selected.hour(0)
      } else if (period === 'PM') {
        this.selected.hour(+this.selected.format('h') + 12)
      } else {
        this.selected.hour(this.selected.format('h'))
      }
      this.$forceUpdate()
      this.closePopup(this.selected)
    },
    generateYears (date) {
      date = this.selected || moment()
      date = (moment.isMoment(date) ? date : moment(date))
      const thisYear = date.year()
      const start = thisYear - 100
      const end = thisYear + 100
      const years = []
      for (let i = start; i <= end; i++) {
        years.push(i)
      }
      this.years = years
      setTimeout(() => {
        const pane = this.$el.querySelector('.pane-year')
        const year = this.$el.querySelector(`[data-year="${thisYear}"]`)
        pane.scrollTop = year.offsetTop - (year.offsetHeight * 2)
      })
    },
    generateMonth () {
      date = this.selected || moment()
      date = (moment.isMoment(date) ? date : moment(date)).clone()
      const start = date.clone().startOf('month').add(-1, 'day').startOf('week')
      const end = date.clone().endOf('month').add(1, 'day').endOf('week')
      const current = start.clone()
      const month = []
      let week = []
      const today = moment()
      while (!current.isAfter(end)) {
        week.push({
          date: current.clone(),
          number: current.date(),
          isCurrentMonth: current.month() === date.month(),
          isToday: current.isSame(today, 'day')
        })
        if (week.length > 6) {
          month.push(week)
          week = []
        }
        current.add(1, 'day')
      }
      this.month = month
    }
  },
  computed: {
    inputListeners () {
      return Object.assign({}, this.$listeners, {
        input: (event) => {
          this.$emit('input', event.target.value)
        }
      })
    }
  }
}
</script>
<template>
    <div class="scoped-component-datetime">
        <div class="input-wrapper">
            <input @input="onInput" class="cp-input-standard" :placeholder="placeholder" />
            <span class="mdi-calendar icon-button" @click="openPopup()"></span>
        </div>
        <cp-popup position="bottom" alignment="left" :visible="popupVisible" @cancel="cancelPopup()">
            <div class="calendar-header">
                <span class="mdi-chevron-up icon-button" @click="goUp()"></span>
                <div class="selected-date">{{ selected.format('MMM D, YYYY') }}</div>
                <div class="selected-time" v-if="type !== 'date'">{{ selected.format('h:mm a') }}</div>
            </div>
            <div v-if="displayMode === 'year'" class="pane-year">
                <div v-for="(year, index) in years"
                    class="year"
                    :key="index"
                    :data-year="year"
                    @click="selectYear(year)">
                    {{ year }}
                </div>
            </div>
            <div v-if="displayMode === 'month'" class="pane-month">
                <div class="month-tools">
                    <span class="mdi-chevron-left icon-button" @click="prevMonth()"></span>
                    <span class="mdi-chevron-right icon-button" @click="nextMonth()"></span>
                </div>
                <div class="month-header">
                    <span>Sun</span>
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                </div>
                <div v-for="(week, index) in month" :key="index" class="week">
                    <span v-for="(day, index) in week"
                        :key="index"
                        :class="{'other-month': !day.isCurrentMonth, 'is-today': day.isToday}"
                        @click="selectDay(day.date)">
                        {{ day.number }}
                    </span>
                </div>
            </div>
            <div v-if="displayMode === 'time'" class="pane-time">
                <div class="time-selectors">
                    <div class="hours">
                        <div v-for="(hour, index) in hours"
                            :key="index"
                            @click="selectHour(hour)"
                            :class="{'time-selected': hour === selected.format('h') }"
                            >{{ hour }}</div>
                    </div>
                    <div class="minutes">
                        <div v-for="(minute, index) in minutes"
                            :key="index"
                            @click="selectMinute(minute)"
                            :class="{'time-selected': minute === selected.format('mm')}"
                            >{{ minute }}</div>
                    </div>
                    <div class="periods">
                        <div v-for="(period, index) in periods"
                            :key="index"
                            @click="selectPeriod(period)"
                            :class="{'time-selected': period === selected.format('A')}"
                            >{{ period }}</div>
                    </div>
                </div>
                <div class="time-tools">
                    <button class="cp-button-standard" @click="closePopup()">Apply</button>
                </div>
            </div>
        </cp-popup>
    </div>
</template>
<style lang="scss">
    .scoped-component-datetime{
        display: inline-block;
        position: relative;

        .icon-button{
            cursor: pointer;
        }

        .input-wrapper{
            display: flex;
            align-items: center;

            input{
                flex: 1;
            }
            span{
                padding: 6px 8px;
            }
        }

        .calendar-header{
            display: flex;
            align-items: center;
            padding: 6px 8px;
            .selected-date,.selected-time{
                flex: 1;
                font-size: 22px;
                white-space: nowrap;
                text-align: center;
            }
        }

        .pane-year{
            height: 262px;
            overflow: auto;
            padding: 6px 8px;
            border: solid 1px $cp-lightGrey;
            .year{
                width: 306px;
                padding: 6px 8px;
                text-align: center;
                font-size: 18px;
                cursor: pointer;
                &:hover{
                    background: $cp-lightGrey;
                }
            }
        }

        .pane-month{
            span{
                display: inline-block;
            }
            .month-tools{
                display: flex;
                align-items: center;
                justify-content: flex-end;
                padding: 6px 8px;
            }
            .month-header{
                display: flex;
                flex-direction: row;
                background: $cp-lightGrey;
                span{
                    flex: 1;
                    padding: 6px 8px;
                    text-align: center;
                }
            }
            .week{
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                span{
                    flex: 1;
                    padding: 8px 8px;
                    text-align: center;
                    cursor: pointer;
                    &.other-month{
                        background: $cp-lighterGrey;
                        color: $cp-lightGrey;
                    }
                    &.is-today{
                        background: $cp-LightBlue;
                    }
                    &:hover{
                        background: $cp-lightGrey;
                    }
                }
            }
        }

        .pane-time{
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 306px;
            padding: 6px 8px;
            border: solid 1px $cp-lightGrey;

            .time-selectors{
                display: flex;
                justify-content: center;
                height: 299px;
                .hours,.minutes,.periods{
                    flex: 1;
                    text-align: center;
                    overflow: auto;
                    padding: 6px 8px;
                    font-size: 24px;
                    cursor: pointer;

                    div{
                        padding: 6px 8px;
                        &:hover{
                            background: $cp-lightGrey;
                        }
                        &.time-selected{
                            background: $cp-LightBlue;
                        }
                    }
                }

                .periods{
                    display: flex;
                    flex-direction: column;
                    justify-content: space-evenly;
                }
            }

            .time-tools{
                display: flex;
                justify-content: flex-end;
                padding: 12px 8px 6px;
            }
        }
    }
</style>
