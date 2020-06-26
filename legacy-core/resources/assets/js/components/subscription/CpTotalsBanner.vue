<template lang="html">
  <div class="totals-banner-subscription">
    <span v-if="totalsTitle" class="totals-column totals-title">{{ totalsTitle }}</span>
    <span v-for="total in totals" class="totals-column">
      <span class="total-title">{{ total.title }}</span>
      <span v-if="total.amount">
        <span class="total-amount">{{ total.amount || 0 | currency }}</span>
      </span>
      <span v-else>
          <span class="total-amount">{{ total.count || 0 }}</span>
      </span>
    </span>
  </div>
</template>

<script>
module.exports = {
  props: {
    totals: {
      type: Array,
      required: true
    },
    totalsTitle: {
      type: String,
      required: false
    }
  },
  mounted: function () {
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.totals-banner-subscription {
  display: flex;
  padding: 15px 0px;
  margin-top: 28px;
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
