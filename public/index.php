<?php
require __DIR__ . '/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

if(file_exists(__DIR__.'/../.env')) {
    //dotenv
    $dotenv = new Dotenv\Dotenv(__DIR__.'/../');
    $dotenv->load();
}

////logger
$log = new Logger('name');
$log->pushHandler(new StreamHandler(__DIR__ .'/../logs/calls_results.log', Logger::INFO));
//
////db
$username = getenv('USERNAME');
$password = getenv('PASSWORD');
$dsn = getenv('DSN');

$dbh = new PDO($dsn, $username, $password);

try {
    $stmt = $dbh->query('SELECT * from feeds');
    $feeds = $stmt->fetchAll();

    header('Content-Type: application/json');

    echo json_encode($feeds);

} catch (\Exception $e) {
    $log->error($e->getMessage());
}
