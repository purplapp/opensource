**This has now been merged into the [master Purplapp repo on GitHub](https://github.com/purplapp/purplapp). Check it out over there.**

---

opensource
==========

A breakdown of Purplapp's open source contributions and statistics. 

## Setup

### Getting the code

```bash
# clone the repo
git clone git@github.com:purplapp/opensource.git && cd opensource

# Install composer. If you've got it already, skip this step
curl -sS https://getcomposer.org/installer | php

# install dependencies
php composer.phar install
```

### Configuration

You'll need to get or create a GitHub personal access token. Information on scopes required is coming soon, but the defaults are probably good for now. You can get this information from [your applications tab](https://github.com/settings/tokens/new).

App configuration is handled via a `.env` file in the root. Copy the `.env.example` file and fill in your details there.

### Server

This will run on most servers. For testing:

```bash
# enter web directory
cd web

# start php built in server
php -S localhost:8888 router.php
```
