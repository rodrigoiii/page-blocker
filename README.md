# Skeleton Page Blocker

This library helps you to block the page and give it time how long it will available to access again.

## Installation And Usage

 - Install this library via composer using `composer require rodrigoiii/page-blocker` command.

 - Add this example snippet
 ```php
 <?php

 $pageBlocker = new PageBlocker([
     'database' => [
         'hostname' => "localhost",
         'username' => "root",
         'password' => "secret",
         'name' => "db"
     ],
     'table' => "page_blocker_logs",
     'pages' => [
         "/login" => [
             'block_time' => 20, // 20 seconds page lock
             'number_access_length' => 5,
             'trigger_method' => "POST"
         ]
     ]
 ]);
 $accessible = $pageBlocker->attempt();

 if (!$accessible)
 {
     // block the page here...
 }
 ```

## Testing

```bash
$ ./vendor/bin/phpunit tests
```

## License
This library is released under the MIT Licence. See the bundled LICENSE file for details.
