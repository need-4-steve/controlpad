<template>
    <div class="cp-splash-registration-wrapper">
       <button class="cp-button-standard accept-payment-button" @click="showModal=true">Accept Payments Now</button>
       <transition name="modal">
       <div class="cp-modal-standard" v-if="showModal">
         <div class="cp-modal-body">
           <span class="close-modal"><i class="mdi mdi-close" @click="showModal=false"></i></span>
           <div class="step-one" v-show="Step.get('one')">
             <h1 class="business-title">What kind of business are you?</h1>
             <div class="business-wrapper">
               <div class="box cp-panel-standard">
               <h2 class="title-width">Sole Proprietor</h2>
               <div class="sol-prop-content">
                   <p>The simplest business structure is the sole proprietorship--the IRS classifies most new businesses with only one owner as a sole proprietorship.</p>
                   <p>If you HAVE NOT formally incorporated or followed a process that makes your business a separate legal entity, the IRS considers your business a sole proprietorship.</p>
                   <p>If you HAVE NOT applied for a EIN number the IRS will identify your business with your social security number.</p>
               </div>
                 <h4>I will submit my business taxes with my Social Security number</h4>
                 <button type="button"  class="cp-button-standard splash" name="button" @click="Step.next(), ppa.type = '0'">I am a Sole Proprietor</button>
               </div>
               <div class="box cp-panel-standard">
                 <h2 class="title-width">Business or Corporation</h2>
                 <div class="bus-corp-content">
                     <p>Any business that has employees must obtain an EIN to file payroll and other taxes. The payroll tax deductions for the employees are filed using the employer identification number.</p>
                     <p>Some businesses and corporations apply for and reveive and EIN if they would like to keep personal business tax filing under separate tax ID numbers.</p>
                 </div>
                 <h4>I submit my company taxes with an EIN number</h4>
                 <button type="button" class="cp-button-standard splash" name="button" @click="Step.next(), ppa.type = '1'">I am a business or Corporation</button>
               </div>
             </div>
           </div>
           <div v-show="Step.get('two')">
             <div v-if="!loading && !ppaProcessed" class="splash-get-paid-form">
               <div class="registration-title">
                 <h2>Get Paid</h2>
                 <h4>We need some basic information to pay you</h4>
                 <h4><strong>NOTE:</strong> This information must be entered as it appears on your tax return.</h4>
               </div>
               <form class="cp-form-registration" @submit.prevent>
                   <div class="col">
                     <h5>General Account Information</h5>
                     <cp-select
                       label="Select your business type"
                       :options="[
                         { name: 'Sole Proprietor', value: '0' },
                         { name: 'Business', value: '1' },
                         { name: 'Limited Liability Company', value: '2' },
                         { name: 'Partnership', value: '3' },
                         { name: 'Association', value: '4' },
                         { name: 'Non-profit Organiztion', value: '5' },
                         { name: 'Government Organization', value: '6' }
                       ]"
                       :error="validationErrors['type']"
                       v-model="ppa.type"></cp-select>
                         <!-- all others -->
                         <cp-input v-if="ppa.type !== '0'"
                           label="Business Name"
                           type="text"
                           :error="validationErrors['name']"
                           v-model="ppa.name"></cp-input>
                         <cp-input-mask v-if="ppa.type !== '0'"
                           label="Business Phone"
                           type="text"
                           mask="(###)-###-####"
                           :error="validationErrors['phone']"
                           v-model="ppa.phone"></cp-input-mask>
                         <cp-input v-if="ppa.type !== '0'"
                           label="Date Established"
                           type="date"
                           :error="validationErrors['established']"
                           v-model="ppa.established"></cp-input>
                         <cp-input-mask
                           v-if="ppa.type !== '0'"
                           label="EIN"
                           mask="##-#######"
                           type="text"
                           :error="validationErrors['ein']"
                           v-model="ppa.ein"></cp-input-mask>
                           <p v-if="ppa.type !== '0'"><i class="mdi mdi-help-circle" @click="eInInfo()"></i>What is an EIN</p>
                           <cp-input
                           label="DBA (Doing Business As)"
                           type="text"
                           :error="validationErrors['dba']"
                           v-model="ppa.dba"></cp-input>
                         <h5>Business Address</h5>
                         <cp-address-form
                           :address="ppa.address"
                           :validation-errors="validationErrors"
                           :address-type="'address'"
                           :hide-name-field="true"></cp-address-form>
                         <h5>Tell us where to send your money</h5>
                         <cp-input
                           label="Routing Number"
                           type="text"
                           :error="validationErrors['account.routing']"
                           v-model="ppa.account.routing"></cp-input>
                         <cp-input
                           label="Account Number"
                           type="text"
                           :error="validationErrors['account.number']"
                           v-model="ppa.account.number"></cp-input>
                         <cp-select
                           label="Account Type"
                           :options="[
                             { name: 'Checking', value: 'checking' },
                             { name: 'Savings', value: 'savings' }
                           ]"
                           :error="validationErrors['account.type']"
                           v-model="ppa.account.type"></cp-select>
                     </div>
                     <div class="col">
                         <h5>Business Owner</h5>
                         <cp-input
                         label="First Name"
                         type="text"
                         :error="validationErrors['owner.first_name']"
                         v-model="ppa.owner.first_name"></cp-input>
                         <cp-input
                         label="Last Name"
                         type="text"
                         :error="validationErrors['owner.last_name']"
                         v-model="ppa.owner.last_name"></cp-input>
                         <cp-input
                         label="Date of Birth"
                         placeholder="Example: 1986-12-21"
                         type="date"
                         :error="validationErrors['owner.dob']"
                         v-model="ppa.owner.dob"></cp-input>
                         <cp-input-mask
                           label="Phone"
                           mask="(###)-###-####"
                           :error="validationErrors['owner.phone']"
                           v-model="ppa.owner.phone"></cp-input-mask>
                         <cp-input
                         label="Email"
                         type="text"
                         :error="validationErrors['owner.email']"
                         v-model="ppa.owner.email"></cp-input>
                         <cp-input v-if="ppa.type !== '0'"
                         label="Percentage of Ownership"
                         type="number"
                         placeholder="%"
                         :error="validationErrors['owner.ownership']"
                         v-model="ppa.owner.ownership"></cp-input>
                         <cp-input-mask
                         label="SSN"
                         mask="###-##-####"
                         :error="validationErrors['owner.ssn']"
                         v-model="ppa.owner.ssn"></cp-input-mask>
                         <div v-if="ppa.type !== '0'">
                         <h5>Business Owner's Address</h5>
                         <cp-address-form
                         :address="ppa.owner.address"
                         :validation-errors="validationErrors"
                         :address-type="'owner.address'"
                         :hide-name-field="true"></cp-address-form>
                     </div>

                        <div class="complience-box">
                             <h4>In a few moments you can be accepting Visa, Mastercard, Discover and American Express for one low rate.</h4>
                         <p class="complience-bullets">
                         <span>• No long term commitment </span><span>• No Credit Check </span><span>• No Monthly Fees</span></p>
                         <div class="complience-info">
                             <span class="complience-info-left-num">2.75% + .15¢</span>|
                             <span class="complience-info-right-num">3.50% +.15¢</span>
                                <br>
                            <span class="complience-info-1">PER SWIPE, DIP OR TAP</span>
                            <span class="complience-info-2">PER KEYED-IN TRANSACTION</span>
                         </div>
                            <br>
                         <div class="center">
                             <p>Our express credit card processing program is provided by Splash Payments and is designed to get you processing as soon as possible.</p>
                             <p><b>By submitting this form you are agreeing to the following <a href="https://portal.splashpayments.com/terms">terms</a></b></p>
                         </div>
                        </div>

                       </div>
                 </form>
             </div>
             <div class="cp-modal-controls">
               <button class="cp-button-standard right" name="submit-ppa" v-if="!ppaProcessed"  @click="processPaymentAccount()" :disabled="disableSubmit">Submit</button>
               <button class="cp-button-standard right" name="submit-ppa" v-if="ppaProcessed" @click="showPaymentAccountModal = false, showPaymentAccountModal = true, ppaProcessed = false">Try Again</button>
               <button class="cp-button-standard left" name="cancel-ppa" @click="Step.skipTo('one'), ppaProcessed = false">Back</button>
             </div>
           </div>
           <div class="align-center" v-if="loading">
             Processing your application. Please Wait.
             <br />
             <img class="loading" :src="$getGlobal('loading_icon').value">
           </div>
           <div class="align-center">
             <p class="ppa-message" v-if="ppaProcessed">{{ ppaMessage }}</p>
             <br />
           </div>
         </div>
       </div>
     </transition>
     </div>
</template>
<script type="text/javascript">
const Payments = require('../../resources/payments.js')
const Step = require('../../libraries/step.js')
// PPA stands for payment process account
module.exports = {
  data: function () {
    return {
      Step: Step,
      einInfo: false,
      accountInfo: false,
      showPaymentAccountModal: false,
      user: this.userInfo,
      disableSubmit: false,
      showModal: false,
      loading: false,
      ppaProcessed: false,
      validationErrors: {},
      foo: 'foo'
    }
  },
  props: {
    userInfo: {
      required: true
    },
    ppa: {   // PAYMENT PROCESS ACCOUNT
      type: Object,
      required: true,
      default () {
        return {
          type: null,
          businessAddress: {},
          businessAccount: {}
        }
      }
    }
  },
  mounted () {
    this.initSteps()
  },
  methods: {
    eInInfo: function () {
      this.$toast("The Employer Identification Number (EIN), also known as the Federal Employer Identification Number (FEIN). If you don't have one please select Sole Proprietor above.", {dismiss: false})
    },
    initSteps () {
      // init steps - only one step should be set to true
      let steps = {
        one: true,
        two: false
      }
      this.Step.init(steps, 300)
    },
    /*
    * cleans dashes from specific numbers
    * @return object
    */
    cleanDashes (inputs) {
      if (inputs.phone) {
        inputs.phone = inputs.phone.replace(/-/g, '').replace(/\(|\)/g, '')
        inputs.phone = parseInt(inputs.phone)
      }
      if (inputs.ein) {
        inputs.ein = inputs.ein.replace(/-/g, '')
        inputs.ein = parseInt(inputs.ein)
      }
      if (inputs.owner.phone) {
        inputs.owner.phone = inputs.owner.phone.replace(/-/g, '').replace(/\(|\)/g, '')
        inputs.owner.phone = parseInt(inputs.owner.phone)
      }
      if (inputs.owner.ssn) {
        inputs.owner.ssn = inputs.owner.ssn.replace(/-/g, '')
      }
      return inputs
    },
    // accept-payments
    processPaymentAccount () {
      this.disableSubmit = true
      this.loading = true
      window.scrollTo(0,0);
      this.validationErrors = {}
      let request = JSON.parse(JSON.stringify(this.ppa))
      if (request.type !== '0') {
        request.provider = 'splash_option_2'
      } else {
        request.name = request.owner.first_name + ' ' + request.owner.last_name
        request.phone = request.owner.phone
        request.provider = 'splash_option_1'
      }
      request.owner.dob = moment(request.owner.dob).format("YYYY-MM-DD")
      if (request.established) {
        request.established = moment(request.established).format("YYYY-MM-DD")
      }
      if (!moment(request.established)) {
        this.loading = false
        this.disableSubmit = false
        this.validationErrors['established'] = ['Invalid date. Example: 2012-12-21'];
      }
      if (!moment(request.owner.dob).isValid()) {
        this.loading = false
        this.disableSubmit = false
        this.validationErrors['owner.dob'] = ['Invalid date. Example: 2012-12-21'];
        return
      }
      request = this.cleanDashes(request)
      Payments.createPaymentProcessingAccount(request)
        .then((response) => {
          this.loading = false
          this.disableSubmit = false
          if (response.error && response.code === 422) {
            this.validationErrors = response.message
            return
          } else if (response.error) {
            this.ppaMessage = response.message
            this.ppaProcessed = true
            return
          }
          console.log('success')
          this.showPaymentAccountModal = false
          this.$emit('account-processed');
          this.$toast('Your payment application was accepted.')
        })
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpInputMask: require('../../cp-components-common/inputs/CpInputMask.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue'),
    CpAddressForm: require('../addresses/CpAddressForm.vue')
  }
}
</script>

<style lang="scss">
  @import "resources/assets/sass/var.scss";
  .complience-box {
      width: 400px;
      height: 300px;
      padding: 10px;
      border: 5px;
      border-radius: 3px;
      margin: 5px;
  }
  h4 {
    display: flex;
    text-align: center;
  }
  b {
    text-align: center;
  }
  .complience-bullets {
    font-size: 12px;
    text-align:center;
  }
  .complience-info {
    background-color: $cp-LightBlue;
    text-align: center;
    padding: 10px 30px 20px 30px;
    max-width:100%;
    max-height:50px;
    border-radius: 2px;
    box-shadow: 1px 1px 1px #888888;
    margin-left: 25px;
    margin-right: 25px;
    color: #ffffff;
  }
  .complience-info-left-num {
    margin-left: 10px;
    float:left;
    font-size: 14px;
    text-align: center;
  }
  .complience-info-right-num {
    margin-right: 10px;
    float:right;
    font-size: 14px;
    text-align: center;
  }
  .complience-info-1  {
    margin-left: 5px;
    float: left;
    font-size: 10px;
  }
   .complience-info-2  {
    margin-right: -15px;
    float: right;
    font-size: 10px;
  }
  .splash {
   padding: 4px 25px !important;
   margin: 10px 50px !important;


  }
  .cp-splash-registration-wrapper{
    .accept-payment-button {
      float: right;
    }
    .registration-title {
      text-align: center;
    }
    .cp-modal-body{
      position: relative;
      max-width:  900px;
    }
    .close-modal{
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 20px;
    color: #838383;
    cursor: pointer;
    }
  .select-standard-wrapper span {
    padding-bottom: 8px;
  }
  .business-title{
    text-align: center;
  }
  .business-wrapper{
    display: flex;
    width: 100%;
  }
  .box {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-content: center;
    text-align: center;
    margin: 16px;
    padding: 8px;
    .bus-corp-content {
        padding-right: 10px;
        padding-left: 10px;
        padding-bottom: 120px;
        font-size: 16px;
    }
    .sol-prop-content {
        padding-right: 10px;
        padding-left: 10px;
        padding-bottom: 22px;
        font-size: 16px;
    }
    h2 {
        margin-top:20px;
        margin-bottom: 20px;
    }
    h4 {
        text-align: center;
    }
  }
  .p-padding-bottom {
      padding-bottom: 50px;
  }
  @media (max-width: 768px) {
    .business-wrapper{
      display: flex;
      flex-direction: column;
    }
  }
}
.center {
    text-align: center;
}
.page--ui-datepicker {
    .ui-datepicker {
        max-width: rem-calc(400px);
        margin-bottom: rem-calc(32px);
    }
}
</style>
