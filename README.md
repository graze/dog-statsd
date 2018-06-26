# graze/dog-statsd

![Giphy](http://media1.giphy.com/media/ALCI3eTii7qOk/giphy.gif)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/graze/dog-statsd.svg?style=flat-square)](https://packagist.org/packages/graze/dog-statsd)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/graze/dog-statsd/master.svg?style=flat-square)](https://travis-ci.org/graze/dog-statsd)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/graze/dog-statsd.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/dog-statsd/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/graze/dog-statsd.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/dog-statsd)
[![Total Downloads](https://img.shields.io/packagist/dt/graze/dog-statsd.svg?style=flat-square)](https://packagist.org/packages/graze/dog-statsd)

Client to talk to [DataDogs StatsD Agent](http://docs.datadoghq.com/guides/dogstatsd) Forked from:
[League/StatsD](https://github.com/thephpleague/statsd)

For more information on the metric, see: [Datadogs metrics guide](http://docs.datadoghq.com/guides/metrics/).

## Install

Via Composer

``` bash
$ composer require graze/dog-statsd
```

## Usage

### Configuring

```php
$statsd = new Graze\DogStatsD\Client();
$statsd->configure([
    'host' => '127.0.0.1',
    'port' => 8125,
    'namespace' => 'example',
]);
```

OR

```php
$statsd1 = DogStatsD\Client::instance('server1')->configure([...]);
$statsd2 = DogStatsD\Client::instance('server2')->configure([...]);
```

The StatsD client waits for `ini_get('default_socket_timeout')` seconds when opening the socket by default. To reduce
this timeout, add `'timeout' => <int>` to your config.

The StatsD client will either throw a `ConnectionException`, throw a warning or ignore all errors if it is unable to
send data to the StatsD server. This can be configured using the `onError` property

```
    'onError' => 'error' // 'error', 'exception' or 'ignore'
```

By default this is set to `'error'`

#### Core StatsD implementation

To use this with a core statsd implementation (without the extra features DataDog have added) include the following in
your configuration:
```
'dataDog' => false
```

### Methods

#### Counters

```php
$statsd->increment('web.pageview');
$statsd->decrement('storage.remaining');
$statsd->increment([
    'first.metric',
    'second.metric'
], 2);
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

#### Histogram

```php
$result = $db->fetch();
$statsd->histogram('db.results', count($result), 0.5);
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

##### Tags Processors

You can add tag processors to inject tags at runtime for each metric.

```php
$statsd->addTagProcessor(function (array $tags) {
    $tags['new-key'] = 'new-value';
    return $tags;
});
```

#### Events

```php
$statsd->event(
    'build.success',
    'The build super_awesome_application_build_1 has completed',
    [
        'time'  => time(),
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

#### Default Tags

Send the same base tags with every request

```php
$client = new Client();
$client->configure([
    'tags' => [
        'env'     => 'live',
        'release' => 'app-2.3.1',
    ],
]);
```

## Testing

``` bash
$ make test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@graze.com instead of using the issue tracker.

## Credits

- [Harry Bragg](https://github.com/h-bragg)
- [All Contributors](../../contributors)

### Forked from [thephpleague/statsd](https://github.com/thephpleague/statsd):

- [Marc Qualie](https://github.com/marcqualie)
- [League/StatsD Contributors](https://github.com/thephpleague/statsd/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
