<?php
    /**
     * User: Noah Kovacs
     * Date: 2019-01-03
     * Time: 15:37
     */

    $dbDriver = getenv("DB_DRIVER") ?: "sqlite";
    $dbHost = getenv("DB_HOST") ?: "127.0.0.1";
    $dbName = getenv("DB_NAME") ?: "shlink";
    $dbUser = getenv("DB_USER") ?: "root";
    $dbPassword = getenv("DB_PASSWORD") ?: "password";
    $dbPort = getenv("DB_PORT") ?: 3306;

    if (!$dbHost || !$dbName || !$dbUser || !$dbPassword || !$dbPort || !$dbDriver) {
        echo "false";
        exit;
    }

    if ($dbDriver !== "sqlite") {
        if ($dbDriver === "mysql") {
            // Using string concatenation because interpolation acts weird sometimes
            $mysqlDSN = "mysql:host=" . $dbHost . ";port=" . $dbPort . ";dbname=" . $dbName;
            $pdo = new PDO($mysqlDSN, $dbUser, $dbPassword);

            $pdo->beginTransaction();
            $pdo->exec("LOCK TABLES migrations, api_keys, short_urls, short_urls_in_tags, tags, visit_locations, visits WRITE NOWAIT");
            $stmt = $pdo->prepare("SELECT `version` from shlink.migrations LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch();
            $pdo->exec("UNLOCK TABLES");
            $pdo->commit();
            if ($result) {
                echo "true";
                exit;
            } else {
                echo "false";
                exit;
            }
        } else if ($dbDriver === 'postgres') {
            $connString = "host=" . $dbHost . " dbname=" . $dbName . " user=" . $dbUser;
            if ($dbPassword) {
                $connString .= " password=$dbPassword";
            }
            if ($dbPort) {
                $connString .= " port=$dbPort";
            }
            $conn = pg_connect($connString);
            $q = "SELECT `version` from migrations LIMIT 1";
            $res = pg_query($q) or die("false");
            $row = pg_fetch_array($res, null, PGSQL_ASSOC);
            if ( count($row) > 0 ) {
                echo "true";
                exit;
            } else {
                echo "false";
                exit;
            }
        }
    }
