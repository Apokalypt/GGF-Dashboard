<?php

require __DIR__ . "/../modules/discord.php";

global $guild_id, $moderator_role_id;

if (!user_is_authorized($guild_id, $moderator_role_id)) {
    session_destroy();

    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'You are not authorized to perform this action.']);
    exit;
}

require __DIR__ . "/../modules/database.php";

header('Content-Type: application/json');

// Get the raw POST data
$data = file_get_contents('php://input');

// Decode the JSON data
$qna = json_decode($data, true);

// Update the document in MongoDB
$success = update_guild_QNA($guild_id, $qna);

// Check if update was successful
if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'QNA updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update QNA']);
}
