# Contribution Guide

### Bug fixes

**ALL** bug fixes should be made to appropriate branch (e.g. `1.1` for 1.1.* bug fixes). Bug fixes should never be sent to the `master` branch.

### Pull Requests

Every pull request should pass the unit tests. If you include new functionality, make sure you include a test. Pull requests will be evaluated and possibly added to the next stable release.

### Feature Requests

If you have an idea for a new feature you would like to see added to Laravel Excel, you may create an issue on GitHub with `[PROPOSAL]` in the title. The feature request will then be reviewed by @Maatwebsite.

### Coding Guidelines

Laravel, and therefore Maatwebsite's Laravel Excel follows the [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) and [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) coding standards. In addition to these standards, below is a list of other coding standards that should be followed:

- Namespace declarations should be on the same line as `<?php`.
- Class opening `{` should be on the same line as the class name.
- Function and control structure opening `{` should be on a separate line.
- Interface and Trait names are suffixed with `Interface` (`FooInterface`) and `Trait` (`FooTrait`) respectively.
