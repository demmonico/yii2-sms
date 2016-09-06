#Yii2 component - Sms Sender
##Description
Yii2 component which allow simple send sms using multiply providers.
Switching between providers supports using yii config file (better)



##Configure
###Common params

```php
return [
    //...
    'components' => [
        //...
        'sms' => [
            'class' => 'demmonico\sms\Sender',
            'senderNumber' => 'name' or 'number',
            'provider' => [
                'class' => 'demmonico\sms\Nexmo',
                'apiKey' => '***',
                'apiSecret' => '***',
            ],
        ],
    ],
];
```

or DI

```php
$component = \Yii::createObject('demmonico\sms\Nexmo', [
    [
        'class' => 'demmonico\sms\Sender',
        'senderNumber' => 'name' or 'number',
        'provider' => [
            'class' => 'demmonico\sms\Nexmo',
            'apiKey' => '***',
            'apiSecret' => '***',
        ],
    ],
]);
```

or using config component's bootstrap method (see [https://github.com/demmonico/yii2-config](https://github.com/demmonico/yii2-config))

in config file
```php
return [
    //...
    'components' => [
        //...
        'sms' => [
            'class' => 'demmonico\sms\Sender',
            'senderNumber' => 'name' or 'number',
            'provider' => [
                'class' => 'demmonico\sms\Nexmo',
                'apiKey' => [
                    'component' => 'config',
                    'sms.Nexmo.apiKey',
                ],
                'apiSecret' => [
                    'component' => 'config',
                    'sms.Nexmo.apiSecret',
                ],
            ],
        ],
    ],
];
```

and in local params file

```php
return [
    //...
    'sms.Nexmo.apiKey' => '******',
    'sms.Nexmo.apiSecret' => '******',
];
```


Now available Nexmo provider only. But you can add any external class with your custom provider. 
New providers can be added by creating class, which will implements `demmonico\sms\SmsProviderInterface` and extends (optional) `demmonico\sms\BaseProvider`.

###Debug params
For debug you can use redirect option and dummy option. They can use separately or together.
If redirect option `redirectNumber` is set then all messages will be send to this number.
If dummy option `dummyNumbers` is set and field $to matches to any of dummyNumbers elements then process send will be skip and all sms fields will be logged
```php
return [
    //...
    'components' => [
        //...
        'sms' => [
            //...
            'debug' => [
                'redirectNumber' => 'number',
                'dummyNumbers' => [
                    'number',
                    //...
                ],
            ],
        ],
    ],
];
```



##Usage
###Send sms
```php
Yii::$app->sms->sendSms('Hello, world!', 'number');
```
or

```php
Yii::$app->sms->sendSms('Hello, world!', 'recipientNumber', 'senderNumber');
```

Numbers must be in [E.164](https://en.wikipedia.org/wiki/E.164) format.
Method returns number (integer) of sent sms.

###Get account balance
```php
Yii::$app->sms->getBalance();
```
Method returns balance value (float).

###Get account numbers
```php
Yii::$app->sms->getNumbers();
```
Method returns array of numbers which are associate with account.
