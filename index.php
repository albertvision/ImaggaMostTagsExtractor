<?php

require_once "lib/Imagga.php";
require_once "config.php";

$client = new \Imagga\Imagga\Client($config['api_key'], $config['api_secret']);
$images = json_decode(file_get_contents('images.json'), true);

$images = array_slice($images, 0, 10); // Extracts only the first 20 items.

function getTopTags($images) {
    global $client;

    $tags = [];
    $max = 0;

    foreach($images as $image) {
        $response = $client->tagging($image['small_url']);

        if ($response->getErrors()) {
            foreach ($response->getErrors() as $err) {
                echo 'Error: '. $err->getMessage() . ', status code: ' . $err->getStatusCode();

                exit;
            }
        }

        foreach($response->getResults() as $result) {
            foreach($result->getTags() as $tag) {
                $score = getScore($tag->getConfidence());

                if($score < 1) {
                    break;
                }

                if(!isset($tags[$tag->getLabel()])) {
                    $tags[$tag->getLabel()] = 0;
                }
                $tags[$tag->getLabel()] += getScore($tag->getConfidence());

                if($tags[$tag->getLabel()] > $max) {
                    $max = $tags[$tag->getLabel()];
                }
            }
        }
    }

    return [$tags, $max];
}

function getScore($confidence) {
    return (double)($confidence);
}

$fontSize = 12;
$data = getTopTags($images);

foreach($data[0] as $label => $tag) {
    ?>
    <span style="font-size: <?= ($fontSize+$tag) ?>; padding: 10px; background-color: rgb(<?= 270-(int)(255*($tag/$data[1])); ?>,0,0); margin-right: 10px; color: #fff; display: inline-block; border-radius: 5px;"><?= $label; ?></span>
    <?php
}
?>
