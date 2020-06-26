// Test File "AddressSpec.js"
/* global describe, it, expect */

if (!window.Vue) {
  window.Vue = require('vue')
}
const AddressBox = require('../../components/addresses/CpAddressBox.vue')

describe('Address Box', function () {
  it('should have data', function () {
    expect(typeof AddressBox.data).toBe('function')
  })

  it('default data values should be', function () {
    var defaultData = AddressBox.data()
    expect(defaultData.address).toEqual({})
    expect(defaultData.editAddress).toEqual(false)
  })

  // this does not expect an address to show
  it('render corrrectly with props', function () {
    var elem = document.createElement('div')
    const Ctor = window.Vue.extend(AddressBox)
    const vm = new Ctor({
      el: elem,
      propsData: {
        addressLabel: 'Business',
        addressableType: 'App\\Models\\User',
        headingTitle: 'Foo',
        addressableId: 106
      }
    })
    vm.getAddress()
    expect(vm.$el.textContent).toContain('No address found') // no address can be found because the api cannot be tested
    expect(vm.$el.textContent).toContain('Foo')
    expect(vm.address.label).toBe('Business')
    expect(vm.address.addressable_type).toBe('App\\Models\\User')
    expect(vm.address.addressable_id).toBe(106)
    vm.createAddress()
    expect(vm.$el.textContent).toContain('No address found') // no address can be found because the api cannot be tested
    expect(vm.$el.textContent).toContain('Foo')
    expect(vm.address.label).toBe('Business')
    expect(vm.address.addressable_type).toBe('App\\Models\\User')
    expect(vm.address.addressable_id).toBe(106)
  })
})
