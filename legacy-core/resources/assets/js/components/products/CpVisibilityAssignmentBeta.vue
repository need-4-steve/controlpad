<template lang="html">
  <div class="cp-box-standard visibility-wrapper">
    <div class="cp-box-heading">
      VISIBLE TO
    </div>
    <div class="cp-box-body visible-inputs">
      <ul class="">
          <li class="" v-for="(visibility, index) in visibilities">
              <input type="checkbox"
                      v-model="newSelectedVisibilities"
                      :name="visibility.name"
                      :value="{ id: visibility.id, name: visibility.name }"
                      :id='visibility.id'
                      @change="$emit('selected-visibilities', newSelectedVisibilities)"/>
              <label for="visibilities">
                  <span>{{ visibility.name }}</span>
                  <cp-tooltip :options="{ content: visibility.description }"></cp-tooltip>
              </label>
              <span :class="{ 'cp-validation-errors': validationErrors['visibilities.' + (index + 1) ] }" v-if="validationErrors['visibilities.' + (index + 1)]">{{ validationErrors['visibilities.' + (index + 1)][0] }}</span>
          </li>
      </ul>
    </div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      newSelectedVisibilities: [],
      visibilities: []
    }
  },
  props: {
    selectedVisibilities: {
      type: Array,
      default () {
        return []
      }
    },
    validationErrors: {
      default () {
        return {}
      }
    },
    starterKit: {
      default () {
        return {
          show: false,
          value: null
        }
      }
    }
  },
  created () {
    this.newSelectedVisibilities = this.selectedVisibilities
    if (this.starterKit.show) {
      this.visibilities = [
        {
          id: 4,
          name: 'Registration',
          description: 'Registration purchase.'
        },
        {
          id: 5,
          name: 'Wholesale',
          description: 'Wholesale purchase.'
        }
      ]
    } else {
      this.visibilities = [
        {
          id: 1,
          name: 'Corp Retail',
          description: 'Retail store for corporate.'
        },
        {
          id: 2,
          name: 'Affiliate',
          description: 'Affiliate stores.'
        },
        {
          id: 3,
          name: 'Reseller Retail',
          description: 'Reseller sites.'
        },
        {
          id: 5,
          name: 'Wholesale',
          description: 'Wholesale purchase.'
        },
        {
          id: 6,
          name: 'Preferred Retail',
          description: 'Preferred Retail purchase in backoffice'
        }
      ]
    }
  },
  methods: {},
  components: {
    CpTooltip: require('../../custom-plugins/CpTooltip.vue')
  }
}
</script>

<style lang="scss">
.visibility-wrapper {
  .visible-inputs {
    overflow: hidden;
    ul {
      padding: 0px;
      margin: 5px;
    }
    li {
      list-style: none;
      display: inline;
    }
    input {
      display: inline-block;
      width: 20px;
      height: 15px;
    }
    label {
      margin-bottom: 10px;
      margin-right: 15px;
    }
  }
}
</style>
