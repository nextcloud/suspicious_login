# 🔮 Nextcloud Suspicious Login Detection

Detect and warn about suspicious IPs logging into Nextcloud

The app is in incubation stage, so it’s time for you to [get involved! 👩‍💻](https://github.com/nextcloud/suspicious_login#development-setup)

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

When enough data is collected – which is roughly three to four weeks (!) – a first
training run can be started. The training is invoked via the OCC command line tool:

```bash
php -f occ suspiciouslogin:train:mlp
```

This command uses several sensible default that should work for instances of any size.
The ``--stats`` flag is useful to see the measured performance of the trained model
after the training finishes. The duration of the training run depends on the size
of the input training set, but is usually between two to 15 minutes.

The full list of parameters, their description and default values can be seen with

```bash
php -f occ suspiciouslogin:train:mlp --help
```

### Hyper parameter optimization (optional)

To find the best possible parameters for the training it's possible to start a *hyper
parameter optimization* run via the CLI:

```bash
php -f occ suspiciouslogin:optimize:mlp
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


## Development setup

1. ☁ Clone the app into the `apps` folder of your Nextcloud: `git clone https://github.com/ChristophWurst/recommendations.git`
2. 💻 Run `npm i` or `krankerl up` to install the dependencies
3. 🏗 To build the Javascript whenever you make changes, run `npm run dev`
4. ☁ Enable the app through the app management of your Nextcloud or run `krankerl enable`
5. 👍 Partytime! Help fix [some issues](https://github.com/nextcloud/suspicious_login/issues) and [review pull requests](https://github.com/nextcloud/suspicious_login/pulls)
