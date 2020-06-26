<template lang="html">
  <div class="cp-box-standard search-term-wrapper">
    <div class="cp-box-heading">
      SEARCH TERMS
    </div>
    <div class="cp-box-body">
      <span class="seperate">Separate terms with commas to add a list</span>
      <form class="" @submit.prevent>
        <div class="search-term-input">
          <cp-input
          label="Name"
          type="text"
          :error="null"
          v-model="selectedTag"></cp-input>
          <button class="cp-button-standard" @click="addTag()">Add</button>
        </div>
        <ul class="cp-list-tags">
          <li v-for="(tag, index) in newTags">
            <span>{{tag}}</span>
            <span><i class="mdi mdi-close pointer" @click="deleteTag(index)" v-show="newTags.length > 0"></i></span>
          </li>
        </ul>
      </form>
    </div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      selectedTag: [],
      newTags: []
    }
  },
  props: {
    tags: {
      default () {
        return []
      }
    },
    validationErrors: {
      default () {
        return {}
      }
    }
  },
  mounted () {
    this.initPreviousTags()
  },
  methods: {
    initPreviousTags () {
      var tags = []
      for (var i = 0; i < this.tags.length; i++) {
        if (this.tags[i].name) {
          tags.push(this.tags[i].name)
        }
      }
      this.newTags = tags
      this.$emit('new-tags', this.newTags)
    },
    addTag () {
      if (this.selectedTag === null) {
        return false
      }
      let tags = this.selectedTag.split(', ')
      for (var i = 0; i < tags.length; i++) {
        this.newTags.push(tags[i])
      }
      this.$emit('new-tags', this.newTags)
      this.selectedTag = null
    },
    deleteTag (index) {
      this.newTags.splice(index, 1)
      this.$emit('new-tags', this.newTags)
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
.search-term-wrapper {
  .search-term-input {
    text-align: center;
    span {
      display: inline-block;
    }
    button {
      margin-left: 5px;
    }
    padding-right: 5%;
    padding-left: 5%;
  }
}
</style>
