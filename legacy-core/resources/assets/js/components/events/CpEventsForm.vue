<template>
  <div id="event-form-wrapper">
    <div class="cp-form-standard">
      <div>
        <cp-input
        :label="eventTitle + ' Name'"
        type="text"
        :error="validationErrors['name']"
        v-model="event.name"></cp-input>
        <cp-input
        label="Host Name"
        type="text"
        :error="validationErrors['host_name']"
        v-model="event.host_name"></cp-input>
        <label>Location</label>
        <cp-tooltip :options="{content:'Tell your guests where they can find your event. You can give them a website, a specific address or even a store parking lot.'}"></cp-tooltip>
        <cp-input
        type="text"
        :error="validationErrors['location']"
        v-model="event.location">
        </cp-input>
        <label>Limit</label>
        <cp-tooltip :options="{content:'This is an optional field which allows you to set a limit on the number of items (not orders) that can be sold in this event. Once the number of items have been sold, the event will be displayed as “Sold Out” to your customers.'}"></cp-tooltip>
        <cp-input
        type="number"
        :error="validationErrors['items_limit']"
        v-model="event.items_limit"></cp-input>
        <div>
          <label>{{ eventTitle }} Date</label>
          <cp-tooltip :options="{content:'This is the actual date/time of your event for people to meet at the designated location. The time will be saved in your local time zone and will be automatically converted for any customer viewing the event from a different time zone. Please remember that the Sale Start Sate and Sale End Date allow you to control how long before or after an event you will accept orders. '}"></cp-tooltip>
        </div>
        <div>
          <cp-datetime v-model="saleDate" type="datetime"></cp-datetime>
        </div>
        <div>
          <label>Sale Start Date</label>
          <cp-tooltip :options="{content: 'This is the date/time the event will be made available to your customers. The time will be saved in your local time zone and will be automatically converted for any customer viewing the event from a different time zone.'}"></cp-tooltip>
        </div>
        <div>
          <cp-datetime v-model="saleStart" type="datetime"></cp-datetime>
        </div>
        <div>
          <label>Sale End Date</label>
          <cp-tooltip :options="{content: 'This is the date/time the event will no longer be made available to your customers. The time will be saved in your local time zone and will be automatically converted for any customer viewing the event from a different time zone. You can edit this date after creation if you ever need to adjust how long to make the event available.'}"></cp-tooltip>
        </div>
        <div>
          <cp-datetime v-model="saleEnd" type="datetime"></cp-datetime>
        </div>
        <div v-if="edit">
          <label>{{ eventTitle }} Status:</label><strong>{{event.status}}</strong>
        </div>
        <button v-if="event.status === 'open' && edit" class="cp-button-standard" type="button" name="button" @click="confirmAndClose(event)"> Close {{ eventTitle }}</button>
        <cp-events-photo-upload
          v-if="!loadingImage"
          @new-media="function (val) { event.img = val }"
          :current-media="event.img"
          :validation-errors="validationErrors"></cp-events-photo-upload>
        <label>Description</label>
        <cp-editor v-model="event.description"></cp-editor>
        <div class="cp-box-standard">
            <div class="cp-box-heading">
                <h5>Products <cp-tooltip :options="{ content: 'When Select Individual Products is off all products will be add to the Event.'}"></cp-tooltip></h5>
            </div>
         </div>
        <cp-toggle label="Select individual Products" v-model="selectIndividual" @click.native="selectProducts()"></cp-toggle>
        <div v-show="selectIndividual" class="select products">
          <label for="">Search Products</label>
          <cp-typeahead
            @input="addProduct"
            :options="searchResults"
            :clear-dropdown="clearSearchOptions"
            :name-value="{ name: 'name', value: 'id'}"
            @options-cleared="clearSearchOptions"
            :search-function="searchProducts"></cp-typeahead>
          <table class="cp-table-standard">
              <thead>
              <th></th>
                  <th @click="sortColumn('name')">Name
                      <span v-show="indexRequest.sort_by == 'name'">
                          <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                          <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      </span>
                  </th>
                  <th>Category</th>
                  <th@click="sortColumn('price')">Lowest Price
                    <span v-show="indexRequest.sort_by == 'price'">
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    </span>
                  </th>
                  <th></th>
              </thead>
              <tbody>
                  <tr v-for="(product, index) in products">
                    <td v-if="product.images">
                      <img class="thumb" :src="product.images[0].url">
                    </td>
                    <td v-else></td>
                    <td>
                      {{product.name}}
                    </td>
                    <td v-if="product.categories">
                        <span v-for="category in product.categories">
                          {{category.name }} &nbsp;
                        </span>
                    </td>
                    <td v-else></td>
                    <td v-if="product.price">
                      {{product.price}}
                    </td>
                    <td v-else></td>
                    <td><i class="mdi mdi-close pointer right" @click="removeProduct(product, index)"></i></td>
                  </tr>
              </tbody>
          </table>
        </div>
      </div>
    </div>
    <cp-confirm
    :message="'Are you sure you want to close this event? This can\'t be undone.'"
    v-model="showConfirm"
    :show="showConfirm"
    :callback="updateEvent"
    :onCancelled="undoDelete"
    :params="{id:event.id}"></cp-confirm>
    <div class="submit-button">
      <button class="cp-button-standard" @click="$emit('close')">Cancel</button>
      <button class="cp-button-standard" @click="createOrUpdate()" :disabled="creatingEvent">Submit</button>
    </div>
  </div>
</template>
<script>
const Auth = require('auth')
const Events = require('../../resources/EventsAPIv0.js')
const Products = require('../../resources/InventoryAPIv0.js')
const moment = require('moment')

module.exports = {
  data: () => ({
    Auth: Auth,
    validationErrors: {},
    creatingEvent: false,
    showConfirm: false,
    loadingImage: true,
    selectIndividual: false,
    products: [],
    saleDate: null,
    saleStart: null,
    saleEnd: null,
    indexRequest: {
      price: 'retail',
      order: 'DESC',
      sort_by: 'created_at',
      per_page: 100,
      search_term: '',
      page: 1,
      type: 'products',
      expands: ['categories', 'product_images'],
      user_id: null,
      corp: false
    },
    reverseSort: false,
    searchResults: [],
    eventsTitle: 'Events',
    eventTitle: 'Event'
  }),
  props: {
    event: {
      default () {
        return {}
      }
    },
    edit: {
      type: Boolean
    }
  },
  created () {
    this.eventsTitle = this.$getGlobal('events_title').value.plural
    this.eventTitle = this.$getGlobal('events_title').value.single
    if (this.event.date) {
      this.saleDate = moment.utc(this.event.date, 'YYYY-MM-DDTHH:mm:ss').local().format('YYYY-MM-DDTHH:mm:ss.SSS')
    }
    if (this.event.sale_start) {
      this.saleStart = moment.utc(this.event.sale_start, 'YYYY-MM-DDTHH:mm:ss').local().format('YYYY-MM-DDTHH:mm:ss.SSS')
    }
    if (this.event.sale_end) {
      this.saleEnd = moment.utc(this.event.sale_end, 'YYYY-MM-DDTHH:mm:ss').local().format('YYYY-MM-DDTHH:mm:ss.SSS')
    }
  },
  mounted () {
    this.dataSetup()
  },
  methods: {
    dataSetup () {
      if (!this.edit) {
        this.event.img = this.$getGlobal('events_default_img').value
        this.event.product_ids = null
      } else {
        if (this.event.product_ids !== null && this.event.product_ids.length > 0) {
          this.selectIndividual = true
          this.getProducts()
        } else if (this.event.product_ids !== null) {
          this.selectIndividual = true
          this.products = []
        }
      }

      this.loadingImage = false
    },
    confirmAndClose (id) {
      this.event.status = 'closed'
      this.showConfirm = true
    },
    undoDelete () {
      this.event.status = 'open'
    },
    createOrUpdate: function () {
      if (this.edit) {
        this.updateEvent()
      } else {
        this.createEvent()
      }
    },
    createEvent () {
      let request = {
        name: this.event.name,
        host_name: this.event.host_name,
        items_limit: this.event.items_limit,
        location: this.event.location,
        description: this.event.description,
        date: this.convertDateUTC(this.saleDate),
        sale_start: this.convertDateUTC(this.saleStart),
        sale_end: this.convertDateUTC(this.saleEnd),
        img: this.event.img,
        status: this.event.status,
        product_ids: this.event.product_ids
      }
      this.creatingEvent = true
      this.event.status = 'open'
      Events.createEvent(request)
        .then((response) => {
          this.creatingEvent = false
          if (response.error) {
            this.validationErrors = response.message
            return this.$toast(response.message)
          }
          this.$toast('Event created successfully.')
          this.$emit('add-event', this.event)
        })
    },
    updateEvent () {
      let request = {
        name: this.event.name,
        host_name: this.event.host_name,
        items_limit: this.event.items_limit,
        location: this.event.location,
        description: this.event.description,
        date: this.convertDateUTC(this.saleDate),
        sale_start: this.convertDateUTC(this.saleStart),
        sale_end: this.convertDateUTC(this.saleEnd),
        img: this.event.img,
        status: this.event.status,
        product_ids: this.event.product_ids
      }
      // don't update status unless input is setting it to closed
      if (this.event.status !== 'closed') {
        delete request.status
      }
      Events.updateEvent(request, this.event.id)
        .then((response) => {
          if (!response.error) {
            this.$toast('Event updated successfully.', { dismiss: false })
            this.$emit('add-event', this.event)
          } else {
            this.validationErrors = response.message
          }
        })
    },
    addProduct (product) {
      if (this.event.product_ids === null) {
        this.event.product_ids = []
      }
      this.event.product_ids.push(product.id)
      this.products.unshift(product)
    },
    removeProduct (product, index) {
      this.products.splice(index, 1)
      this.event.product_ids = this.event.product_ids.filter(product_ids => product_ids != product.id)
    },
    clearSearchOptions: _.debounce(function () {
      this.searchResults = []
    }, 400),
    convertDateUTC (datetime) {
      return moment(datetime, 'YYYY-MM-DDTHH:mm:ss.SSS').utc().format('YYYY-MM-DDTHH:mm:ss')
    },
    searchProducts (searchTerm) {
      this.indexRequest.search_term = searchTerm
      if (Auth.hasAnyRole('Superadmin', 'Admin')) {
        this.indexRequest.corp = true
      } else {
        this.indexRequest.user_id = this.Auth.getOwnerId()
      }
      this.indexRequest.product_ids = []
      Products.getProducts(this.indexRequest)
        .then((response) => {
          if (!response.error) {
            this.searchResults = response.data
          }
        })
    },
    getProducts () {
      this.indexRequest.product_ids = this.event.product_ids
      if (Auth.hasAnyRole('Superadmin', 'Admin')) {
        this.indexRequest.corp = true
      } else {
        this.indexRequest.user_id = this.Auth.getOwnerId()
      }
      Products.getProducts(this.indexRequest)
        .then((response) => {
          if (this.edit) {
            this.products = response.data
          }
        })
    },
    selectProducts () {
      this.selectIndividual = !this.selectIndividual
      if (this.selectIndividual === false) {
        this.products = []
        this.event.product_ids = null
      } else {
        this.event.product_ids = []
      }
    },
    sortColumn: function (sort_by) {
      this.indexRequest.sort_by = sort_by
      this.indexRequest.product_ids = this.event.product_ids
      this.reverseSort = !this.reverseSort
      if (this.reverseSort !== true) {
        this.indexRequest.sort_by = '-' + sort_by
      }
      this.getProducts()
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpEventsPhotoUpload: require('./CpEventsPhotoUpload.vue'),
    CpEditor: require('../../cp-components-common/inputs/CpEditor.vue'),
    CpConfirm: require('../../cp-components-common/CpConfirm.vue'),
    CpTooltip: require('../../custom-plugins/CpTooltip.vue')
  }
}
</script>
<style lang="scss" scoped>
#event-form-wrapper {
  .line-wrapper {
      display: flex;
      -webkit-display: flex;
      justify-content: space-between;
      -webkit-justify-content: space-between;
      label {
          font-size: 14px;
          font-weight: 300;
          margin-bottom: 0;
      }
      span {
        width: 50% !important;
        input {
          width: 100% !important;
        }
      }
      .input-class {
          height: 100%;
          width: 50%;
          height: 30px;
          text-indent: 10px;
          margin: 5px 0;

      }
      &.toggle-switch {
          width: 40px;
      }
  }
}
</style>
