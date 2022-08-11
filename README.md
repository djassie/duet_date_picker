This module offers a Duet Date Picker field widget plugin.

DEPENDENCIES
------------

**Important!** The Duet Date Picker plugin requires Duet Date Picker 1.4 or higher.

HOW TO INSTALL DEPENDENCIES VIA COMPOSER:

1. Add asset-packagist to your `composer.json`.

```
"repositories": [
    {
        "asset-packagist": {
            "type": "composer",
            "url": "https://asset-packagist.org"
        }
    }
],
```

2. Execute `composer require npm-asset/duetds--date-picker`


CORE PATCHES
------------

Since the Duet Date Picker widgers extend from core's date and datetime widgets, they are subject to the same limitations (and bugs) as those base widgets. In particular, for datetime ranges, the core/base widget does not by default check that the selected start date precedes the selected end date. There is an open issue with a working patch, however:

https://www.drupal.org/project/drupal/issues/2847041

