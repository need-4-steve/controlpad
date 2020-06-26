<template>
  <div class="cp-form-standard">
    <div class="webhook-form-wrapper">
      <cp-input
        label="Name"
        type="text"
        :error="validationErrors['name']"
        v-model="webhook.name">
      </cp-input>
      <cp-select
        label="Event"
        :options="options"
        :error="validationErrors['event']"
        v-model="webhook.event">
      </cp-select>
      <cp-input
        label="URL"
        type="url"
        :error="validationErrors['url']"
        v-model="webhook.url">
      </cp-input>
      <div class="active-checkbox">
        <label>Active</label>
        <input
          type="checkbox"
          v-model="webhook.active">
        </input>
      </div>
      <div>
        <cp-select
          label="Auth"
          :options="authOptions"
          v-model="authType"
          @input="authSelected">
        </cp-select>
        <component v-if="authType !== 'none'" :is="selectedAuth.component" v-bind="{webhook}" :key="authType" ref="authComponent"></component>
      </div>
    </div>
    <cp-confirm
      :message="'Are you sure you want to delete this webhook? This can\'t be undone.'"
      v-model="showConfirm"
      :show="showConfirm"
      :callback="deleteWebhook"
      :params="{}">
    </cp-confirm>
    <div class="submit-button">
      <button class="cp-button-standard" @click="$emit('close')" :disabled="loading">Cancel</button>
      <button class="cp-button-standard" @click="save()" :disabled="loading">Save</button>
    </div>
    <div v-if="webhook.id" class="delete-button">
      <button class="cp-button-standard warning" @click="showConfirm = true" :disabled="loading">Delete</button>
    </div>
  </div>
</template>
<script>
const Auth = require('auth')
const Webhooks = require('../../resources/WebhooksAPIv0.js')

module.exports = {
  data: () => ({
    Auth: Auth,
    webhook: null,
    authType: 'none',
    validationErrors: {},
    loading: false,
    showConfirm: false,
    options: [],
    authTypes: {
      'none': {name: 'None', component: null},
      'sha256': {name: 'Sha256', component: 'cp-webhook-sha256'}
    },
    authOptions: [],
    selectedAuth: null
  }),
  props: {
    webhookProp: {
      default () {
        return {}
      }
    }
  },
  created () {
    for (var a in this.authTypes) {
      if (this.authTypes.hasOwnProperty(a)) {
        this.authOptions.push({name: this.authTypes[a].name, value: a})
      }
    }
    if (this.webhookProp == null) {
      this.webhook = {name: '', url: '', event: '', active: false, config: {auth: {type: 'none'}}}
      this.authType = 'none'
    } else {
      this.webhook = JSON.parse(JSON.stringify(this.webhookProp))
      this.authType = this.webhook.config.auth.type
    }
    this.selectedAuth = this.authTypes[this.authType]
    let eventMap = Webhooks.getEventMap()
    for (var k in eventMap) {
        if (eventMap.hasOwnProperty(k)) {
           this.options.push({name: eventMap[k], value: k});
        }
    }
  },
  methods: {
    save () {
      this.loading = true
      let errors = {}
      if (this.$isBlank(this.webhook.name)) {
        errors['name'] = ['Required']
      } else if (this.webhook.name.length > 255) {
        errors['name'] = ['Must be less than 255 characters']
      }
      if (this.$isBlank(this.webhook.url)) {
        errors['url'] = ['Required']
      } else if (this.webhook.url.length > 255) {
        errors['name'] = ['Must be less than 255 characters']
      } else if (!this.$isUrl(this.webhook.url)) {
        errors['url'] = ['Must be valid URL']
      }

      let authValid = true
      if (this.authType !== 'none') {
        authValid = this.$refs.authComponent.validate()
        this.webhook.config.auth = this.$refs.authComponent.getAuth()
      } else {
        this.webhook.config.auth = {type: 'none'}
      }
      // Check to display errors
      if (!authValid || Object.keys(errors).length > 0) {
        this.validationErrors = errors
        this.loading = false
        return false
      } else {
        this.validationErrors = {}
      }
      // Update or create
      if (this.webhook.id) {
        this.updateWebhook()
      } else {
        this.createWebhook()
      }
    },
    authSelected (value) {
      this.selectedAuth = this.authTypes[value]
    },
    createWebhook () {
      this.loading = true
      Webhooks.create(this.webhook)
        .then((response) => {
          this.loading = false
          if (response.error) {
            this.validationErrors = response.message
            return this.$toast(response.message)
          }
          this.webhook = response
          this.$toast('Webhook created successfully.')
          this.$emit('webhook-created', response)
        })
    },
    updateWebhook () {
      Webhooks.update(this.webhook)
        .then((response) => {
          this.loading = false
          if (!response.error) {
            this.webhook = response
            this.$toast('Webhook updated successfully.', { dismiss: false })
            this.$emit('webhook-updated', this.webhook)
          } else {
            this.validationErrors = response.message
          }
        })
    },
    deleteWebhook () {
      this.loading = true
      Webhooks.delete(this.webhook.id)
        .then((response) => {
          this.loading = false
          if (!response.error) {
            this.$toast('Webhook deleted successfully.', { dismiss: false })
            this.$emit('webhook-deleted', this.webhook)
            this.webhook.id = null
          } else {
            this.$toast('Webhook delete failed.', {error: true, dismiss: true})
          }
        })
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue'),
    CpTooltip: require('../../custom-plugins/CpTooltip.vue'),
    CpWebhookSha256: require('./CpWebhookSha256.vue')
  }
}
</script>
<style>
.webhook-form-wrapper {
}
.active-checkbox {
  display: flex;
  input {
    width: 75px;
  }
}
.delete-button {
    float: left;
    margin-top: 15px;
}
</style>
