<template lang="html">
  <div class="company-wrapper">
    <div class="cp-box-standard">
      <div class="cp-box-heading">
        <h5>Company Information (optional)</h5>
      </div>
      <div class="cp-accordion">
        <div class="cp-box-body">
          <div class="cp-accordion-head" @click="companyInfo = !companyInfo">
              <h5> Your Company Information </h5>
              <span v-if="companyInfo"class="arrow"><i class="mdi mdi-chevron-down"></i></span>
              <span v-if="!companyInfo" class="arrow"><i class="mdi mdi-chevron-left"></i></span>
          </div>
          <div class="cp-accordion-body cp-form-standard" :class="{closed: companyInfo === true}">
              <div class="cp-accordion-body-wrapper">
                <cp-input label="Company Name" type="text" v-model="company_settings.name"></cp-input>
                <cp-input-mask
                label="Company EIN"
                tooltip="An Employer Identification Number (EIN) is also known as a
                Federal Tax Identification Number, and is used to identify a business entity.
                Please do not use a Social Security Number in this field."
                v-if="companyLoaded"
                placeholder="00-0000000"
                :mask="'##-########'"
                :error="null"
                v-model="company_settings.ein"></cp-input-mask>
              </div>
                <div class="button-wrapper">
                  <button class="cp-button-standard"type="button" name="button" @click="saveCompanyInfo()">Save Company Info</button>
                </div>
              </div>
          </div>
      </div>
    </div>
  </div>
</template>
<script>
const Users = require('../../../resources/users.js')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      companyInfo: true,
      companyLoaded: false,
      company_settings: {
        name: '',
        ein: ''
      },
      errorMessages: {}
    }
  },
  mounted () {
    this.userCompanyInfo()
  },
  methods: {
    userCompanyInfo () {
      Users.userCompanyInfo()
        .then((response) => {
          if (response.error) {
            return
          }
          if (response.name == null) {
            this.company_settings.name = null
          } else {
            this.company_settings.name = response.name
          }
          if (response.ein == null) {
            this.company_settings.ein = null
          } else {
            this.company_settings.ein = response.ein
          }
          this.companyLoaded = true
        })
    },
    saveCompanyInfo: _.debounce(function () {
      this.errorMessages = {}
      if (this.companyInfo) {
        return
      }
      if (this.company_settings.ein) {
        this.company_settings.ein = this.cleanDashes(this.company_settings.ein)
      }
      Users.createOrUpdateCompanyInfo(this.company_settings)
      .then((response) => {
        if (response.error) {
          this.errorMessages = response.message
          return
        } else {
          this.$toast('Company information has saved.', { dismiss: false })
        }
      })
    }, 1000),
      /*
      * cleans dashes from specific numbers
      * @return object
      */
    cleanDashes (ein) {
      ein = ein.toString()
      ein = ein.replace(/-/g, '')
      ein = parseInt(ein)
      return ein
    }
  },
  components: {
    CpInputMask: require('../../../cp-components-common/inputs/CpInputMask.vue')
  }
}
</script>

  <style lang="sass">
    @import "resources/assets/sass/var.scss";
    .company-wrapper {
      .cp-box-heading {
           position: relative;
           &.sub-heading {
               background: lighten($cp-main, 20%);
           }
           button {
               &.action-btn {
                   width: auto;
                   background: inherit;
                   padding: 0 10px;
                   color: #fff;
               }
           }
      }
    }
  </style>
