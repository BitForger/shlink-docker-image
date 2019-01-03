<?php
    /**
     * Created by PhpStorm.
     * User: yungdev (Noah Kovacs)
     * Date: 2019-01-03
     * Time: 15:37
     */

    $dbHost = getenv("DB_HOST");
    $dbName = getenv("DB_NAME") ?: "shlink";
    $dbUser = getenv("DB_USER");
    $dbPassword = getenv("DB_PASSWORD");
    $dbPort = getenv("DB_PORT") ?: 3306;

    if (!$dbHost || !$dbName || !$dbUser || !$dbPassword || !$dbPort) {
        echo "false";
        exit;
    }

    $mysqlDSN = "mysql:host=".$dbHost.";port=".$dbPort.";dbname=".$dbName;
    $pdo = new PDO($mysqlDSN, $dbUser, $dbPassword);

    $result = $pdo->query("SELECT `version` from shlink.migrations LIMIT 1")->execute();
    if ($result) {
        echo "true";
        exit;
    } else {
        echo "false";
        exit;
    }
