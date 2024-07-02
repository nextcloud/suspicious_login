<!--
  - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# 🔮 Nextcloud Suspicious Login Detection

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/suspicious_login)](https://api.reuse.software/info/github.com/nextcloud/suspicious_login)
![Downloads](https://img.shields.io/github/downloads/nextcloud/suspicious_login/total.svg)
[![Build Status](https://travis-ci.com/nextcloud/suspicious_login.svg?branch=master)](https://travis-ci.com/nextcloud/suspicious_login)
[![Dependabot Status](https://api.dependabot.com/badges/status?host=github&repo=nextcloud/suspicious_login)](https://dependabot.com)

Detect and warn about suspicious IPs logging into Nextcloud

The app is still under development, so it’s time for you to [get involved! 👩‍💻](https://github.com/nextcloud/suspicious_login#development-setup)

## Installation

### Nextcloud 25 and newer

The app is [shipped](https://docs.nextcloud.com/server/latest/developer_manual/app_publishing_maintenance/release_process.html#shipped-apps) and comes with the installation of Nextcloud Server. No additional steps are necessary.

### Nextcloud 24 and older

Old versions of this app remain available through the [app store](https://apps.nextcloud.com/apps/suspicious_login). They can be [installed through Nextcloud's app management UI](https://docs.nextcloud.com/server/latest/admin_manual/apps_management.html#managing-apps). 

> [!NOTE]
> Newer versions of the app are not included in the app store since it is now a shipped app.

## Releases and CHANGELOGs

As a shipped app:

- changes are posted within the [Nextcloud Server changelog](https://nextcloud.com/changelog/).
- releases are **not** posted in this GitHub repository, but they are [tagged](https://github.com/nextcloud/suspicious_login/tags) for code perusal.
- it is automatically kept up-to-date with each Nextcloud Server release.

## How it works

### Data collection

Once this app is enabled, it will automatically start tracking (IP, uid) tuples from
successful logins on the instance and feed them into the `login_address` table. This
insert operation is executed for the majority of requests (client authenticate on
almost all requests) and therefore has to be fast. In a background job, these rows
will be transformed into an aggregated format that is suitable for the training of
the neural net. The (IP, uid) tuple becomes (IP, uid, first_seen, last_seen, seen) so
that we know which (IP, uid) tuple has been seen first and last. The aggregated data
is a compressed format of the raw data. The original data gets deleted and thus the
database does not need much space for the collected login data.

### Neural net

When enough data is collected – which by default is 60 days (!) – a first
training run can be started.

The app registers a background job that invokes the training once a day.
As long as there isn't sufficient data, no trained model is generated.

#### Manual training

The training can also be invoked via the OCC command line tool:

```bash
php -f occ suspiciouslogin:train
```

This command uses several sensible default that should work for instances of any size.
The ``--stats`` flag is useful to see the measured performance of the trained model
after the training finishes. The duration of the training run depends on the size
of the input training set, but is usually between two to 15 minutes.

The full list of parameters, their description and default values can be seen with

```bash
php -f occ suspiciouslogin:train --help
```

### Hyper parameter optimization (optional)

To find the best possible parameters for the training it's possible to start a *hyper
parameter optimization* run via the CLI:

```bash
php -f occ suspiciouslogin:optimize
```

This command uses the heuristic *simulated annealing* algorithm to find optimal
parameter sets in the multidimensional parameter space. By default this will do **100**
steps consisting of five training runs per step, hence this command might take a few
days to execute on large instances. On smaller ones it will also take a few hours.


### Login classification

As soon as the first model is trained, the app will start classifying (IP, uid) tuples
on login. In contrast to the data collection it won't consider requests authenticated
via an app password as suspicious. Should it detect a password login where the (IP,
uid) is classified as suspicious by the trained model, it will add an entry to the
``suspicious_login`` table, including the timestamp, request id and URL.

## Configuration

By default notifications about suspicious logins contain a link (button) to lookup more info about the noted IP address (geography, ISP). This link can be disabled if it isn't desired:

`occ config:app:set --value 0 suspicious_login show_more_info_button`

## Development setup

1. ☁ Clone the app into the `apps` folder of your Nextcloud: `git clone https://github.com/nextcloud/suspicious_login.git`
2. 💻 Run `npm i` or `krankerl up` to install the dependencies
3. 🏗 To build the Javascript whenever you make changes, run `npm run dev`
4. ☁ Enable the app through the app management of your Nextcloud or run `krankerl enable`
5. 👍 Partytime! Help fix [some issues](https://github.com/nextcloud/suspicious_login/issues) and [review pull requests](https://github.com/nextcloud/suspicious_login/pulls)
