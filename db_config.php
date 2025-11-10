<?php
function getDBConnection() {
    
    if (getenv('PGHOST')) {
        
        $host = getenv('PGHOST');
        $port = getenv('PGPORT');
        $dbname = getenv('PGDATABASE');
        $user = getenv('PGUSER');
        $password = getenv('PGPASSWORD');
        
        if (!$host || !$port || !$dbname || !$user) {
            die("Database environment variables not set");
        }
        
        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    } else {
       
        $host = 'localhost';
        $port = '3306';
        $dbname = 'dubzadventours';
        $user = 'root';
        $password = '';
        
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage() . 
                "<br><br><strong>Make sure you:</strong><br>
                1. Started XAMPP (Apache + MySQL)<br>
                2. Created database 'dubzadventours' in phpMyAdmin<br>
                3. Imported the database tables");
        }
    }
}
?>
