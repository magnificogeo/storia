<?php

// Connect to Mongo database and instantiate Mongo object
$m = new MongoClient();

// Database object
$db = $m->juubs;

// Collection objects
$user_collection = $db->user;
$stories_collection = $db->stories;
$usermetadata_collection = $db->usermetadata;
$likes_collection = $db->likes;
$comments_collection = $db->comments;

$stories_collection->ensureIndex(array('posted_time' => -1));



