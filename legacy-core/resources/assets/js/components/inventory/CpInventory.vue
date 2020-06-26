<template>
  <div id="inventory-wrapper">
    <div class="action-btn-wrapper" v-if="Auth.hasAnyRole('Admin', 'Superadmin')">
      <a class="cp-button-standard" @click="instructionsModal = true">Import Inventory</a>
      <a class="cp-button-standard"  download href='/api/v1/inventory/csv-export?template=0'>Export Inventory</a>
      <router-link class="cp-button-standard" to='/products/create'>New Product</router-link>
    </div>
    <cp-tabs :items="tabs" :callback="changeTab"></cp-tabs>
    <cp-table-controls
    :date-picker="false"
    :index-request="indexRequest"
    :resource-info="pagination"
    :get-records="getItems"></cp-table-controls>

    <div class="item-list item-list-container">
      <div class="inventory-header item-container">
        <div class="column-name">Image</div>
        <div class="group-container">
          <div class="item-info-container">
            <div class="cp-clickable" @click="updateSort('product_name')">
              <div class="column-name">Product<i v-show="indexRequest.sort_by.includes('product_name')" :class="['mdi', '', indexRequest.sort_by.indexOf('-') == 0 ? 'mdi-sort-descending' : 'mdi-sort-ascending']"></i></div>
            </div>
            <div class="column-name">Variant</div>
            <div class="column-name">Prices</div>
          </div>
          <div class="item-quantity-container">
            <div class="column-name">Size</div>
            <div class="cp-clickable" @click="updateSort('quantity_available')">
              <div class="column-name">Quantity<i v-show="indexRequest.sort_by.includes('quantity_available')" :class="['mdi', '', indexRequest.sort_by.indexOf('-') == 0 ? 'mdi-sort-descending' : 'mdi-sort-ascending']"></i></div>
            </div>
            <div class="column-name">+/- Inventory</div>
            <div class="column-name" v-if="!Auth.hasAnyRole('Rep') || $getGlobal('replicated_site').show">Hide in Store</div>
          </div>
        </div>
      </div>
      <div v-for="(item, index) in items" v-if="!loading" class="item-container">
        <div>
          <img v-if="item.variant.images.length > 0" :src="item.variant.images[0].url | imageSize('url_xxs')" alt="">
          <img v-else-if="item.variant.product.images.length > 0" :src="item.variant.product.images[0].url | imageSize('url_xxs')" alt="">
        </div>
        <div class="group-container">
          <div class="item-info-container">
            <div>
              <a v-if="Auth.hasAnyRole('Admin', 'Superadmin')" :href="'/products/' + item.variant.product_id + '/edit'"
                class="attribute"
                data-name="Product">{{ item.variant.product.name }}</a>
              <div v-else class="attribute" data-name="Product">{{ item.variant.product.name }}</div>
              <div class="attribute" data-name="SKU"><span>SKU: </span>{{ item.sku }}</div>
            </div>
            <div class="attribute" data-name="Variant">{{ item.variant.name }}</div>
            <div>
              <div class="attribute" data-name="Retail:"><span>Retail: </span>{{ item.retail_price | currency }}</div>
              <div class="attribute" data-name="Wholesale"><span>Wholesale: </span>{{ item.wholesale_price | currency }}</div>
            </div>
          </div>
          <div class="item-quantity-container">
            <div class="attribute" data-name="Size">{{ item.option }}</div>
            <div class="attribute" data-name="Quantity"><div>{{ item.quantity_available }} <i v-if="item.quantity_available <= lowQuantity()" class='errorText mdi mdi-alert' title="Low Quantity"/></div></div>
            <div class="attribute" data-name="+/- Inventory"><div><input class="quantity-update" type="number" @keyup.enter="changeQuantity(item)" v-model="item.update_quantity"></input><button class="cp-button-standard" @click="changeQuantity(item)">Update</button></div></div>
            <div v-if="!Auth.hasAnyRole('Rep') || $getGlobal('replicated_site').show" class="attribute attribute-center" data-name="Hide in Store">
              <input type="checkbox" v-model="item.disable" @change="updateInventory(item)"></input>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination
        :pagination="pagination"
        :callback="getItems"
        :offset="2"></cp-pagination>
    </div>
    <section class="import-modal" v-show="instructionsModal" @click="instructionsModal = false">
        <div class="instructions-wrapper" @click="preventDefault">
            <h3>Import Products Via CSV</h3>
            <p>To get started, please download our import template. You'll be able to open it in any spreadsheet application, such as Microsoft Excel or Apple Numbers.</p>
            <div class="action-btn-wrapper">
                <a class="cp-button-standard left" download href='/api/v1/inventory/csv-export?template=1'>Download Template</a>
                <button class="cp-button-standard import-button" @click="instructionsModal = false, importModal = true">Ready to Import</button>
            </div>
        </div>
    </section>
    <section class="import-modal" v-show="importModal" @click="importModal = false, importSuccess = false, importSteps = true">
      <div class="instructions-wrapper" @click="preventDefault">
        <div v-show="importSteps">
          <h1>Import Inventory</h1>
          <h3>Step 1: Search for User</h3>
          <form class="cp-form-registration" @submit.prevent>
              <cp-typeahead
              v-model="importRequest"
              :options="reps"
              :name-value="{ name: 'full_name', value: 'id' }"
              @options-cleared="function (val) { reps = val }"
              :search-function="searchReps"
              ></cp-typeahead>
          </form>

          <h4 v-show="importRequest.full_name">Upload inventory for <span class="full-name"><i> {{importRequest.full_name}}</i></span></h4>
          <h3>Step 2: Upload template by dropping in box below</h3>
          <form action="/api/v1/inventory/csv-import" method="POST" class="dropzone" id="createMediaZone">
              <input type="hidden" name="title" v-model="media.title">
              <input type="hidden" name="is_public" v-model="media.is_public">
              <input type="hidden" name="userId" v-model="importRequest.id">
          </form>
        </div>
        <div class="align-center" v-show="importSuccess">
          <h2>Success!</h2>
          <p>Inventory has been updated!</p>
        </div>
      </div>
    </section>
  </div>
</template>
<script id="CpInventory">
const Inventory = require('../../resources/InventoryAPIv0.js')
const Auth = require('auth')
const Users = require('../../resources/UsersAPIv0.js')
const Dropzone = require('dropzone')
const _ = require('lodash')
const Media = require('../../resources/media.js')

module.exports = {
  routing: [{
    name: 'site.CpInventory',
    path: '/inventory',
    meta: {
      title: 'Inventory'
    }
  }],
  data: () => ({
    Auth: Auth,
    instructionsModal: false,
    importModal: false,
    importSteps: true,
    importSuccess: false,
    loading: false,
    reps: [],
    items: [],
    tabs: [
      { name: 'IN STOCK', active: true },
      { name: 'SOLD OUT', active: false },
      { name: 'ALL', active: false }
    ],
    showProduct: {},
    showVariant: {},
    pagination: {},
    media: {},
    validationErrors: {},
    importRequest: {
      id: '',
      name: ''
    },
    indexRequest: {
      expands: ['product', 'variant', 'variant_images', 'product_images'],
      per_page: 15,
      sort_by: 'product_name',
      search_term: '',
      available: true,
      page: 1
    }
  }),
  props: {
    selectedRep: {
      type: Object,
      required: false,
      default: undefined
    }
  },
  mounted () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getItems()
    this.uploadDropzone()
  },
  methods: {
    getItems () {
      this.loading = true
      this.indexRequest.page = this.pagination.current_page
      if (this.selectedRep !== undefined) {
        this.indexRequest.user_id = this.selectedRep.id
        this.importRequest = {
          id: this.selectedRep.id,
          full_name: this.selectedRep.first_name + " " + this.selectedRep.last_name + " (" + this.selectedRep.id + ")"
        }
      } else {
        this.indexRequest.user_id = Auth.getOwnerId()
      }
      Inventory.getItems(this.indexRequest)
        .then((response) => {
          this.loading = false
          if (response.error) {
            return this.$toast(response.message)
          }
          this.items = response.data
          this.pagination = response
        })
    },
    changeTab (name) {
      // Start back at page one and filter items
      this.indexRequest.page = 1
      this.pagination.current_page = 1
      switch(name) {
        default:
        case 'ALL':
          delete this.indexRequest.available
          break
        case 'IN STOCK':
          this.indexRequest.available = true
          break
        case 'SOLD OUT':
          this.indexRequest.available = false
          break
      }
      this.getItems()
    },
    searchReps: _.debounce(function (searchTerm) {
      Users.index({search_term: searchTerm, limit: 10, role_id: [5, 8]})
        .then(response => {
          if (!response.error) {
            let users = response.data
            for (var i = 0; i < users.length; i++) {
                users[i].full_name = users[i].first_name + " " + users[i].last_name + " (" + users[i].id + ")"
            }
            this.reps = users
          }
        })
    }, 800),
    changeQuantity: _.debounce(function (item) {
      if (item.update_quantity === undefined || item.update_quantity === '' || item.update_quantity === null) {
        this.$toast('Invalid quantity entered. Please check input and try again.')
        return
      }
      let userId = null
      if (this.selectedRep !== undefined) {
        userId = this.selectedRep.id
      } else {
        userId = Auth.getOwnerId()
      }
      var request = {
        user_id: userId,
        quantity: parseInt(item.update_quantity)
      }
      Inventory.updateInventory(request, item.id)
        .then((response) => {
          if (!response.error) {
            item.quantity_available = response.quantity_available
            item.update_quantity = null
            return this.$toast(item.variant.name + ': ' + item.variant.option_label + ' ' + item.option + ' has been updated.', { dismiss: false })
          } else {
            return this.$toast(response.message, { error: true, dismiss: true })
          }
        })
    }, 800),
    updateInventory: function (item) {
      let userId = null
      if (this.selectedRep !== undefined) {
        userId = this.selectedRep.id
      } else {
        userId = Auth.getOwnerId()
      }
      var request = {
        user_id: userId,
        disable: item.disable
      }
      Inventory.updateInventory(request, item.id)
        .then((response) => {
          if (!response.error) {
            return this.$toast(item.variant.name + ': ' + item.variant.option_label + ' ' + item.option + ' has been ' + (response.disable ? 'disabled' : 'enabled'), { dismiss: false })
          } else {
            return this.$toast(response.message, { error: true, dismiss: true })
          }
        })
    },
    lowQuantity: function () {
      let setting = null;
      if (Auth.hasAnyRole('Rep') || this.selectedRep !== undefined) {
        setting = this.$getGlobal('low_inventory_alert_rep')
      } else {
        setting = this.$getGlobal('low_inventory_alert_corp')
      }
      // If setting is off lets alert for negative inventory
      return setting.show ? setting.value : -1
    },
    updateSort: function (sortBy) {
      if (this.indexRequest.sort_by.includes(sortBy)) {
        if (this.indexRequest.sort_by.indexOf('-') == 0) {
          this.indexRequest.sort_by = sortBy
        } else {
          this.indexRequest.sort_by = '-' + sortBy
        }
      } else {
        this.indexRequest.sort_by = sortBy
      }
      this.getItems()
    },
    uploadDropzone: function () {
      var $this = this
      var uploadZoneConfig = Media.dropzoneConfig('.csv')
      uploadZoneConfig.success = function (file, response) {
        $this.media = response
        $this.uploading = false
        $this.importModal = false
        $this.$toast('File successfully uploaded.')
        $this.importRequest = {
          id: '',
          name: ''
        }
      }
      uploadZoneConfig.queuecomplete = function (file, response) {
        this.removeAllFiles(true)
      }
      uploadZoneConfig.error = function (file, response) {
        $this.uploading = false
        $this.$toast(response, { error: true })
      }
      uploadZoneConfig.autoProcessQueue = true
      this.uploadZone = new Dropzone('#createMediaZone', uploadZoneConfig)
    },
    preventDefault: function (event) {
      event.stopPropagation()
    },
    getDefaultImage (imgs) {
      let images = JSON.parse(JSON.stringify(imgs))
      let reg = /(?:\.([^.]+))?$/
      if (images.length > 0) {
        let image = images[0].url
        let ext = reg.exec(image)[1]
        image = image.replace(/\.[^/.]+$/, '')
        return image + '-url_sm.' + ext
      }
      return ''
    }
  },
  computed: {
    itemError () {
      return function (index) {
        return JSON.stringify(this.validationErrors).includes('items.' + index + '.')
      }
    }
  },
  components: {
    CpTypeahead: require('../../cp-components-common/inputs/CpTypeahead.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpVariantGallery: require('./CpVariantGallery.vue')
  }
}
</script>
<style lang="scss" scoped>
#inventory-wrapper {
  div {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .product-img {
    max-width: 50px;
  }
  .quantity-update {
    max-width: 70px;
    margin-right: 10px;
  }
  @media screen and (min-width: 751px) {
    .item-list-container {
      li:nth-child(even) {background: $cp-lighterGrey};
    }
    .item-list > li {
      margin-bottom: 10px
    }
    .inventory-header {
      background-color: $cp-main;
    }
    .column-name {
      font-weight: 400;
      color: $cp-main-inverse;
    }
    .item-container {
      display: grid;
      grid-template-columns: 10% 90%;
      padding: 10px;
    }
    .item-info-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    .item-quantity-container {
      display: grid;
      grid-template-columns: minmax(100px, 2fr) minmax(100px, 2fr) minmax(200px, 4fr) minmax(100px, 2fr);
    }
    .group-container {
      display: grid;
      grid-template-columns: 4fr minmax(500px, 3fr);
    }
    .attribute-center {
      display: inline-block;
      text-align: center;
    }
  }
  @media screen and (max-width:750px) {
    .item-list-container {
      display: block;
      padding: 20px;
      .item-container {
        border-radius: 2px;
        display: block;
        background: white;
        margin-bottom: 10px;
        box-shadow: 1px 1px 1px 1px #ccc;
        padding: 10px 20px;
        div {
          font-weight: bold;
        }
      }
    }
    /* Don't display the first item, since it is used to display the header for tabular layouts*/
    .item-list-container>div:first-child {
        display: none;
    }
    .attribute {
      display: grid;
      grid-template-columns: minmax(9em, 30%) 1fr;
      span {
        display: none;
      }
    }
    .attribute::before {
      content: attr(data-name);
      font-weight: normal;
    }
  }

  .import-modal {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    padding: 200px;
    z-index: 0;
    .instructions-wrapper {
      background: #fff;
      padding: 50px;
      max-width: 768px;
      margin: 0 auto;
      box-shadow: 1px 1px 2px 1px #000;
      z-index: -1;
      .full-name {
        padding-left: 3px;
      }
      .import-button {
        height: 30px;
      }
      p {
        font-size: 18px;
        line-height: 1.4;
        margin: 20px 0 30px;
      }
      button {
        margin: 0 5px;
        &.left {
          margin-left: 0;
        }
      }
    }
  }
}
</style>
