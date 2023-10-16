<?php
// /** Create DB Folder if not existing yet */
// if(!is_dir(__DIR__.'./db'))
//     mkdir(__DIR__.'./db');
// /** Define DB File Path */
// if(!defined('db_file')) define('db_file',__DIR__.'./db/forum_db.db');
// /** Define DB File Path */
// if(!defined('tZone')) define('tZone',"Asia/Manila");
// if(!defined('dZone')) define('dZone',ini_get('date.timezone'));

define('db_host', 'localhost'); // Change to your MariaDB server hostname
define('db_user', 'root');  // Change to your MariaDB username
define('db_pass', '');  // Change to your MariaDB password
define('db_name', 'blog_db'); // Change to the name of your MariaDB database

/** DB Connection Class */
class DBConnection extends mysqli
{
    protected $db;
    function __construct()
    {
        /** Opening Database */

        parent::__construct(db_host, db_user, db_pass, db_name);

        if ($this->connect_error) {
            die("Connection failed: " . $this->connect_error);
        }

        // Enable foreign key support (if your MariaDB version supports it)
        $this->query("SET foreign_key_checks = 1;");

        $this->query("SET FOREIGN_KEY_CHECKS = 1;");

        $this->query("CREATE TABLE IF NOT EXISTS `user_list` (
            `user_id` INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
            `fullname` VARCHAR(255) NOT NULL,
            `username` VARCHAR(255) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `type` TINYINT(1) NOT NULL DEFAULT 0,
            `status` TINYINT(1) NOT NULL DEFAULT 0,
            `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $this->query("CREATE TABLE IF NOT EXISTS `topic_list` (
            `topic_id` INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
            `user_id` INT NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `status` TINYINT(2) NOT NULL DEFAULT 0,
            `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `user_list` (`user_id`)
        )");

        $this->query("CREATE TABLE IF NOT EXISTS `comment_list` (
            `comment_id` INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
            `topic_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `comment` TEXT NOT NULL,
            `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`topic_id`) REFERENCES `topic_list` (`topic_id`)
        )");

        $this->query("INSERT IGNORE INTO `user_list` (`user_id`, `fullname`, `username`, `password`, `type`, `status`, `date_created`) 
        VALUES (1, 'Administrator', 'admin', '$2y$10\$Aj/jjNbcT1vNZrp.9ELpheF9rgjP9RInWb8RSuTGAKcoKJE26HCb6', 1, 1, CURRENT_TIMESTAMP)");
    }
    function __destruct()
    {
        $this->close();
    }
}

$conn = new DBConnection();
