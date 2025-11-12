# BrickMMO Uptime Monitor

A simple uptime monitoring system for BrickMMO services built with PHP and MySQL.

## Features

- Monitor multiple websites/services
- Track uptime percentages and response times
- Real-time dashboard with charts
- Admin panel for asset management
- Automatic monitoring via cron jobs

## Installation

1. Clone this repository to your web server
2. Edit `config.php` with your database settings
3. Run `php setup.php` to create the database
4. Set up cron job: `*/5 * * * * cd /path/to/uptime-v1 && php cron.php`
5. Access the application in your browser

## Default Admin Login

- Username: `admin`
- Password: `password`

⚠️ PLease Change the password accordingly

## Tech Stack

- PHP 7.4+
- MySQL 5.7+
- W3.CSS
- Chart.js

## License

Part of the BrickMMO ecosystem.
