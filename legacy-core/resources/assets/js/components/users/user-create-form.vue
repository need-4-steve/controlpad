<template lang="html">
    <div class="user-create-wrapper">
        <div class="cp-box-standard">
            <div class="cp-box-heading">
                <h5>Create</h5>
            </div>
            <div class="cp-box-body">
                <form class="cp-form-standard user-create-form">
                    <label>First Name:</label>
                    <input type="text" :class="{ error: errorMessages.first_name }" placeholder="First Name" v-model="user.first_name">
                    <span v-show="errorMessages.first_name" class="cp-warning-message">{{ errorMessages.first_name }}</span>

                    <label>Last Name:</label>
                    <input type="text" :class="{ error: errorMessages.last_name }" placeholder="Last Name" v-model="user.last_name">
                    <span v-show="errorMessages.last_name" class="cp-warning-message">{{ errorMessages.last_name }}</span>

                    <label>Public ID: </label>
                    <input type="text" :class="{ error: errorMessages.public_id }" placeholder="Public ID" v-model="user.public_id">
                    <span v-show="errorMessages.public_id" class="cp-warning-message">{{ errorMessages.public_id }}</span>

                    <label>Email:</label>
                    <input type="email" :class="{ error: errorMessages.email }" placeholder="Email" v-model="user.email">
                    <span v-show="errorMessages.email" class="cp-warning-message">{{ errorMessages.email }}</span>

                    <label>Password:</label>
                    <input type="password" :class="{ error: errorMessages.password }" placeholder="Password" v-model="user.password">
                    <span v-show="errorMessages.password" class="cp-warning-message">{{ errorMessages.password }}</span>

                    <label>Password Confirmation:</label>
                    <input type="password" :class="{ error: errorMessages.password_confirmation }" placeholder="Confirm Password" v-model="user.password_confirmation">
                    <span v-show="errorMessages.password_confirmation" class="cp-warning-message">{{ errorMessages.password_confirmation }}</span>

                    <cp-input-mask
                    label="Phone Number"
                    mask="###-###-####"
                    :error="errorMessages.phone"
                    v-model="user.phone.number">
                  </cp-input-mask>
                    <div>
                        <label>User Role: </label>
                        <div class="cp-select-standard" :class="{ error: errorMessages.role }">
                          <select v-model="user.role">
                            <option value="" selected>Select Role</option>
                            <option v-for="role in roles" :value="role">{{ role.name }}</option>
                          </select>
                        </div>
                        <span v-show="errorMessages.role" class="cp-warning-message">{{ errorMessages.role }}</span>

                        <div class="role-description cp-panel-standard" v-if="user.role">
                            <h5>{{ user.role.name }} Description: </h5>
                            <p>
                                {{ user.role.description }}
                            </p>
                        </div>
                    </div>
                    <label>Address 1:</label>
                    <input type="text" :class="{ error: errorMessages.address_1 }" placeholder="Street address, P.O. Box" v-model="user.address_1">
                    <span v-show="errorMessages.address_1" class="cp-warning-message">{{ errorMessages.address_1 }}</span>

                    <label>Address 2:</label>
                    <input type="text" :class="{ error: errorMessages.address_2 }" placeholder="Apartment, suite, building, floor" v-model="user.address_2">
                    <span v-show="errorMessages.address_2" class="cp-warning-message">{{ errorMessages.address_2 }}</span>

                    <label>City:</label>
                    <input type="text" :class="{ error: errorMessages.city}" placeholder="City" v-model="user.city">
                    <span v-show="errorMessages.city" class="cp-warning-message">{{ errorMessages.city }}</span>
                    <div class="select-wrapper">
                        <label>State:</label>
                        <div class="cp-select-standard" :class="{ error: errorMessages.state }">
                       <select v-model="user.state">
                         <option :value="{}" selected>Select State</option>
                         <option v-for="state in states" :value="state.value">{{ state.name }}</option>
                       </select>
                        </div>
                       <span v-show="errorMessages.state" class="cp-warning-message">{{ errorMessages.state }}</span>

                    </div>
                    <label>Zip Code:</label>
                    <input type="text" :class="{ error: errorMessages.zip }" placeholder="Zip Code" v-model="user.zip">
                    <span v-show="errorMessages.zip" class="cp-warning-message">{{ errorMessages.zip }}</span>
                    <button v-show="enableCreate" class="cp-button-standard user-create-button" type="button" @click="createUser()">Create</button>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
const Users = require('../../resources/users.js')
const { states } = require('../../resources/states.js')

module.exports = {
  data () {
    return {
      enableCreate: true,
      roles: [],
      states: states,
      user: {
        first_name: '',
        last_name: '',
        public_id: '',
        password: '',
        password_confirmation: '',
        email: '',
        phone: {
          number: ''
        },
        role: {},
        address_1: '',
        address_2: '',
        city: '',
        state: '',
        zip: ''
      },
      errorMessages: {}
    }
  },
  computed: {},
  mounted: function () {
    this.getRoles()
  },
  methods: {
    getRoles: function () {
      Users.adminCreatableRoles()
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, { error: true })
          }
          this.roles = response
        })
    },
    createUser: function () {
      if (this.user.phone.number) {
        this.user.phone.number = this.user.phone.number.replace(/-/g, '').replace(/\(|\)/g, '')
      }
      this.enableCreate = false
      this.$toast('Creating new user...', { dismiss: false })
      Users.create(this.user)
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message
            this.enableCreate = true
            return
          }
          this.errorMessages = {}
          this.$toast('User created successfully.', { dismiss: false })
          this.user = {
            first_name: '',
            last_name: '',
            public_id: '',
            password: '',
            password_confirmation: '',
            email: '',
            phone: {
              number: null
            },
            role: {},
            address_1: '',
            address_2: '',
            city: '',
            state: '',
            zip: ''
          }
          this.enableCreate = true
          this.$forceUpdate()
          return
        })
    }
  },
  components: {
    CpInputMask: require('../../cp-components-common/inputs/CpInputMask.vue')
  }
}
</script>
<style lang="sass">
    .user-create-wrapper {
        max-width: 600px;
        .cp-box-standard {
            margin: 0;
        }
        .user-create-form {
            overflow: hidden;
            label {
                display: block
            }
            .user-create-button {
                float: right;
                margin-top: 5px;

            }
        }
        .role-description {
            margin-bottom: 5px;
        }
    }
</style>
