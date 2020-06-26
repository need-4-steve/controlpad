<template>
<div class="product-index-wrapper">
    <cp-tabs v-if="Auth.hasAnyRole('Admin', 'Superadmin')"
     :items="[
       { name: 'PRODUCTS', active: true },
       { name: 'BUNDLES', active: false }
     ]"
     :callback="selectProductType"></cp-tabs>
    <cp-tabs v-if="Auth.hasAnyRole('Rep')"
     :items="[
       { name: 'PRODUCTS', active: true },
     ]"
     :callback="selectProductType"></cp-tabs>
    <cp-table-controls
    :date-picker="false"
    :search-place-holder="'Search Products'"
    :index-request.sync="indexRequest"
    :resource-info.sync="pagination"
    :get-records="getProducts"></cp-table-controls>
    <table class="cp-table-standard">
        <thead>
            <th>Image</th>
            <th @click="sortColumn('name')">Name
                <span v-show="indexRequest.sort_by.includes('name')">
                    <span v-show="asc"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!asc"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            <th>Category
                <span v-show="indexRequest.sort_by.includes('category_name')">
                    <span v-show="asc"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!asc"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('updated_at')">Last Updated
                <span v-show="indexRequest.sort_by.includes('updated_at')">
                    <span v-show="asc"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!asc"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            <th>
                Delete
            </th>
        </thead>
        <tbody v-show="!loading">
            <tr v-for="product in products" track-by="$index">
                <td>
                    <a v-if="showProducts && product.images[0]" :href="`/products/${ product.id }/edit`">
                        <img class="thumb" :src="product.images[0].url">
                    </a>
                    <a v-if="showBundles && product.images[0]" :href="`/bundles/${ product.id }/edit`">
                        <img class="thumb" :src="product.images[0].url">
                    </a>
                </td>
                <td>
                    <a v-if="showProducts" :href="'/products/' + product.id + '/edit'">{{ product.name }}</a>
                    <a v-if="showBundles" :href="'/bundles/' + product.id + '/edit'">{{ product.name }}</a>
                    <br>
                    <span class="label label-default" v-for="(tag, index) in product.tags">
                      {{ tag.name }}
                    </span>
                </td>
                <td>
                    <span v-if="product.categories[0]">
                      {{ product.categories[0].name }} &nbsp;
                    </span>
                </td>
                <td>
                    {{ product.updated_at | cpStandardDate }}
                </td>
                <td>
                    <span><i class="mdi mdi-close cursor-pointer" @click="deleteProduct(product.id)"></i></span>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination :pagination="pagination" :callback="getProducts" :offset="2"></cp-pagination>
    </div>
</div>
</template>

<script>
    const Inventory = require('../../resources/InventoryAPIv0.js')
    const Auth = require('auth')

    module.exports = {
      name: 'CpProductIndex',
      routing: [
        {
          name: 'site.CpProductIndex',
          path: 'products',
          meta: {
            title: 'Products'
          },
          props: true
        }
      ],
      data: function () {
        return {
          Auth: Auth,
          loading: false,
          products: [],
          bundles: [],
          type: 'PRODUCTS',
          pagination: {
            current_page: 1
          },
          asc: false,
          indexRequest: {
            sort_by: '-updated_at',
            per_page: 15,
            search_term: '',
            page: 1
          },
          quantity: [],
          showProducts: true,
          showBundles: false,
          activeTab1: true,
          activeTab2: false
        }
      },
      mounted: function () {
        Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
        this.getProducts()
      },
      methods: {
        selectProductType (type) {
          this.type = type
          this.indexRequest.page = 1
          this.pagination.current_page = 1
          switch (type) {
            case 'PRODUCTS':
              this.showBundles = false
              this.showProducts = true
              break
            case 'BUNDLES':
              this.showProducts = false
              this.showBundles = true
              break
            default:
          }
          this.getProducts()
        },
        getProducts () {
          this.loading = true
          this.products = []
          if (this.type == 'PRODUCTS') {
            let params = JSON.parse(JSON.stringify(this.indexRequest))
            params.expands = ['product_images', 'categories']
            params.owner_id = Auth.getOwnerId()
            params.page = this.pagination.current_page
            Inventory.getProducts(params)
            .then((response) => {
              if (response.error) {
                this.$toast('Successfully updated.', { dismiss: false })
              }
              this.products = response.data
              this.pagination = response
              this.loading = false
            })
          } else if (this.type == 'BUNDLES') {
            this.indexRequest.per_page = 15
            let params = JSON.parse(JSON.stringify(this.indexRequest))
            params.page = this.pagination.current_page
            params.expands = ['bundle_images', 'categories']
            params.user_id = Auth.getOwnerId()
            Inventory.getBundles(params)
              .then((response) => {
                if (response.error) {
                  this.$toast('Successfully updated.', { dismiss: false })
                }
                this.products = response.data
                this.pagination = response
                this.loading = false
              })
          }

        },
        sortColumn: function (column) {
          this.asc = !this.asc
          this.indexRequest.sort_by = (this.asc ? '' : '-') + column
          this.getProducts()
        },
        deleteProduct: function (id) {
          this.loading = true
          Inventory.deleteProduct(id)
          .then((response) => {
            this.loading = false
            if (response.error) {
              this.$toast(response.message.message, { error: true })
              return
            }
            this.$toast('Product deleted')
            this.getProducts()
          })
        }
      },
      components: {
        CpTabs: require('../../cp-components-common/navigation/CpTabs.vue'),
        CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
      }
    }
</script>
<style lang="scss">
.product-index-wrapper {}
</style>
