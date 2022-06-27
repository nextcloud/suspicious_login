# Changelog
All notable changes to this project will be documented in this file.

## 4.2.0 - 2022-06-28
### Added
- Nextcloud 24 support
- PHP8.1 support
### Changed
- Background jobs run at off-peak times
- New and updated translations
### Removed
- Nextcloud 21 support (EOL)
- PHP7.4 support (EOL)

## 4.0.0 – 2021-03-11
### Added
- Nextcloud 21 support
- PHP8 support
### Changed
- ML algorithm replaced and improved
- New and updated translations
### Removed
- Nextcloud 17-20 support
- PHP7.2 support

## 3.2.1 - 2020-09-02
### Fixed
- Packaging of development dependencies

## 3.2.0 - 2020-08-31
### Added
- Nextcloud 20 support
### Changed
- New and updated translations
- Updated dependencies

## 3.1.0 - 2020-02-11
### Added
- Nextcloud 19 support
### Changed
- New and updated translations
- Updated dependencies
### Fixed
- Model persistence type error on php7.4

## 3.0.1 - 2019-12-12
### Changed
- New and updated translations
- Updated dependencies
### Fixed
- JavaScript vulnerability in `serialize-javascript` dependency

## 3.0.0 - 2019-12-02
### Added
- php7.4 support
### Changed
- New and updated translations
- Updated dependencies
### Fixed
- IPv6 optimizer
### Removed
- php7.1 support

## 2.4.1 - 2019-10-23
### Fixed
- Missing data collection of requests that use an app password

## 2.4.0 - 2019-10-23
### Changed
- New and updated translations
- Use new @nextcloud npm packages
### Fixed
- Login aggregation background job on PostgreSQL
- Readme clone link

## 2.3.0 - 2019-09-17
### Added
- Persist notification status of alerts in the DB
### Changed
- New and updated translations
### Fixed
- Peak detection log message

## 2.2.0 - 2019-09-11
### Added
- Alert peak detection

## 2.1.0 - 2019-09-10
### Added
- Nextcloud 18 support
- IPv6 classification
- Send email when a suspicious login is detected
### Changed
- New and updated translations

## 2.0.2 - 2019-09-03
### Fixed
- Event propagation of suspicious logins

## 2.0.1 - 2019-08-28
### Changed
- New and updated translations
### Fixed
- Better handling of deserialization errors
- Vulnerable npm dependencies

## 2.0.0 - 2019-08-26
### Added
- Nextcloud 17 support
### Changed
- New and updated translations
- Requires Postgres 9.5+
### Fixed
- Log level logging when no models have been trained yet
### Removed
- Nextcloud 16 support

## 1.0.0 - 2019-04-24
### Added
- First production-ready implementation
- Nextcloud 17 (development) support
### Changed
- New and updated translations
### Fixed
- Typo on settings page
- Duplicate address collection of the same request
- IPv6 handling
