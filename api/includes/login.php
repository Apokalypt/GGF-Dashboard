<?php

require __DIR__ . "/../modules/discord.php";

global $guild_id, $moderator_role_id;

# Initializing all the required values for the script to work
$login_is_valid = authenticate_user();
if (!$login_is_valid) {
    session_destroy();

    header('Location: /');
    exit;
}

if (!user_is_authorized($guild_id, $moderator_role_id)) {
    session_destroy();

    header('Location: /');
    exit;
}

header('Location: /dashboard');
exit;
