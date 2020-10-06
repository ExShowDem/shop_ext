# Releasing
Before making a new official release run

```
gulp bump-version --version=version-number
```

This will replace all `__DEPLOY_VERSION__` uses with the new version number.
You can then commit the changes and make a new release like usual using `gulp release`.

Note that this should never be used during normal development, **_only_** when making an official release.
