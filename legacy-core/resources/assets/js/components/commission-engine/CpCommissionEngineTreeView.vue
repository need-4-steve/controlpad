<template>
    <div id='cp-tree-view-wrapper'>
          <svg :width="svgWidth" :height="svgHeight"></svg>
    </div>
</template>

<script id="CpCommissionEngineTreeView">
const Comm = require('../../resources/CommissionEngineAPIv0.js')
const Auth = require('auth')

module.exports = {
  routing: [{
    name: 'site.CpCommissionEngineTreeView',
    path: '/commission-engine/tree-view',
    meta: {
      title: 'Downline Tree View'
    }
  }],
  data () {
    return {
      Auth: Auth,
      userid: Auth.getAuthId().toString(),
      loading: true,
      svgHeight: null,
      svgWidth: null,
      downline: {
        userid: Auth.getAuthId().toString(),
        name: Auth.getClaims().fullName,
        children: []
      },
      childrenArray: [],
      indexRequest: {
        command: 'mydownlinelvlone',
        orderdir: 'asc',
        userid: '',
        offset: '0',
        limit: '10',
        orderby: 'id',
        systemid: '1'
      }
    }
  },
  mounted () {
    this.indexRequest.userid = this.userid
    this.getTreeData()
  },
  methods: {
    updateHeight (children) {
      if (children) {
        let childrenLength = children * 4
        this.svgHeight = this.svgHeight + childrenLength
      } else {
        return this.svgHeight
      }
    },
    createTree () {
      this.$d3.selectAll('.node').remove()
      this.$d3.selectAll('.link').remove()

      var svg = this.$d3.select('svg')
      var width = +svg.attr('width')
      var height = +svg.attr('height')

      var g = svg.append('g').attr('transform', 'translate(105,0)')

      var tree = this.$d3.cluster()
        .size([height, width - 180])

      this.updateTree(svg, tree, g)
    },
    updateTree (svg, tree, g) {
      var root = this.$d3.hierarchy(this.downline)

      tree(root)

      var link = g.selectAll('.link')
        .data(root.descendants().slice(1))
        .enter().append('path')
        .attr('class', 'link')
        .attr('d', d => {
          return 'M' + d.y + ',' + d.x +
            'C' + (d.parent.y + 100) + ',' + d.x +
            ' ' + (d.parent.y + 100) + ',' + d.parent.x +
            ' ' + d.parent.y + ',' + d.parent.x
        })
      var node = g.selectAll('.node')
        .data(root.descendants())
        .enter().append('g')
        .attr('class', d => { return 'node' + (d.children ? ' node--internal' : ' node--leaf') })
        .attr('transform', d => { return 'translate(' + d.y + ',' + d.x + ')' })

      node.append('circle')
        .attr('r', 2.5)
        .style('fill', d => { return d.data.count > 0 ? '#4988FB' : '#f5f5f5' })

      node.append('text')
        .attr('dy', 3)
        .attr('x', (d) => { return d.children ? -8 : 8 })
        .style('text-anchor', d => { return d.children ? 'end' : 'start' })
        .text(d => `${d.data.firstname || d.data.name}  (${d.data.userid})`)

      node.on('click', d => {
        this.indexRequest.userid = d.data.userid
        Comm.runCommand(this.indexRequest).then(res => {
          d.data.children = res.users
          this.updateHeight(d.data.children.length)
          this.createTree()
        })
      })
    },
    getTreeData () {
      this.svgWidth = document.getElementById('cp-tree-view-wrapper').clientWidth
      this.svgHeight = document.getElementById('cp-tree-view-wrapper').clientHeight * 6
      this.loading = true
      Comm.runCommand(this.indexRequest).then(res => {
        this.loading = false
        if (!res.errors) {
          this.downline.children = res.users
          for (let i = 0; i < this.downline.children.length; i++) {
            this.downline.children[i].name = this.downline.children[i].firstname + ' ' + this.downline.children[i].lastname
            if (this.downline.children[i].count > 0) {
              this.downline.children[i].children = []
            }
          }
        } else {
          this.$toast('Downline isnt available', {error: true})
        }
        this.createTree()
      })
    }
  }
}
</script>

<style lang='scss'>
  #cp-tree-view-wrapper {
    svg {
      padding: 10px 85px 10px 25px;
    }
    .node circle {
      stroke: #777;
      stroke-width: 1.5px;
    }
  }
</style>