<template lang="html">
  <div class="cart-wrapper">
    <button class="cp-button-standard" @click="goToCart()"><i class="mdi mdi-cart"></i></button>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
    }
  },
  props: {
    cartPid: {
      type: String,
      required: true
    }
  },
  methods: {
    goToCart () {
      this.$router.push({path: '/carts/' + this.cartPid})
    }
  },
  components: {
  }
}
</script>

<style lang="scss">
.cart-wrapper {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  button {
    margin-bottom: 15px;
  }
}
</style>
