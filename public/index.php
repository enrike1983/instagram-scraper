<?php
require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

if(file_exists(__DIR__.'/.env')) {
    //dotenv
    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();
}

////logger
$log = new Logger('name');
$log->pushHandler(new StreamHandler(__DIR__ .'/logs/calls_results.log', Logger::INFO));
//
////db
$username = getenv('USERNAME');
$password = getenv('PASSWORD');
$dsn = getenv('DSN');

$dbh = new PDO($dsn, $username, $password);

//instagram
//echo "Id: {$media->getId()}\n";
//echo "Shotrcode: {$media->getShortCode()}\n";
//echo "Created at: {$media->getCreatedTime()}\n";
//echo "Caption: {$media->getCaption()}\n";
//echo "Number of comments: {$media->getCommentsCount()}";
//echo "Number of likes: {$media->getLikesCount()}";
//echo "Get link: {$media->getLink()}";
//echo "High resolution image: {$media->getImageHighResolutionUrl()}";
//echo "Media type (video or image): {$media->getType()}";
//$account = $media->getOwner();
//echo "Account info:\n";
//echo "Id: {$account->getId()}\n";
//echo "Username: {$account->getUsername()}\n";
//echo "Full name: {$account->getFullName()}\n";
//echo "Profile pic url: {$account->getProfilePicUrl()}\n";

try {
    $feeds = $dbh->query('SELECT instagram_id from feeds', PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($feeds);

} catch (\Exception $e) {
    $log->error($e->getMessage());
}
