# Environment Integration Tests #

> WARNING: You should NOT run these tests on an actual production environment.  In theory, it is safe, but it's generally a bad idea.

These tests are meant to test an actual production-like environment externally.  The tests *should* be run from a server that is hosting the application, as some tests need to run CLI commands.

These tests serve a different purpose than the integration tests in the application bundles.  These tests are meant to test the environment end-to-end; not just the application layer.  In general, they do the same thing (though more cursory), but are more likely to detects problems or misconfigurations in the running environment which are not application-level problems.

## How to ##

* copy `config.defaults.ini` to `config.ini` and modify the file for the Ayamel instance you are testing
* run phpunit from this directory

## TODO ##

Right now, CLI commands for indexing and transcoding are not being tested, and they should be.
