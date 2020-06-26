<template lang="html">
  <div class="custom-links-create">
    <h4>Create Custom Links</h4>
    <hr />
    <cp-input
      label="Name"
      type="text"
      :error="validationErrors['name']"
      v-model="customLink.name"></cp-input>
    <cp-input
      label="URL"
      type="text"
      :error="validationErrors['url']"
      v-model="customLink.url"></cp-input>
      <div class="line-wrapper">
          <label>Open link in a new tab</label>
          <input class="toggle-switch" type="checkbox" v-model="customLink.open_in_new_tab">
      </div>
      <button class="cp-button-standard add-links-button" @click="add()">Add</button>
      <br />
      <label for="">Custom links:</label>

      <ul class="added-custom-links">
        <li v-for="(link, index) in addedLinks">
           <div class="col1">{{ link.name }}: {{ link.url }}</div>
          <div class="col2"><i class="mdi mdi-close delete-custom-link pointer" @click="remove(index, link.id)"></i></div>
        </li>
      </ul>
 </div>
</template>

<script>
const CustomLinks = require('../../resources/custom-links.js')

module.exports = {
  data () {
    return {
      addedLinks: [],
      customLink: {
        name: '',
        url: '',
        open_in_new_tab: 1
      },
      validationErrors: {}
    }
  },
  mounted () {
    this.getIndex()
  },
  methods: {
    getIndex () {
      CustomLinks.index({ type: 'corporate_rep_site_links' })
        .then((response) => {
          if (response.error) {
            return
          }
          this.addedLinks = response
        })
    },
    add () {
      this.validationErrors = {}
      let newLink = JSON.parse(JSON.stringify(this.customLink))
      if (!newLink.open_in_new_tab) {
        newLink.open_in_new_tab = 0
      }
      CustomLinks.create(newLink)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.addedLinks.push(response)
          this.customLink = {name: '', url: '', open_in_new_tab: 1}
        })
    },
    remove (index, id) {
      CustomLinks.remove(id)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            this.$toast('Failed to delete.', {error: true})
            return
          }
          this.addedLinks.splice(index, 1)
      })
  }
 },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.custom-links-create {
  width: 100%;
  text-align: left;
  .added-custom-links {
    width: 100%;
    margin-top: 5px;
    margin-bottom: 5px;
    padding-left: 5px;
    li {
      margin-top: 5px;
      margin-bottom: 5px;
      list-style: none;
      background-color: $cp-lighterGrey;
      padding: 3px;
      border-radius: 4px;
      display: flex;
      justify-content: space-between;
      .col1 {
        width:97%;
        overflow-wrap: break-word;
      }
      .col2 {
      }
    }
  }
  .add-links-button {
    float: right;
    margin-top: 5px;
  }

}
</style>
