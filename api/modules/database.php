<?php

use MongoDB\Client;

// Connect to MongoDB
$client = new Client($_SERVER['GGF_DASHBOARD_MONGODB_URI']);

// Select database and collection
$db = $client->selectDatabase($_SERVER['GGF_DASHBOARD_MONGODB_NAME']);
$collection = $db->selectCollection('guilds');

function fetch_guild_QNA($guild_id)
{
    global $collection;

    // Fetch the document from MongoDB
    $result = $collection->findOne(['_id' => $guild_id]);

    // Check if document was found
    if ($result) {
        return $result['qna'];
    } else {
        return null;
    }
}

function update_guild_QNA($guild_id, $qna): bool
{
    global $collection;

    try {
        $result = $collection->updateOne(
            ['_id' => $guild_id],
            ['$set' => ['qna' => $qna]]
        );

        if ($result->getMatchedCount() > 0) {
            return true;
        } else {
            error_log('Failed to update QNA in MongoDB, no document found for guild ID: ' . $guild_id);
            return false;
        }
    } catch (Exception $e) {
        error_log('An error occurred while trying to save the new QNA: ' . $e->getMessage());
        return false;
    }
}
