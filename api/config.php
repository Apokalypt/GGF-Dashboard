<?php
# CLIENT ID
$client_id = $_SERVER['GGF_DASHBOARD_CLIENT_ID'];

# CLIENT SECRET
$client_secret = $_SERVER['GGF_DASHBOARD_SECRET'];

# SCOPES SEPARATED BY + SIGN
$scopes = "identify+guilds.members.read	";

# REDIRECT URL
$http_scheme = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $http_scheme = 'https';
} else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $http_scheme = 'https';
}
$redirect_url = $http_scheme . '://' . $_SERVER['HTTP_HOST'] . '/auth/discord';

$guild_id = $_SERVER['GGF_DASHBOARD_GUILD_ID'];
$moderator_role_id = $_SERVER['GGF_DASHBOARD_MODERATOR_ROLE_ID'];
