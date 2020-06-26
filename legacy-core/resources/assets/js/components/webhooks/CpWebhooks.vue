<template>
  <div class="webhook-wrapper">
    <div style="margin-bottom: 16px;">
        <button @click="newWebhook()" class="cp-button-link">Create Webhook</button>
    </div>
    <div class="webhook-list-container">
      <div class="webhook-header">
        <div class="webhook-container">
          <div class="webhook-info-container">
            <div class="cp-clickable" @click="updateSort('name')">
              <div class="column-name">Name<i v-show="indexRequest.sort_by.includes('name')" :class="['mdi', '', indexRequest.sort_by.indexOf('-') == 0 ? 'mdi-sort-descending' : 'mdi-sort-ascending']"></i></div>
            </div>
            <div class="cp-clickable" @click="updateSort('event')">
              <div class="column-name">Event<i v-show="indexRequest.sort_by.includes('event')" :class="['mdi', '', indexRequest.sort_by.indexOf('-') == 0 ? 'mdi-sort-descending' : 'mdi-sort-ascending']"></i></div>
            </div>
            <div class="column-name">Delivery URL</div>
          </div>
          <div class="column-name">Active</div>
        </div>
      </div>
      <div v-for="(webhook, index) in webhooks" v-if="!loading" class="webhook-container">
        <div class="webhook-info-container">
          <div class="attribute" data-name="Name"><a @click="editWebhook(webhook)">{{ webhook.name }}</a></div>
          <div class="attribute" data-name="Event">{{ formatEventSlug(webhook.event) }}</div>
          <div class="attribute" data-name="URL">{{ webhook.url }}</div>
        </div>
        <div class="attribute" data-name="Active">
          <i :class="['mdi', 'mdi-checkbox-blank-circle', webhook.active ? 'webhook-on' : 'errorText']"></i>
        </div>
      </div>
    </div>
    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination
        :pagination="pagination"
        :callback="getWebhooks"
        :offset="2"></cp-pagination>
    </div>
    <cp-dialog :open="webhookModal" @close="clearModal()">
      <h2 slot="header">Webhook</h2>
      <template slot="content">
        <cp-webhook-form
          v-if="webhookModal"
          :webhookProp="selectedWebhook"
          @close="clearModal"
          @webhook-created="webhookCreated"
          @webhook-updated="webhookUpdated"
          @webhook-deleted="webhookDeleted"
        ></cp-webhook-form>
      </template>
    </cp-dialog>
  </div>
</template>


<script>
const Auth = require('auth')
const Webhooks = require('../../resources/WebhooksAPIv0.js')
const _ = require('lodash')

module.exports = {
  name: 'CpWebhooks',
  routing: [
    {
      name: 'site.CpWebhooks',
      path: 'webhooks',
      meta: {
        title: 'Webhooks'
      },
      props: true
    }
  ],
  data: () => ({
    Auth: Auth,
    loading: false,
    webhooks: [],
    webhookModal: false,
    indexRequest: {
      per_page: 50,
      sort_by: 'name',
      page: 1
    },
    pagination: {},
    webhookEventMap: {

    }
  }),
  mounted () {
    if (!Auth.hasAnyRole('Superadmin')) {
      // TODO superadmin only
    } else {
      this.getWebhooks()
    }
  },
  methods: {
    getWebhooks () {
      this.loading = true
      this.indexRequest.page = this.pagination.current_page
      Webhooks.search(this.indexRequest)
        .then((response) => {
          this.loading = false
          if (response.error) {
            return this.$toast(response.message)
          }
          this.webhooks = response.data
          this.pagination = response
        })
    },
    newWebhook () {
      this.selectedWebhook = null
      this.webhookModal = true
    },
    editWebhook (webhook) {
      this.selectedWebhook = webhook
      this.webhookModal = true
    },
    clearModal () {
      this.webhookModal = false
      this.selectedWebhook = null
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
      this.getWebhooks()
    },
    webhookCreated (webhook) {
      this.webhooks.push(webhook)
      this.clearModal()
    },
    webhookUpdated (webhook) {
      let i = this.webhooks.indexOf(this.selectedWebhook)
      if (i > -1) {
        this.webhooks[i] = webhook
      }
      this.clearModal()
    },
    webhookDeleted () {
      let i = this.webhooks.indexOf(this.selectedWebhook)
      if (i > -1) {
        this.webhooks.splice(i, 1)
      }
      this.clearModal()
    },
    formatEventSlug (slug) {
      if (Webhooks.getEventMap().hasOwnProperty(slug)) {
        return Webhooks.getEventMap()[slug]
      } else {
        return slug.split('-').map(function (string) {
            return string.charAt(0).toUpperCase() + string.substring(1)
          }
        ).join(' ')
      }
    }
  },
  components: {
    CpConfirm: require('../../cp-components-common/CpConfirm.vue'),
    CpWebhookForm: require('../webhooks/CpWebhookForm.vue')
  }
}
</script>
<style lang="scss" scoped>
.webhook-wrapper {
  div {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .webhook-on {
    font-size: 12px;
    color: $cp-green;
  }
  @media screen and (min-width: 751px) {
    .webhook-list-container {
      li:nth-child(even) {background: $cp-lighterGrey};
    }
    .webhook-header {
      background-color: $cp-main;
    }
    .column-name {
      font-weight: 400;
      color: $cp-main-inverse;
    }
    .webhook-container {
      display: grid;
      grid-template-columns: 4fr 100px;
      padding: 10px;
    }
    .webhook-info-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    .attribute-center {
      display: inline-block;
      text-align: center;
    }
  }
  @media screen and (max-width:750px) {
    .webhook-list-container {
      display: block;
      padding: 20px;
      .webhook-container {
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
    .webhook-list-container>div:first-child {
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
}
</style>
