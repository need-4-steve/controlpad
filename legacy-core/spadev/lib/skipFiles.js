const micromatch = require('micromatch')

const patterns = [
  // scss
  'assets/sass/store.scss',
  'assets/sass/store-styles.scss',

  // angular stuff, old news
  'assets/js/main.js',
  'assets/js/app.js',
  'assets/js/angApp.js',
  'assets/js/directives.js',
  'assets/js/services.js',
  'assets/js/controllers/**/*',
  'assets/js/directives/**/*',
  'assets/js/services/**/*',

  // we don't want tests in the build; that would be silly
  'assets/js/tests/**/*',

  // these aren't currently being used, but may in the future; eventually
  // we should be able to remove all of these.
  'assets/js/components/passport/AuthorizedClients.vue',
  'assets/js/components/passport/Clients.vue',
  'assets/js/components/passport/PersonalAccessTokens.vue',
  'assets/js/components/rep-locator/index.vue',
  'assets/js/components/sort-tools.vue',
  'assets/js/components/store/about-me.vue',
  'assets/js/cp-common/inputs/CPTextArea.vue',
  'assets/js/cp-common/inputs/CPTypeahead.vue',
  'assets/js/cp-common/navigation/CPTabs.vue',
  'assets/js/cp-common/tables/TableStandard.vue',

  // these are going to be removed
  'assets/js/libraries/vue-typeahead.common.js',
  'assets/js/components/typeahead.vue'
]

module.exports = (filename) => {
  return micromatch.isMatch(filename, patterns)
}
