<?php

$GLOBALS['base_url'] = "https://discord.com/api/v10";

##################################################################
## Authentication
##################################################################

# A function to generate a random string to be used as state | (protection against CSRF)
function gen_state(): string
{
    $_SESSION['state'] = bin2hex(openssl_random_pseudo_bytes(12));
    return $_SESSION['state'];
}

# A function to generate oAuth2 URL for logging in
function url($clientid, $redirect, $scope): string
{
    $state = gen_state();
    $_SESSION['client_id'] = $clientid;
    return 'https://discordapp.com/oauth2/authorize?response_type=code&client_id=' . $clientid . '&redirect_uri=' . $redirect . '&scope=' . $scope . "&state=" . $state;
}

# A function to initialize and store access token in SESSION to be used for other requests
function init($redirect_url, $client_id, $client_secret): bool
{
    if (!isset($_GET['code']) || !isset($_GET['state'])) {
        return false;
    }
    $code = $_GET['code'];
    $state = $_GET['state'];

    if (!check_state($state)) {
        return false;
    }

    # Check if $state == $_SESSION['state'] to verify if the login is legit | CHECK THE FUNCTION get_state($state) FOR MORE INFORMATION.
    $url = $GLOBALS['base_url'] . "/oauth2/token";
    $data = array(
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "grant_type" => "authorization_code",
        "code" => $code,
        "redirect_uri" => $redirect_url
    );
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        return false;
    }

    $results = json_decode($response, true);
    if (!isset($results['access_token'])) {
        return false;
    }

    $_SESSION['token_type'] = $results['token_type'];
    $_SESSION['access_token'] = $results['access_token'];
    $_SESSION['expires_at'] = time() + $results['expires_in'];
    $_SESSION['refresh_token'] = $results['refresh_token'];

    return true;
}

function refresh_token(): bool
{
    $url = $GLOBALS['base_url'] . "/oauth2/token";
    $data = array(
        "grant_type" => "refresh_token",
        "refresh_token" => $_SESSION['refresh_token']
    );
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        return false;
    }

    $results = json_decode($response, true);
    if (!isset($results['access_token'])) {
        return false;
    }

    $_SESSION['token_type'] = $results['token_type'];
    $_SESSION['access_token'] = $results['access_token'];
    $_SESSION['expires_at'] = time() + $results['expires_in'];
    $_SESSION['refresh_token'] = $results['refresh_token'];

    return true;
}


function get_member_details($guild_id)
{
    $url = $GLOBALS['base_url'] . "/users/@me/guilds/" . $guild_id . "/member";
    $headers = array('Content-Type: application/x-www-form-urlencoded', 'Authorization: ' . $_SESSION['token_type'] . ' ' . $_SESSION['access_token']);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);
    curl_close($curl);

    # Return null if the status code is not 200
    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        return null;
    }

    $_SESSION['member'] = json_decode($response, true);

    return $_SESSION['member'];
}

function user_is_authorized($guild_id, $moderator_role_id): bool
{
    global $client_id;

    # If the 'expires_at' is less than the current time, then the token has expired. Refresh it.
    if (isset($_SESSION['expires_at']) && $_SESSION['expires_at'] < time()) {
        error_log("Access token has expired. Refreshing token");
        refresh_token();
    }

    if (!isset($_SESSION['access_token'])) {
        return false;
    }
    if (!isset($_SESSION['client_id']) || $_SESSION['client_id'] != $client_id) {
        return false;
    }

    if (!isset($_SESSION['member'])) {
        $member = get_member_details($guild_id);
    } else {
        $member = $_SESSION['member'];
    }
    if (!$member) {
        return false;
    }

    if (!in_array($moderator_role_id, $member['roles'])) {
        return false;
    }

    return true;
}

# A function to verify if login is legit
function check_state($state): bool
{
    if ($state == $_SESSION['state']) {
        return true;
    } else {
        # The login is not valid, so you should probably redirect them back to home page
        return false;
    }
}
