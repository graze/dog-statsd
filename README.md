# graze/statsd-dd

[![Latest Version on Packagist](https://img.shields.io/packagist/v/graze/statsd-dd.svg?style=flat-square)](https://packagist.org/packages/graze/statsd-dd)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/graze/statsd-dd/master.svg?style=flat-square)](https://travis-ci.org/graze/statsd-dd)
[![Total Downloads](https://img.shields.io/packagist/dt/graze/statsd-dd.svg?style=flat-square)](https://packagist.org/packages/graze/statsd-dd)

Client to talk to [DataDogs StatsD Agent](http://docs.datadoghq.com/guides/dogstatsd) Forked from:
[League/StatsD](https://github.com/thephpleague/statsd)

## Install

Via Composer

``` bash
$ composer require graze/statsd-dd
```

## Usage

### Configuring

```php
$statsd = new Graze\DogStatsD\Client();
$statsd->configure(array(
    'host' => '127.0.0.1',
    'port' => 8125,
    'namespace' => 'example'
));
```

OR

```php
$statsd1 = DogStatsD\Client::instance('server1')->configure(array(...));
$statsd2 = DogStatsD\Client::instance('server2')->configure(array(...));
```

The StatsD client waits for `ini_get('default_socket_timeout')` seconds when opening the socket by default. To reduce
this timeout, add `'timeout' => <float>` to your config.

The StatsD client will throw a `ConnectionException` if it is unable to send data to the StatsD server. You may choose
to disable these exceptions and log a PHP warning instead if you wish. To do so, include the following in your config:

```
    'throwConnectionExceptions' => false
```

If omitted, this option defaults to `true`.

#### Core StatsD implementation

To use this with a core statsd implementation include the following in your configuration:
```
'dataDog' => false
```

### Methods

#### Counters

```php
$statsd->increment('web.pageview');
$statsd->decrement('storage.remaining');
$statsd->increment(array(
    'first.metric',
    'second.metric'
), 2);
$statsd->increment('web.clicks', 1, 0.5);
```

#### Gauges

```php
$statsd->gauge('api.logged_in_users', 123456);
```

#### Sets

```php
$userID = 23;
$statsd->set('api.unique_logins', $userID);
```

#### Timers

```php
$statsd->timing('api.response_time', 256);
```

#### Timing Blocks

```php
$statsd->time('api.dbcall', function () {
    // this code execution will be timed and recorded in ms
});
```

#### Tags

```php
$statsd->increment('web.pageview', 1, ['page' => 'some/page']);
$statsd->guage('api.logged_in_users', 123456, ['environement' => 'live']);
$statsd->set('api.unique_logins', $userID, ['tag']);
$statsd->timing('api.response_time', 245, ['end-point' => 'page', 'env' => 'test']);
```

#### Events

```php
$statsd->event(
    'build.success',
    'The build super_awesome_application_build_1 has completed',
    [
        'time' => time()
        'alert' => Client::ALERT_SUCCESS,
    ],
    [
        'environment' => 'live',
    ]
);
```

#### Service Check

```php
$statsd->serviceCheck(
    'service.api.account',
    Client::STATUS_OK,
    [
        'host' => 'this.hostname.com',
        'time' => time(),
    ],
    [
        'environement' => 'staging',
    ]
);
```

## Testing

``` bash
$ make test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email harry.bragg@graze.com instead of using the issue tracker.

## Credits

- [Harry Bragg](https://github.com/h-bragg)
- [All Contributors](../../contributors)

### Forked from [thephpleague/statsd](https://github.com/thephpleague/statsd):

- [Marc Qualie](https://github.com/marcqualie)
- [League/StatsD Contributors](https://github.com/thephpleague/statsd/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
