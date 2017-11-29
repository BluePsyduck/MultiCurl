# Changelog

## Unreleased

## 2.0.1 - 2017-11-29

### Added

- Deep cloning of request entities.

### Changed

- `CurlWrapper->getInfo()` now accepts `null` as argument to match the underlying `curl_getinfo()`.

## 2.0.0 - 2017-11-20

### Added

- Auto-execution of requests when adding them to the manager.
- Auto-removal of finished requests from the manager to allow for garbage collection.
- Ability to set an limit of parallel executed requests in the manager.
- `onInitializeCallback` to the `Request` to allow manipulation of the underlying cUrl request.
- `Header` entity used in the request and response.

### Changed

- Renamed the `Manager` to `MultiCurlManager`.
- Request method constants to a separate class.
- `Request` now manages the header values with the new `Header` entity.
- `Response->getHeaders()` no longer returns a Collection of Collections, but an array of `Header` entities.
- `Response->getLastHeader()` now returns a `Header` entity, if a header is present.

### Removed

- Support for PHP 5.x. Now the library requires at least PHP 7.0.
- Methods `execute()`, `executeRequest()` and `removeRequest()` from the manager.
- Deprecated method `waitForRequests()` from the manager.
- `Collection` class. Now simple arrays or the new `Header` entity are used.
- `Request->setCurl()` and `Request->setResponse()`. The entity now manages these instances on its own. 

## 1.2.2 - 2017-11-13

### Added

- Method `executeRequest()` to directly add and execute a new request in the manager.
- Method `removeRequest()` to remove no longer needed request instances from the manager.

## 1.2.1 - 2016-08-14

### Added

- New methods `waitForSingleRequest()` and `waitForAllRequests()` to the manager.

### Deprecated

- Method `waitForRequests()`. Use the new `waitForAllRequests()` instead.

## 1.2.0 - 2016-05-18

### Added

- Support for multiple headers when the request gets redirected.

### Changed

- Headers in the response entity to support multiple headers.

## 1.1.1 - 2015-08-16

### Fixed

- [PHP bug #61141](https://bugs.php.net/bug.php?id=61141) 

## 1.1.0 - 2015-07-12

### Added

- Support of Basic authentication in the requests.

### Changed

- Major refactoring of the entity classes for easier use of the library.

## 1.0.0 - 2015-03-23

- Initial release of the library.