<template>
    <section class="confirmation">
    <h1>Successful Payment!</h1>
    <p>Thanks for making an order with {{$getGlobal('company_name').value}}. We recently processed a successful payment from you. </p>
    <div class="details">
        <div class="space-between">
        <span>Customer: </span>
        <span>{{order.buyer_first_name}}&nbsp;{{order.buyer_last_name}}</span>
    </div>
        <div class="space-between">
        <span>Amount: </span>
        <span>{{order.total_price | currency}}</span>
    </div>
    <div class="space-between">
        <span>Method: </span>
        <span>Credit Card Payment</span>
    </div>
    <div class="space-between">
        <span>Receipt ID: </span>
        <span>{{order.receipt_id}}</span>
    </div>
    </div>
    <p>If you have any questions or concerns, please contact customer support.</p>
    </section>
</template>
<script>
module.exports = {
  props: {
    order: {
      type: Object,
      required: true
    }
  }
}
</script>
<style lang="sass">
@import "resources/assets/sass/var.scss";
.confirmation {
    padding-top: 50px;
    text-align: center;
    h1 {
        text-align: center;
        padding: 20px;
    }
    p {
        padding: 10px 20px;
    }
    .details {
        padding: 20px 10px;
        max-width: 400px;
        margin: 0 auto;
    }
    .space-between {
        display: flex;
        -webkit-display: flex;
        justify-content: space-between;
        -webkit-justify-content: space-between;
    }
}
</style>
