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
    $instagram = new \InstagramScraper\Instagram();
    $medias = $instagram->getMediasByTag('metalgirl', 100);

    //if already cached, ignore, otherwise process!
    $cached_feeds_ids = $dbh->query('SELECT instagram_id from feeds', PDO::FETCH_ASSOC);
    $cached_ids = [];
    foreach($cached_feeds_ids as $feed) {
        $cached_ids[] = $feed['instagram_id'];
    }

    foreach ($medias as $media) {

        $type = pathinfo($media->getImageHighResolutionUrl(), PATHINFO_EXTENSION);
        $data = file_get_contents($media->getImageHighResolutionUrl());
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        if(!in_array($media->getId(), $cached_ids)) {
            $log->info(date('h:i:sa').' - new feed found: '.$media->getId());
            //cache
            $account = $media->getOwner();
            $data = [
                'instagram_id' => $media->getId(),
                'url' => $media->getLink(),
                'created_at' => $media->getCreatedTime(),
                'image' => $base64,
                'username' => $account->getUsername(),
                'caption' => $media->getCaption()
            ];

            $sql = "INSERT INTO feeds(instagram_id, url, created_at, image, username, caption) VALUES(:instagram_id, :url, :created_at, :image, :username, :caption)";
            $dbh->prepare($sql)->execute($data);
        }
    }
    echo 'import complete!';
} catch (\Exception $e) {
    $log->error($e->getMessage());
}
