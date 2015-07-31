# Introduction #

While making changes to source code, programmers sometimes unintentionally break already working things (People make mistakes and programmers are one of them too ;-). Automated testing helps detect such errors early.

# PHP unit tests #

We use PHPUnit for testing PHP code. PHP unit tests are located in tests/src directory.

See scripts/README for instructions on how to install PHPUnit on windows. Use your package manager on linux systems. Or refer to PHPUnit website.

PHPUnit test looks like this



## Running a full test suite ##

Run ` ./scripts/run_tests.sh ` or ` ./scripts/run_tests.bat ` depending on your operating system.

## Running individual tests ##

This is much faster than running a full test suite. Useful if you are changing one class and want to test often during developement.

Run ` ./scripts/run_tests.sh --filter <regexp> ` or ` ./scripts/run_tests.bat --filter <regexp> ` where ` <regexp> ` is a regular expression of test name, e.g. ` testDefault* ` or ` ConfigUtilsTest `

# Javascript unit tests #

Our javascripts tests are run using modified version of jsTestDriver.

TODO: howto running javascript tests