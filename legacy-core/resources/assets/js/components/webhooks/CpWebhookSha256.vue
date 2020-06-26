<template>
  <div>
    <div>A header of Payload-Signature {hash} will be provided with hook requests</div>
    <div>The hash will be a base64 encoded sha256 hash of the request body</div>
    <cp-input
      label="Secret Key"
      type="text"
      :error="validationErrors['secret']"
      v-model="auth.secret">
    </cp-input>
    <div style="margin-bottom: 16px;">
      <button @click="generateSecret()" class="cp-button-link">Generate Secret</button>
    </div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      auth: {
        type: 'sha256',
        secret: ''
      },
      validationErrors: {}
    }
  },
  props: ['webhook'],
  created () {
    this.auth.secret = this.webhook.config.auth.secret
  },
  methods: {
    generateSecret () {
      let secret = ''
      for(let i = 0; i < 64; i++) {
        let x = Math.floor(Math.random() * 61.99)
        if (x > 35) {
          secret += String.fromCharCode(x + 61)
        } else if (x > 9) {
          secret += String.fromCharCode(x + 55)
        } else {
          secret += String.fromCharCode(x + 48)
        }
      }
      this.auth.secret = secret
    },
    validate () {
      let errors = {}
      if (this.auth.secret.length < 16) {
        errors['secret'] = ['Must be at least 16 characters']
      } else if(this.auth.secret.length > 64) {
        errors['secret'] = ['Can not be more than 64 characters']
      } else if(!/^[\w!@#$%^&*-=+]*$/.test(this.auth.secret)) {
        errors['secret'] = ['Must be letters, numbers, or any of !@#$%^&*-=+']
      }

      if (Object.keys(errors).length > 0) {
        this.validationErrors = errors
        this.loading = false
        return false
      } else {
        this.validationErrors = {}
        return true
      }
    },
    getAuth () {
      return this.auth
    }
  }
}
</script>
