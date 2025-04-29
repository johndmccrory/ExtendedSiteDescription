# Extended Site Description
## A module for Omeka S

This module creates 2 additional inputs in the Settings page for every Site:

- Featured: a true/false value controlled by a checkbox
- Categories: a multi-select of categories the site belongs to. Categories to choose from
  are configured in the module's Configure page.

In a public view, these values are accessibile using the siteSetting helper:

```php
$featured = $this->siteSetting('extended_site_description_featured');

$categories = $this->siteSetting('extended_site_description_categories');

```

The values are also exposed, using the same keys, in the site API output.

