<?php

namespace PageBlocker;

class PageBlockerDAO
{
    /**
     * @var array
     */
    private $db;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $ip;

    /**
     * Set PageBlockerDAO properties
     *
     * @param array $db_config
     * @param string $table
     * @param integer $uri
     * @param integer $ip
     */
    public function __construct($mysqli, $table, $uri, $ip)
    {
        $this->db = $mysqli;
        $this->table = $table;
        $this->uri = $uri;
        $this->ip = $ip;
    }

    /**
     * Get the table to be used to insert all page request logs.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Create table to be used to insert all page request logs.
     *
     * @throws Throw \Exception once mysql query is invalid.
     * @return void
     */
    public function createTableIfNotExist()
    {
        $table = $this->getTable();
        $create_table_query = "CREATE TABLE IF NOT EXISTS `{$table}` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `uri` tinytext NOT NULL,
                      `ip_address` varchar(20) NOT NULL,
                      `created_at` datetime NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
                    ";

        $result = $this->db->query($create_table_query);

        // invalid mysql query
        if ($result === false) throw new \Exception("Invalid mysql query {$create_table_query}", 1);

        return true;
    }

    /**
     * Get all page request logs.
     *
     * @throws Throw \Exception once mysql query is invalid.
     * @return array
     */
    public function getAll()
    {
        $table = $this->getTable();
        $select_query = "SELECT * FROM {$table}";

        $result = $this->db->query($select_query);

        // invalid mysql query
        if ($result === false) throw new \Exception("Invalid mysql query {$select_query}", 1);

        $rows = [];
        if ($result->num_rows > 0)
        {
            while ($row = $result->fetch_object()) {
                $rows[] = $row;
            }

            $result->close();
        }

        return $rows;
    }

    /**
     * Get page request logs filter by uri and ip.
     *
     * @param string $chain_query
     * @throws Throw \Exception once mysql query is invalid.
     * @return array
     */
    public function get($chain_query="")
    {
        $table = $this->getTable();

        $uri = $this->uri;
        $ip = $this->ip;

        $select_query = "SELECT * FROM {$table} WHERE `uri`='{$uri}' AND `ip_address`='{$ip}' {$chain_query}";

        $result = $this->db->query($select_query);

        // invalid mysql query
        if ($result === false) throw new \Exception("Invalid mysql query: {$select_query}'", 1);

        $rows = [];
        if ($result->num_rows > 0)
        {
            while ($row = $result->fetch_object()) {
                $rows[] = $row;
            }

            $result->close();
        }

        return $rows;
    }

    /**
     * Insert new page request.
     *
     * @throws Throw \Exception once mysql query is invalid.
     * @return void
     */
    public function add()
    {
        $table = $this->getTable();

        $uri = $this->uri;
        $ip = $this->ip;

        $insert_query = "INSERT INTO `{$table}`(`uri`, `ip_address`, `created_at`)
                        VALUES('{$uri}', '{$ip}', '".date('Y-m-d H:i:s')."')";

        $result = $this->db->query($insert_query);

        // invalid mysql query
        if ($result === false) throw new \Exception("Invalid mysql query {$insert_query}", 1);

        return true;
    }

    /**
     * Reset or remove all page request filter by uri and ip.
     *
     * @throws Throw \Exception once mysql query is invalid.
     * @return void
     */
    public function reset()
    {
        $table = $this->getTable();

        $uri = $this->uri;
        $ip = $this->ip;

        $delete_query = "DELETE FROM {$table} WHERE `uri`='{$uri}' AND `ip_address`='{$ip}'";
        $result = $this->db->query($delete_query);

        // invalid mysql query
        if ($result === false) throw new \Exception("Invalid mysql query {$delete_query}", 1);

        return true;
    }

    /**
     * Check if the page is accessible or not.
     *
     * @throws Throw \Exception once mysql query is invalid.
     * @return boolean
     */
    public function isAuthorized($block_time, $attempt_length)
    {
        $is_authorized = true;

        $rows = $this->get("ORDER BY created_at DESC");

        if (!empty($rows))
        {
            $last_row = $rows[0];

            $created_at = strtotime($last_row->created_at);
            $created_at = strtotime("+".$block_time." seconds", $created_at);
            $created_at = date("Y-m-d H:i:s", $created_at);
            $now = date("Y-m-d H:i:s");

            if ($now >= $created_at)
            {
                $this->reset();
            }
            else
            {
                $is_authorized = count($rows) < $attempt_length;
            }
        }

        return $is_authorized;
    }
}
