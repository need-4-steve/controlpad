<template lang="html">
  <div class="coupon-wrapper">
    <div class="coupon-index box2">
      <button class="cp-button-standard cp-button-new" @click="couponModal=true">New Coupon</button>
      <cp-coupon-index :refreshPage="refreshCoupons"></cp-coupon-index>
    </div>
    <transition name="modal">
      <section class="cp-modal-standard" v-if="couponModal">
        <div class="cp-modal-body">
          <cp-coupon-create @closeModal="couponModal=false" @newCoupon="newCoupon()"></cp-coupon-create>
        </div>
      </section>
    </transition>
  </div>
</template>
<script>

module.exports = {
  name: 'CpCoupon',
  routing: [
    {
      name: 'site.CpCoupon',
      path: 'coupons/create',
      meta: {
        title: 'Coupons'
      },
      props: true
    }
  ],
  data () {
    return {
      coupons: [],
      couponModal: false,
      refreshCoupons: false
    }
  },
  computed: {},
  mounted () {},
  methods: {
    'newCoupon': function () {
      this.$toast('Coupon successfully created.', {
        dismiss: false
      })
      this.refreshCoupons = !this.refreshCoupons
    }
  },
  events: {},
  components: {
    'CpCouponCreate': require('../coupons/CpCouponCreate.vue'),
    'CpCouponIndex': require('../coupons/CpCouponIndex.vue')
  }
}
</script>

<style lang="scss">
// @import "resources/assets/sass/var.scss";
.coupon-wrapper {
    display: -ms-flex;
    display: -webkit-flex;
    display: flex;
    .cp-button-new {
        height: 30px;
    }
    .box1 {
        width: 100%;
    }
    .box2 {
        width: 100%;
        margin-top: 10px;
        #uses {
            width: 12%;
        }
        #description {
            width: 38%;
        }
    }
    .coupon-box-body {
        border: solid 1px #ddd;
        padding: 5px;
        input,
        select,
        textarea {
            background-color: $cp-grey;
        }
    }
}
@media (max-width: 960px) {
    .coupon-wrapper {
        display: block;
        .box1 {
            width: auto;
        }
        .box2 {
            width: auto;
        }
    }
}
</style>
