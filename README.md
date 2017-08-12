# DIEMS College App JSON API Service

The API Service for the College App of [DIEMS, Aurangabad](http://dietms.org).
It provides database handling and other back-end functionality for the Android App
and the upcoming Web App.

## Usage

For details on how to use this API and it's endpoints,
refer to the [wiki](https://github.com/deshmukhmayur/diems-app-backend/wiki).

## Installation

### Requirements

The following components should be pre-installed:

- A web server (Recommended: Apache)
- PHP (Recommended: 7.x)
- A Database Engine (Recommended: MariaDB / PostgreSQL)

### Configuration

Clone this Repository:
```bash
$ git clone https://github.com/deshmukhmayur/diems-app-backend.git
```

Install the dependencies:
```bash
$ php composer.phar install
```

After cloning the repository, you need to add a `settings.php` file in the `src/` folder
containing your database details. For eg.
```php
$settings = array(
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'database',
    'username' => 'username',
    'password' => 'password',
    'collation' => 'utf8_general_ci',
    'prefix' => '',
);
```

## Built with:

- [Slim3](https://www.slimframework.com/) - a micro-framework for PHP
- [Eloquent](https://laravel.com/docs/5.4/eloquent) - an ORM for PHP, developed by Laravel

## Contributing

This project is not, by any means, complete. This app was meant as a basic follow up tutorial for AngularJS and Django. I just wanted to see how it works out. One can find possibly many flaws in it. Or even missing features. If you like this project and would like to contribute to it, you can do so by following a few basic steps:

1. Fork this repository
2. Clone it locally to your system
3. Create a new branch for your patch or feature
4. Add your code/patch
5. Commit your work, and write good/unambiguous commit messages
6. Push it to your origin repository
7. Create a Pull Request for your patch/feature
8. Respond to any code review/comments feedback

## License

This project is licensed under the Apache-2.0.
See the [LICENSE.md](LICENSE) file for details.
