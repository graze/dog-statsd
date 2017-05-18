# Change Log

All Notable changes to `graze/dog-statsd` will be documented in this file

## v0.3 - 2017-05-18

### Added
 - Added A StreamWriter which has an exponential back-off when attempting to send the udp messages to prevent blocking
 - Added An echo server for functional testing
 - Added a fix for sending long messages
 - Added Histogram method

## v0.2 - 2016-03-02

### Added
 - Added default tags to be sent with every request
 - Added a Dockerfile

### Changed
 - Renamed the option `throwConnectionExceptions` to `throwExceptions`

## v0.1 - 2015-11-04

### Added
 - Initial release
