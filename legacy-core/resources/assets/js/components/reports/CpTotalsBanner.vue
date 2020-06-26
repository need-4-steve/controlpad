<template lang="html">
  <div class="totals-banner">
    <span v-if="totalsTitle" class="totals-column totals-title">{{ totalsTitle }}</span>
    <span v-for="total in totals" class="totals-column">
      <span class="total-title">{{ total.title }}</span>
      <span v-if="total.currency == false">
        <span class="total-amount">{{ total.amount || 0 }}</span>
      </span>
      <span v-else>
          <span v-if="!floor" class="total-amount">{{ total.amount || 0 | currency }}</span>
          <span v-if="floor" class="total-amount">{{ total.amount || 0 | currency('floor') }}</span>
      </span>
      </span>
  </div>
</template>

<script>
module.exports = {
  props: {
    totals: {
      type: Array,
      required: false
    },
    totalsTitle: {
      type: String,
      required: false
    },
    floor: {
      type: Boolean,
      default () {
        return false
      }
    }
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.totals-banner {
  display: flex;
  padding: 15px 0px;
  .totals-column {
    flex: 1;
    text-align: center;
    .total-title {
      display: block;
    }
    &.totals-title {
      font-size: 22px;
      color: $cp-darkFont;
      text-align: left;
    }
    .total-amount {
      display: block;
      font-size: 22px;
      font-weight: bold;
      color: $cp-darkFont;
    }
  }

}
@media (max-width: 767px) {
  .totals-banner{
    flex-direction: column;
    align-items: center;
  }
}
</style>
