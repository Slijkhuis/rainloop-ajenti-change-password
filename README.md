# rainloop-ajenti-change-password
RainLoop plugin that works with Ajenti, this lets users change their passwords through the RainLooop UI.

## Warning

*Warning: this is highly experimental. Not for production use.*

## Installation

Copy the contents of this repository to a folder in the plugins-directory of your RainLoop installation.

In order for it to work you need to edit this file:

```
/etc/sudoers
```

And add this line:

```
www-data ALL=(ALL:ALL) NOPASSWD:/srv/rainloop/data/_data_/_default_/plugins/change-password-ajenti/*
```

Where `www-data` is the user that runs the RainLoop PHP scripts, and `/srv/rainloop/data/_data_/_default_/plugins/change-password-ajenti/` is the path where you install this plugin.

This is required because the script that updates the passwords needs root access.

## Credits

This was built upon the ideas in https://github.com/janxb/AjentiMailAdmin
