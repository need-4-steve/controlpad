<template lang="html">
    <div class="typeahead-wrapper">
        <div class="search-input" v-bind:class="custom_class">
            <input type="text" autofocus autocomplete="off" v-model="query" v-bind:placeholder="placeholder"
                @keydown.down="down"
                @keydown.up="up | debounce 700"
                @keydown.enter="hit"
                @keydown.esc="reset"
                @blur="reset"
                @input="update"/>
            <ul v-if="model_type == 'product'">
                <li v-for="item in items" :class="activeClass($index)" @mousedown="hit" @mousemove="setActive($index)">
                    <div>
                        <img :src="item.default_media.url_xxs"/>
                        <span v-text="item.name" class="item-name"></span>
                    </div>
                    <span v-text="item.items[0].msrp.price | currency"></span>
                </li>
            </ul>
            <ul v-else>
                <li v-for="item in items" :class="activeClass($index)" @mousedown="hit" @mousemove="setActive($index)">
                    <span v-text="item.full_name"></span>
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
const VueTypeahead = require('../libraries/vue-typeahead.common.js');

module.exports = {
    extends: VueTypeahead,
    data: function() {
        return {
            src: this.api_src + '?searchTerm=',
            queryParamName: false,
            minChars: 1,
            limit: 15
        }
    },
    props: {
        api_src: {
            type: String,
            required: true
        },
        custom_class: {
            type: Array
        },
        selected: {
            type: Object,
            twoWay: true
        },
        placeholder: {
            type: String
        },
        model_type: {
            type: String
        },
        selected_array: {
            type: Array,
            twoWay: true
        }
    },
    methods: {
        onHit: function(item){
            this.selected = item;
            if (this.model_type == 'product') {
                this.selected_array.push(this.selected);
            }
        }
    },
}
</script>
<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .typeahead-wrapper {
        .search-input {
            position: relative;
            height: 30px;
            z-index: 10;
            &.browse {
                display: inline-block;
                width: 70%;
                margin-right: 30px;
            }
            input {
                height: 100%;
                width: 100%;
                border: none;
                text-indent: 25px;
                &:focus {
                    outline: none;
                }
            }
            ul {
                position: absolute;
                padding-left: 0;
                background: #fff;
                list-style-type: none;
                width: 100%;
                li {
                    padding: 5px;
                    display: flex;
                    -webkit-display: flex;
                    justify-content: space-between;
                    -webkit-justify-content: space-between;
                    border-bottom: solid 1px #ddd;
                    cursor: pointer;
                    white-space: nowrap;
                    &:hover {
                        background: $cp-lighterGrey;
                    }
                    span {
                        width: 100px;
                        max-width: 100px;
                    }
                    .item-name {
                        margin-left: 20px;
                    }
                }
            }
            .active {
                background-color: $cp-lighterGrey;
            }
        }
    }
</style>
