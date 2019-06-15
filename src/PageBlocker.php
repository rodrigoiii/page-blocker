<?php

namespace PageBlocker;

class PageBlocker
{
    private $config;

    public function __construct($config=null)
    {
        $this->config = [];
        $this->config['database'] = [];
        $this->config['pages'] = [];

        if (isset($config['database']))
        {
            $this->setDatabase($config['database']);
        }

        if (isset($config['table']))
        {
            $this->setTableName($config['table']);
        }

        if (isset($config['pages']))
        {
            foreach ($config['pages'] as $uri => $settings) {
                $this->queuePage($uri, $settings);
            }
        }
    }

    public function setDatabase($db)
    {
        if (isset($db['hostname']))
        {
            $this->config['database']['hostname'] = $db['hostname'];
            unset($db['hostname']);
        }

        if (isset($db['username']))
        {
            $this->config['database']['username'] = $db['username'];
            unset($db['username']);
        }

        if (isset($db['password']))
        {
            $this->config['database']['password'] = $db['password'];
            unset($db['password']);
        }

        if (isset($db['name']))
        {
            $this->config['database']['name'] = $db['name'];
            unset($db['name']);
        }

        if (!empty($db))
        {
            $keys = array_keys($db);
            array_map(function($key) {
                error_log("Page Blocker Error: {$key} is not valid key for setting database.");
            }, $keys);
        }
    }

    /**
     * Set table for page blocker
     *
     * @param string $table  Table name must not use space or dash, just underscore for separating words.
     */
    public function setTableName($table)
    {
        $this->config['table'] = $table;
    }

    /**
     * Register page to be block
     *
     * @param  string $uri      [description]
     * @param  array $settings
     * [
          integer 'block_time'  How long the page block
          integer 'number_access_length'  Number of allow to access
          string 'trigger_method'  "POST" or "GET"
     * ]
     * @return void
     */
    public function queuePage($uri, $settings)
    {
        $this->config['pages'][$uri] = $settings;
    }

    public function attempt()
    {
        $routes = array_keys($this->config['pages']);
        $uri = $_SERVER['REQUEST_URI'];

        if (in_array($uri, $routes))
        {
            $db_config = $this->config['database'];

            $page_settings = $this->config['pages'][$uri];

            $mysqli = new \mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['name']);

            // throw error if not connected in database.
            if ($mysqli->connect_errno !== 0) exit($mysqli->connect_error);

            $pageBlocker = new PageBlockerDAO($mysqli, $this->config['table'], $uri, get_user_ip());

            try {
                // create table for page blocker if not yet existing
                $pageBlocker->createTableIfNotExist($this->config['table']);

                if (!$pageBlocker->isAuthorized($page_settings['block_time'], $page_settings['number_access_length']))
                {
                    return false;
                }

                // insert log
                $method = strtoupper($_SERVER['REQUEST_METHOD']);
                $trigger_method = $page_settings['trigger_method'];
                switch (true) {
                    case is_array($trigger_method):
                        if (in_array($method, array_map("strtoupper", $trigger_method)))
                        {
                            $pageBlocker->add();
                        }
                        break;

                    case is_string($trigger_method):
                        if ($method === strtoupper($trigger_method))
                        {
                            $pageBlocker->add();
                        }
                        break;
                }

                $mysqli->close();
            } catch (\Exception $e) {
                exit($e->getMessage());
            }
        }
        else
        {
            error_log("Page Blocker Error: Uri {$uri} is not registered.");
        }

        return true;
    }
}
