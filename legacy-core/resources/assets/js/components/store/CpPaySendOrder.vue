<template lang="html">
  <div class="text-area-wrapper">
  <h4>Send Order To: {{customer.email}} </h4>
      <div class="text-area-note">
        <textarea  placeholder="Add a note to customer email..." rows="4" v-model="note"></textarea>
      </div>
  </div>
</template>

<script>
module.exports = {
  data: function () {
    return {
      customer: {},
      note: null
    }
  },
  methods: {
    setCustomer (customer) {
      this.customer = customer
    },
    getNote () {
      return this.note
    }
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.text-area-wrapper {
  .text-area-note {
    textarea {
      width: 100%;
      border-color: white;
      padding: 10px;
    }
  }
}

</style>
