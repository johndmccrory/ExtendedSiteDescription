# Extended Site Description
## A module for Omeka S

This module creates two additional inputs in the Settings page for every Site:

- Featured: a true/false value controlled by a checkbox
- Categories: a multi-select of categories to which the site belongs. Categories to choose from
  are configured in the module's Configure page.

In a public view, these values are accessibile using the siteSetting helper:

```php
$featured = $this->siteSetting('extended_site_description_featured');

$categories = $this->siteSetting('extended_site_description_categories');

```

The values are also exposed, using the same keys, in the site API output.

Update: v0.4
Revised to incorporate a rule only permitting letters (A-Z, a-z) when adding categories, optionally keeping spaces if desired.  
