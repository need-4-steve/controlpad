/*
* import Step form 'path'
* Step.init({ one: true, two: false, three: false }, 3000)
* first argument is the steps, and the default step is set to true
* second argument is a timeout by miliseconds to delay the changing of steps ( for css transitions )
*/
module.exports = {
  steps: {},
  totalSteps: 0,
  timeOut: 0,
  // set the steps object (all should contain boolean values, only one set to true)
  init (steps, timeOut = 0) {
    this.steps = steps
    this.totalSteps = Object.keys(steps).length
    this.timeOut = timeOut
  },
  get (key) {
    return this.steps[key]
  },
  // go to the step before the currently active step
  previous () {
    var previousStep = ''
    for (let key in this.steps) {
      if (this.steps[key] === true) {
        this.steps[key] = false
        this.steps[previousStep] = true
        return
      }
      previousStep = key
    }
  },
  // go to the next step after the currently active step
  next () {
    var next = false
    var previous = ''
    var count = 0
    var $this = this
    for (let key in this.steps) {
      count = count + 1
      if (next) {
        this.steps[previous] = false
        setTimeout(function () {
          $this.steps[key] = true
        }, $this.timeOut)
        return
      }
      if (this.steps[key] === true) {
        previous = key
        next = true
        if (count === this.totalSteps) {
          this.steps[key] = false
          setTimeout(function () {
            $this.steps[Object.keys($this.steps)[0]] = true
          }, $this.timeOut)
        }
      }
    }
  },
  // skip to a specific step
  skipTo (step) {
    for (let key in this.steps) {
      if (key === step) {
        this.steps[key] = true
      } else {
        this.steps[key] = false
      }
    }
  }
}
