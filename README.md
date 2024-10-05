![Pest Laravel Expectations](https://banners.beyondco.de/OX-Telegram.png?theme=dark&packageManager=composer+require&packageName=OX-Technology%2Ftelegram&pattern=architect&style=style_1&description=Facade+for+working+with+Telegram&md=1&showWatermark=1&fontSize=100px&images=annotation)

---


**OXTechPHP** is a Laravel package for fluently interacting with Telegram Bots made by OX-Technology

```php
Telegraph::message('hello world')
    ->keyboard(Keyboard::make()->buttons([
            Button::make('Delete')->action('delete')->param('id', '42'),
            Button::make('open')->url('https://test.it'),
    ]))->send();
```

## Installation

You can install the package via composer:

```bash
composer require OXTechPHP/telegram
```

Publish and launch required migrations:

```bash
php artisan vendor:publish --tag="telegram-migrations"
```

```bash
php artisan migrate
```

Optionally, you can publish the config and translation file with:
```bash
php artisan vendor:publish --tag="telegram-config"
```
```bash
php artisan vendor:publish --tag="telegram-translations"
```

## Usage & Documentation

After a new bot is created and added to a chat/group/channel (as described [in our documentation](https://github.io/telegram)),
the `Telegram` facade can be used to easily send messages and interact with it:

```php
Telegram::message('this is great')->send();
```

An extensive documentation is available at

https://github.io/OXTechPHP/telegram