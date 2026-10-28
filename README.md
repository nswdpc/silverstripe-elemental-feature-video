# Video elements for Silverstripe

This module provides two content elements for us in an elemental area

+ Featured video - a single video that can store  a video ID and provider
+ Video gallery - a gallery of videos

## Features

+ Vimeo, YouTube & YouTube Privacy Enhanced Mode support
+ Sensible defaults for embedded videos
+ Your developer can extend the abstract provider to enable other video sources
+ Basic templates, override these in your own project
+ Transcript, description, link fields
+ Image thumbnails for video placeholders
+ Your developer can override the default templates (required)

## Link migration

This version includes [staged migration of links](https://github.com/nswdpc/silverstripe-linkfield/blob/feat-link-migrator-staged/docs/en/migration.md) to the core Silverstripe Link model.

1. *Important*: Prefix all calls to `FeatureLink`, `LinkTarget` with `Derived` - this will return the link based on the migration state.
2. Once the above change is available on your site, in the "Link migration" admin section any existing links will show with a  "Migrate" button
3. Hit the "Migrate" button
4. Records will migrate to the core Silverstripe Link model and the element interface will display the core Silverstripe link field once the record is republished.
5. Template usage on site frontend will pick up the new, core, link.

The next major release of this module will remove dependency of `nswdpc/silverstripe-linkfield` and legacy linkfield implementation.

## Installation

```
composer require nswdpc/silverstripe-elemental-feature-video
```

## Documentation

+ [Templates and styling hints](./docs/en/001_index.md)

## License

[BSD-3-Clause](./LICENSE.md)

## Maintainers

+ [dpcdigital@NSWDPC:~$](https://dpc.nsw.gov.au)

## Bugtracker

We welcome bug reports, pull requests and feature requests on the Github Issue tracker for this project.

Please review the [code of conduct](./code-of-conduct.md) prior to opening a new issue.

## Security

If you have found a security issue with this module, please email digital[@]dpc.nsw.gov.au in the first instance, detailing your findings.

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.

Please review the [code of conduct](./code-of-conduct.md) prior to completing a pull request.
