<?php

require __DIR__ . "/../modules/discord.php";

global $client_id, $secret_id, $guild_id, $moderator_role_id, $redirect_url;

# Initializing all the required values for the script to work
$login_is_valid = authenticate_user($redirect_url, $client_id, $secret_id);
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
