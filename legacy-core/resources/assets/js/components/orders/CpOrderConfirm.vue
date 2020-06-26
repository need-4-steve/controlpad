<template>
    <div class="eInvoice-wrapper">
        <section class="eInvoice-header">
            <h1>Your Order</h1>
        </section>
        <section class="eInvoice-table">
            <table class="cart-table">
                <tbody class="cart-body">
                    <thead>
                        <th data-label="image"></th>
                        <th data-label="title">Title</th>
                        <th data-label="Size">Size</th>
                        <th data-label="quantity">Quantity</th>
                        <th data-label="price">Retail Price</th>
                    </thead>
                    <tr v-for="item in order.items">
                        <td class="preview" data-label="image">
                            <span v-if="item.product.default_media">
                                <img :src="
                                item.product.default_media.url_md" class="preview">
                            </span>
                        </td>
                        <td data-label="Title">
                            <span>{{item.product.name}}</span>
                        </td>
                        <td data-label="Size">
                            <label class="qty">Size:</label>
                            <span>{{item.size}}</span>
                        <td data-label="Quantity">
                        <label class="qty">Quantity:</label>
                        <span>{{item.pivot.quantity}}</span>
                        </td>
                        <td data-label="Price">
                            <span>${{item.msrp.price}}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
        <section class="eInvoice-subtotal">
                <div class="line-wrapper">
                    <label>Discount</label>
                    <span v-text=" - order.discount | currency"></span>
                </div>
                <div class="line-wrapper">
                    <label>Subtotal</label>
                    <span v-text="order.subtotal | currency"></span>
                </div>
                <div class="line-wrapper">
                    <label>Shipping</label>
                    <span v-text="order.shipping | currency"></span>
                </div>
                <div class="line-wrapper">
                    <label>Sales Tax</label>
                    <span v-text="order.tax | currency">
                    <!-- ${{order.tax}} -->
                    </span>
                </div>
        </section>
        <section class="eInvoice-total">
                <div class="line-wrapper">
                    <label class="total">Total</label>
                    <span v-text="order.total | currency"></span>
                </div>
                <div class="btn-wrapper">
                    <button class="btn" v-show="complete" @click="paymentModal = true, complete = false">Complete Your Order</button>
                </div>
            <section class="payment-modal" v-show="paymentModal">
              <cp-payment-form :payment-data="paymentObject" :validation-errors="validationErrors"></cp-payment-form>
            </section>
        </section>
        <section class="eInvoice-footer">
            <img v-bind:src="logo" class="footer-icon">
            <p>This is an important notification about your purchase from {{$_settings->getGlobal('company_name', 'value')}}</p>
            <p>{{$_settings->getGlobal('address', 'value')}}</p>
        </section>
    </div>
</template>

<script>
module.exports = {
  data: function () {
    return {
      order: {
        items: [],
        subtotal: '',
        total: '',
        tax: '',
        shipping: '',
        retail: '',
        discount: ''
      },
      logo: this.$getGlobal('back_office_logo').value
    }
  },
  props: {
    orderDetails: {
      type: Object,
      required: true
    }
  },
  methods: {
    submitPayment: function () {
      Pay(this.paymentObject)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, {error: true})
          }
        })
    }
  },
  components: {
    'CpPaymentForm': require('../payment/CpPaymentForm.vue')
  }
}
</script>
<style lang="sass">
@import "resources/assets/sass/var.scss";
    .eInvoice-wrapper {
        position: relative;
        max-width: 768px;
        margin: 0 auto;
        .eInvoice-header {
            text-align: center;
        }
        .eInvoice-table {
            margin-top: 50px;
            padding: 10px;
        }
        .eInvoice-subtotal {
            padding: 10px;
            border-top: solid 1px #eee;
            border-bottom: solid 1px #eee;
        }
        .eInvoice-total {
            padding: 10px;
            font-weight: 500;
            .btn {
                margin: 20px 0 0;
            }
        }
        .eInvoice-footer {
            margin-top: 100px;
            .footer-icon {
                margin: 20px auto;
                width: 40px;
            }
            text-align: center;
            p {
                font-size: 14px;
                margin: 15px auto;
            }
        }
        label {
            font-weight: 300;
            &.total {
                font-weight: 500;
            }
            &.qty {
                margin: 0 5px;
                display: none;
            }
        }
        .select-wrapper {
            position: relative;
            select {
                height: 100%;
                width: 100%;
                height: 30px;
                background: $cp-lighterGrey;
                border: none;
                text-indent: 10px;
                margin: 5px 0;
                -webkit-appearance: none;
                -webkit-border-radius: 0;
            }
            &:after {
                position: absolute;
                right: 5px;
                top: 13px;
                font-family: "Linearicons";
                content: "\e93a";
                font-size: 10px;
                pointer-events: none;
            }
        }
        .btn-wrapper {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            background: $cp-main;
            color: #fff;
            text-align: center;
            padding: 5px;
        }
        .line-wrapper {
            display: flex;
            -webkit-display: flex;
            justify-content: space-between;
            -webkit-justify-content: space-between;
        }
        .payment-modal {
            display: flex;
            -webkit-display: flex;
            justify-content: center;
            -webkit-justify-content: center;
            width: 100%;
            background: rgba(255,255,255,.6);
            z-index: 10;
            padding: 50px 0 0;
            &:after {
                display: table;
                content: "";
                clear: both;
            }
        }
        .cart-table {
            width: 100%;
            .preview {
                width: 50px;
            }
            .qty {
                max-width: 50px;
                text-align: center;
            }
        }
    }
@media (max-width: 500px) {
    .eInvoice-wrapper {
        .eInvoice-table {
            margin-top: 0;
        }
        .select-wrapper {
            width: 65px;
            margin: 0 auto;
        }
        label {
            &.qty {
                display: initial;
            }
        }
        table {
            &.cart-table {
                thead {
                    display: none;
                }
                td {
                    display: block;
                    width: 100%;
                    text-align: center;
                    margin: 10px 0;
                    &.preview {
                        width: 100%;
                        img {
                            height: 300px;
                            width: auto;
                            margin: 0 auto;
                        }
                    }
                }
            }
        }
    }
}
</style>
