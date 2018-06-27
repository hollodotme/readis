# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com).

## [2.2.0] - 2018-06-27

### Added

* Selected database will be preserved after page reload 

## [2.1.0] - 2018-05-14

### Added

* Proper output handling, if key data could not be retrieved from ajax call

**Related to [issue #8]:**

* Fallback handling for disabled commands CONFIG, INFO and SLOWLOG
* Verbose error descriptions or hints, if commands are disabled

**Related to [issue #9]:**

* Output handling for keys of type "set"
* Output handling for keys of type "zset" (sorted sets)
* Output handling for keys of type "list"
* New output of all elements/members/fields in lists, (sorted) sets and hashes
* HyperLogLog prettifier that adds a hint on HLL encoded values

## [2.0.0] - 2018-04-03

### Added

 * Prettified view of compact JSON string values + raw JSON view
 * Ability to use deeplinks to a specific database of a server
 * Realtime monitor for connected clients and input/output in KB/sec.
 * Favicons
 
### Changed

 * Migrated UI to bootstrap v4
 * Database select becomes scrollable, if list exceeds the 400px
 * Simplified installation 

## [1.1.3] - 2017-01-03

### Fixed

 * Abandoned dependency to `fortuneglobe/icehawk` (now `icehawk/icehawk`) - [#1](https://github.com/hollodotme/readis/issues/1)
 * Updated all composer dependencies and .lock file

## [1.1.2] - 2016-03-03

### Fixed

 * Removed hard-coded test data

## [1.1.1] - 2016-02-03

### Added

 * Number of database is displayed, even when an expressive name is mapped

### Fixed

 * Missing whitespace in default database name

## [1.1.0] - 2016-01-30

### Added

 * Database mapping for more expressive database names.
	See [README](https://github.com/hollodotme/readis/blob/v1.1.0/README.md#sample-server-configuration) for more details.

## [1.0.1] - 2015-12-16

### Fixed

 * Default settings in `config/app.sample.php`

## [1.0.0] - 2015-12-06

First stable release.

[2.2.0]: https://github.com/hollodotme/readis/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/hollodotme/readis/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/hollodotme/readis/compare/v1.1.3...v2.0.0
[1.1.3]: https://github.com/hollodotme/readis/compare/v1.1.2...v1.1.3
[1.1.2]: https://github.com/hollodotme/readis/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/hollodotme/readis/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/hollodotme/readis/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/hollodotme/readis/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/hollodotme/readis/tree/v1.0.0

[issue #8]: https://github.com/hollodotme/readis/issues/8
[issue #9]: https://github.com/hollodotme/readis/issues/9