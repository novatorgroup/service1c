# HTTP Service 1C

Component for executing requests to HTTP Services 1C

### Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist novatorgroup/service1c "*"
```

or add

```
"novatorgroup/service1c": "*"
```

to the require section of your `composer.json` file.

### Usage
```php
    $service = new HttpService([
        'host' => 'http://host.com',
        'base' => 'base'
        'login' => 'login',
        'password' => 'password',
        'curlOptions' => [
            CURLOPT_CONNECTTIMEOUT_MS => 200,
            CURLOPT_TIMEOUT_MS => 1000,
        ]
    ]);

    $response = $service->get('command', ['param', 'key' => 'value']);
    
    echo $response->code;
    echo $response->error;
    echo $response->result;
    
    // Request to:
    // http://host.com/base/hs/command/param?key=value
```