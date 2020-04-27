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
    $service = new \novatorgroup\service1c\HttpService([
        'host' => 'http://host.com', //required
        'base' => 'base', //required
        'login' => 'login',
        'password' => 'password',
        'curlOptions' => [
            CURLOPT_CONNECTTIMEOUT_MS => 200,
            CURLOPT_TIMEOUT_MS => 1000,
        ]
    ]);

    // GET
    // Request to: // http://host.com/base/hs/command/param?key=value
    
    $response = $service->get('command', ['param', 'key' => 'value']);

    if ($response->isOk()) {
        echo $response->code;
        echo $response->error;
        echo $response->result;
    }
    
    // POST
    // - the body will be sent in JSON format
    // - waiting for reply in XML format
    $response = $service->post('command', ['param' => 'value1', 'param2' => ['a', 'b']]);
    
    // JSON
    {
        "param": "value1",
        "param2": [
            "a",
            "b"
        ]    
    }
    
```