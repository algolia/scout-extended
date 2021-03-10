CONTRIBUTING
============


Contributions are welcome, and are accepted via pull requests. Please review these guidelines before submitting any pull requests.


## Guidelines

* Please follow the [PSR-2 Coding Style Guide](http://www.php-fig.org/psr/psr-2/), enforced by [StyleCI](https://styleci.io/).
* Ensure that the current tests pass, and if you've added something new, add the tests where relevant.
* Send a coherent commit history, making sure each individual commit in your pull request is meaningful.
* You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
* If you are changing the behavior, or the public api, you may need to update the docs.
* Please remember that we follow [SemVer](http://semver.org/).

We have [StyleCI](https://styleci.io/) setup to automatically fix any code style issues.


## Tests
The test suite calls Algolia servers to test the storage, updating, and search of records. 
If you want to run your tests locally, the test suite needs the `ALGOLIA_APP_ID` and `ALGOLIA_SECRET` variables available in your environment to make these calls.

You can set these variables by, for example, passing them when running the `composer test` command:

```sh
ALGOLIA_APP_ID="yourAppID" ALGOLIA_SECRET="yourAdminAPIKey" composer test
```

Please note that the tests add records and perform operations on your application. 
Therefore, it's best to create a separate application with a [free Algolia plan](https://www.algolia.com/pricing/), to ensure the test suite doesn't alter your production data if there are naming collisions, and you don't go over your own plan's limits.
