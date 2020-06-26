<template>
     <div class="page-button-wrapper">
            <button class="cp-button-standard" type="button" name="button" @click="paginateArray('prev')" v-if="!hidePageButtons" :disabled='currentPage === 0'>Prev</button>
            <button class="cp-button-standard" type="button" name="button" @click="paginateArray('next')" v-if="!hidePageButtons " :disabled='disableNextButton'>Next</button>
            <h3 v-if="!hidePageButtons">{{currentPage}} / {{pages}}</h3>
     </div>
</template>
<script>
    module.exports = {
      data () {
        return {
          disableNextButton: false,
          arrayCurrentPage: [],
          currentPage: 1,
          offset: 0,
          hidePageButtons: false,
          disablePrevButton: false,
          pages: null
        }
      },
      props: {
        data: {
          type: Array
        },
        perPage: {
          Number,
          default: 25
        }
      },
      mounted () {
        this.paginateArray()
      },
      methods: {
        paginateArray (prevOrNext) {
          this.disableNextButton = false
          this.pages = Math.ceil(this.data.length / this.perPage)
          if (prevOrNext === 'next') {
            this.offset += this.perPage
            this.currentPage++
            if (this.offset + this.perPage > this.data.length) {
              this.disableNextButton = true
            }
          }
          if (prevOrNext === 'prev' && this.offset !== 0) {
            this.offset += -this.perPage
            this.currentPage--
          }
          this.arrayCurrentPage = []
          for (var i = this.offset; i < this.data.length; i++) {
            if (this.data.length > this.perPage) {
              if (this.arrayCurrentPage.length < this.perPage) {
                this.arrayCurrentPage.push(this.data[i])
              }
              this.$emit('current-page', this.arrayCurrentPage)
            } else {
              this.hidePageButtons = true
              this.arrayCurrentPage = this.data
              this.$emit('current-page', this.arrayCurrentPage)
            }
          }
        }
      }
    }
</script>

<style lang="scss">

</style>
