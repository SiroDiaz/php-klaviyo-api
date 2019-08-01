# php-klaviyo-api

[![Build Status](https://travis-ci.org/SiroDiaz/php-klaviyo-api.svg?branch=dev)](https://travis-ci.org/SiroDiaz/php-klaviyo-api)
[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3XKLA6VTYVSKW&source=url)

Klaviyo API wrapper for PHP. It allows to consume the Klaviyo v1 API using a clear and simple
PHP class format for make the usage user-friendly. Stuff like delete a list is as logic as doing:
`$klaviyo->list->delete('asdD2e2')`. Easy, isn't it?

## Installation
Installing php-klaviyo-api is simple. You just need Composer installed and added to the path. For install the
current last version run:

`composer require siro/php-klaviyo-api:"^1.2.0"`

## Usage

### API organization

The php-klaviyo-api is organized as the official Klaviyo API, really.
If you want to access to event API you must do as follow:

```php
// klaviyo Event API
$klaviyo->event->track($event, $customerProperties, $properties);
$klaviyo->event->trackAsync($event, $customerProperties, $properties);

// email template API
$klaviyo->template->getAll();
$klaviyo->template->create('newuser', $htmlString);

// lists API
$klaviyo->list->getLists();
$klaviyo->list->create('premium');
```

A real example would be as i show here:

```php
<?php

use Siro\Klaviyo\KlaviyoAPI;

$klaviyo = new KlaviyoAPI();
$klaviyo->event->asyncTrack(
    'register', [
        'email' => 'federico@gmail.com'
    ], []
);

```

As you can see it is really easy. The code is good organized, with the same
documentation that the official one. You just need to read a bit and you will see that it is simple and intuitive.

### Klaviyo Event API

This API is used to track events to Klaviyo. This is the main feature
and you maybe would use it. By that reason i implemented this API wrapper
in this way.
For load it



## Contributing
This project uses **PSR-4** coding standard. If you want to make a contribution it must be important run `make sniff` for checking
your code before commit the changes.
At this moment it is prioritary making tests for the API so Pull requests for tests and fixes are welcome.

## Credits
All credits, at this moment, are for Siro Díaz Palazón <sirodiaz93@gmail.com>.

## Contributors
At this moment main contributors are:
 - Siro Díaz Palazón [SiroDiaz](https://github.com/SiroDiaz)
 - Lukasz [Blysq](https://github.com/Blysq)

## License
This project is licensed under MIT.
