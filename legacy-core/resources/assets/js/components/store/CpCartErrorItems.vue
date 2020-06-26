<template lang="html">
        <section class="cart-items-wrapper">
            <div>
                <div class="cart-items-table">
                  <table>
                      <thead class="table-head">
                          <tr>
                              <th></th>
                              <th>Product</th>
                              <th>Variant</th>
                              <th>Option</th>
                              <th>Quantity</th>
                              <th>Item Price</th>
                              <th>Total Price</th>
                          </tr>
                      </thead>
                      <tbody>
                          <tr class="table-body" v-for="(line, index) in cart.lines" v-bind:class="[line.bundle_id ? 'cp-clickable' : '']" @click="showInfo(line)">
                            <td v-if="line.item_id && line.items[0].img_url"><img :src="line.items[0].img_url" /></td>
                            <td v-else-if="line.bundle_id && line.bundle_img_url"><img :src="line.bundle_img_url" /></td>
                            <td v-else></td>

                            <td class="errorText">{{ line.item_id ? line.items[0].product_name : line.bundle_name }}</td>
                            <td>{{ line.item_id ? line.items[0].variant_name : "" }}</td>
                            <td>{{ line.item_id ? line.items[0].option : "" }}</td>
                            <td>{{ line.quantity }}</td>
                            <td>{{ line.price | currency }}</td>
                            <td>{{ (line.price * line.quantity) | currency }}</td>
                          </tr>
                      </tbody>
                  </table>
                </div>
            </div>
            <div v-if="showItems">
              <transition name="modal">
                  <section class="cp-modal-standard" @click="showItems = false">
                    <div class="cp-modal-body" v-on:click.stop>
                    <h2 class="cp-modal-header">{{ selectedLine.bundle_name }}</h2>
                        <table>
                          <thead class="table-head">
                            <tr>
                              <th></th>
                              <th>Product</th>
                              <th>Variant</th>
                              <th>Option</th>
                              <th>Quantity</th>
                            </tr>
                          </thead>
                          <tbody>
                              <tr class="table-body" v-for="(item, index) in selectedLine.items">
                                <td v-if="item.img_url"><img :src="item.img_url" /></td>
                                <td v-else></td>

                                <td>{{ item.product_name }}</td>
                                <td>{{ item.variant_name }}</td>
                                <td>{{ item.option }}</td>
                                <td>{{ item.quantity ? item.quantity : 1 }}</td>
                              </tr>
                          </tbody>
                        </table>
                        <div class="buttton-wrapper">
                            <button class="cp-button-standard" @click="showItems = false">
                              Close
                            </button>
                        </div>
                      </div>
                  </section>
              </transition>
            </div>
        </section>
</template>

<script>
module.exports = {
  data () {
    return {
      showItems: false,
      selectedLine: null
    }
  },
  props: {
    cart: {
      required: true
    }
  },
  methods: {
    showInfo (line) {
      if (!line.bundle_id) {
        return;
      }
      this.selectedLine = line
      this.showItems = true
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpInputMask: require('../../cp-components-common/inputs/CpInputMask.vue')
  }
}
</script>

<style lang="scss">
.cart-items-wrapper {
  width: 100%;
      table {
        input {
            text-align: center !important;
            outline: inherit;
            outline-color: black;
            width: 40px;
        }
        width: 100%;
        margin: 0;
        padding: 0;
        border-collapse: collapse;
        border-spacing: 0;
        background: #fff;
        th, td {
          padding: 10px;
          text-align: center;
        }
        th {
          text-transform: uppercase;
          font-size: 14px;
          letter-spacing: 1px;
        }
      }
      tr {
        height:76px;
        padding: 5px;
        border-top: solid 1px #ddd;
      }
  .table-body {
      img {
          width:50px;
          height:50px;
      }
      .mdi::before {
        padding-top: 0px !important;
      }
  }
    }
    @media (max-width: 768px) {
  .cart-items-wrapper {
        .cart-items-table{
          overflow-x: auto;
          table {
            table-layout: fixed;
            width: 200% !important;
          }

    }
  }
}
</style>
