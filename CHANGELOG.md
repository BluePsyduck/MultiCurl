# Changelog

## Unreleased

## [1.2.2] - 2017-11-13

### Added

- Method `executeRequest()` to directly add and execute a new request in the manager.
- Method `removeRequest()` to remove no longer needed request instances from the manager.

## [1.2.1] - 2016-08-14

### Added

- New methods `waitForSingleRequest()` and `waitForAllRequests()` to the manager.

### Deprecated

- Method `waitForRequests()`. Use the new `waitForAllRequests()` instead.

## [1.2.0] - 2016-05-18

### Added

- Support for multiple headers when the request gets redirected.

### Changed

- Headers in the response entity to support multiple headers.

## [1.1.1] - 2015-08-16

### Fixed

- [PHP bug #61141](https://bugs.php.net/bug.php?id=61141) 

## [1.1.0] - 2015-07-12

### Added

- Support of Basic authentication in the requests.

### Changed

- Major refactoring of the entity classes for easier use of the library.

## [1.0.0] - 2015-03-23

- Initial release of the library.