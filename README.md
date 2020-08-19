# A Wordpress site by Spritely

## Installation

#### 1. Clone this repo

```bash
git clone <repo-url>
```

#### 2. Copy `.env.example` to `.env` (and add your environment's settings)

```bash
cp .env.example .env
```

#### 3. Import the DB


Once imported: scrub any sensitive data (eg. customer info, credit card tokens etc).

#### 4. Install dependencies (composer, npm)

```bash
composer install --ignore-platform-reqs
( cd web/app/themes/pvtl-child ; yarn )
```

---

## Local development

### Installation


Working in the [Pivotal Docker Dev environment](https://github.com/pvtl/docker-dev), you'll need to do the following:

- You'll need `DB_HOST=mysql` in your `.env`
- You'll need to create a symlink of `/public` to `/web` (`ln -s web public`)
- Your Hostname will need to be {website}__.pub.localhost__ (note the `.pub`)

### Theme


For more information on working with this site's theme, please see the README.md from this site's theme directory.

---

## Wordpress Plugins


Wordpress Plugins are managed through composer.

### Installing


- Visit [WP Packagist](https://wpackagist.org/)
- Find the plugin (eg. akismet)
- Copy the packagist name (eg. `wpackagist-plugin/plugin-name`) and run `composer require wpackagist-plugin/plugin-name`

### Updating


Simply update the plugin's version number (to the desired version) in `composer.json` and run `composer update`.

### Removing


Simply run `composer remove wpackagist-plugin/plugin-name`
