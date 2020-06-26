## Our core project

> NOTE: the backend portion of this project is gradually being replaced by the API projects. Eventually, we will migrate the SPA portion of this project into a `Backoffice` project, and the replicated sites portion of this project will be migrated to a `Storefront` project.


### Setup
 - Clone repo (needs expanding)
 - Setup vagrant (needs expanding)
 - Run the following commands in the root folder of the project:

```
npm i
npm --prefix ./spadev i ./spadev
cp .env.testing .env
composer config -g github-oauth.github.com $GITHUB_ACCESS_TOKEN
composer install
```

### Build
Run the following command:
```
npm run build
```

### Develop
> NOTE: Not yet implemented
To automatically rebuild the backoffice and storefront whenever file changes are detected, run the following command:
```
npm start
```