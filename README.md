# Skeleton Page Blocker

This library helps you to block the page and give it time how long it will available to access again.

## Installation And Usage

 - Install this library via composer using `composer require rodrigoiii/skeleton-page-blocker` command.
 - After that publish it in skeleton using `php cli publish:module SkeletonPageBlocker` command.
 - Put SkeletonPageBlocker in `config/app.php` at modules key.
 ```php
 <?php

 return [
     'name' => app_env('APP_NAME', "Skeleton"),
     ...
     'modules' => ["SkeletonPageBlocker"]
 ];
 ```
 - Add example route
 ```php
 <?php

 $app->group('/sklt-page-blocker', function() {
     $this->get('', function() {
         return "execute get method";
     });
     $this->post('', function() {
         return "execute post method";
     });
 })->add("SkeletonPageBlockerApp\\PageBlockerMiddleware");
 ```

## Config schema

| $config keys | Description |
|---|---|
| table | <string\> The table to be used to insert all page request logs. The default table is 'sklt_page_blocker' |
| pages | <array\>  Two dimensional array. All register route and its settings. |
| pages key | <string\> Route path |
| pages.block_time | <integer\> It tells how long to block the page. The default is (60 * 30) or 30 minutes. |
| pages.template | <string\> File name to be render when the request is unauthorized. |
| pages.trigger_method | <string\> Method to be trigger if need to add log or reset the logs. |
| pages.attempt_length | <integer\> Alloted number of attempt to access specific page. The default is 5. |

## Testing

```bash
$ ./vendor/bin/phpunit tests
```

## License
This library is released under the MIT Licence. See the bundled LICENSE file for details.
