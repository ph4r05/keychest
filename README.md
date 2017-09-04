# KeyChest.net

Certificate expiry monitor and server status checker. We operate it as a free service 
at [https://keychest.net](https://keychest.net). It provides:

1. spot checks - for quick feedback when you configure a HTTPS or TLS server.
2. dashboard - with reports about incidents, expiry dates, and 12 months planner.
3. massive enrolment options - single servers, bulk import of domain names, and "Active Domains" with on-going sub-domain discovery.
4. weekly status emails. 

## Important

.env file is important - needed to be added as not versioned

1. never edit /public files
2. just sync /resource files, should be OK for changes in the "look"
3. then you need to rebuild CSS/JS - "npm run dev" in the project root - more info below

## Installation

For installation please refer to [INSTALL.md]

[INSTALL.md]: https://github.com/EnigmaBridge/keychest/blob/master/INSTALL.md

### Resource compilation

Do not ever edit CSS/JS in `/public`, it is automatically generated
from resources. 

To recompile all resources call

```bash
# on dev
nice -n 19 npm run dev

# on production (minification, versioning)
nice -n 19 npm run prod

# dev with watching file changes
nice -n 19 npm run watch-poll
```

On Vue component modification re-upload compiled resources in `public/`

Do not edit CSS / JS files in `public/` directly, its generated. Changes will be lost.


### DB migrations

```bash
php artisan migrate
```

Hack `migrations` table if needed.

```bash
php artisan migrate:status
```


## Troubleshooting

### NPM rebuild fail

```
SyntaxError: Unexpected end of JSON input
    at Object.parse (native)
    at Manifest.read (/var/www/keychest-dev/node_modules/laravel-mix/src/Manifest.js:149:21)
```

 - The original message is not very helpful in diagnosing the true error. 
 - It helps to add `console.log()` to the scripts causing exceptions, in this case here `node_modules/laravel-mix/src/Manifest.js:149`
 - The culprit was the `public/mix-manifest.json` was empty somehow, so it threw JSON parsing exception. To fix remove / reupload the file.
 
Permission fix: 
 
```bash
sudo chown -R $(whoami) $(npm config get prefix)/{lib/node_modules,bin,share}
sudo chown -R $(whoami) $(npm config get prefix)
```

### NPM watching

If problem with watch option on unix system there may be too little watches configured.

```
echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p
```

### NPM infinite watching

It may happen `npm run watch` will rebuild the project infinitely many times.
The problem is described and solved [in this github issue](https://github.com/JeffreyWay/laravel-mix/issues/228#issuecomment-284076792)

Basically the problem is caused by webpack/mix copying all files found
in CSS to the `public/images` folder. If the file is already present there it causes a conflict,
file time changes and this triggers a new compilation.

Solution is to place images found in CSSs to `assets/images` folder.

To detect which file is causing a problem is to modify 
`node_modules/watchpack/lib/DirectoryWatcher.js` and change the watcher 
callback `DirectoryWatcher.prototype.onChange` so it logs
the file and the change - simply add some logging:
    
```javascript
console.log('..onChange ' + filePath);
console.log(stat);
```

### NPM build problem

In case of a weird error remove all npm packages and reinstall:

```
/bin/rm -rf node_modules/
/bin/rm package-lock.json
/bin/rm yarn.lock
/bin/rm public/mix-manifest.json 
npm install
```

### IPv6-only hosts

Keychest scanner supports IPv6 only hosts, try scanning `www.v6.facebook.com`

