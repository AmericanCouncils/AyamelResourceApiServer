# ACMutateBundle #

This bundle provides container services for loading the Mutate transcoder in your own code.

## Services ##

* `mutate.transcoder` - will return an instance of `AC\Mutate\Transcoder`, automatically registering any tagged Adapters, Presets, Jobs and Listeners
* `mutate.application` - will return an instance of `AC\Mutate\Application\Application`, which lets you run cli commands provided with the library from within your own code

## Tags ##

Various container tags are implemented to allow easy registration of custom Adapters, Presets, Jobs, and even Mutate application commands.  See the list below:

* `mutate.transcoder.adapter`
* `mutate.transcoder.preset`
* `mutate.transcoder.job`
* `mutate.transcoder.listener`
* `mutate.application.command`

## Todo ##

* Implement services as they become available