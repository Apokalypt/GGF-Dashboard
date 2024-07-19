<?php

$GLOBALS['base_url'] = "https://discord.com/api/v10";
$GLOBALS['delta_time_expire'] = 60; // 60 seconds

##################################################################
## Authentication
##################################################################
/**
 * Return the formatted token found in the cookies or refresh the token if it is not found
 *
 * @return string|null
 */
function get_token_formatted(): string | null
{
    if (!isset($_COOKIE['access_token']) || !isset($_COOKIE['token_type'])) {
        refresh_token();
    }

    if (isset($_COOKIE['access_token']) && isset($_COOKIE['token_type'])) {
        return $_COOKIE['token_type'] . ' ' . $_COOKIE['access_token'];
    } else {
        return null;
    }
}

/**
 * Call the Discord token endpoint, set the cookies and return true if it's successful
 *
 * @param array $data
 * @return bool
 */
function call_token_endpoint(array $data): bool
{
    $url = $GLOBALS['base_url'] . "/oauth2/token";
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

    $expires_at = time() + $results['expires_in'] - $GLOBALS['delta_time_expire'];
    setcookie("access_token", $results['access_token'], $expires_at, "/");
    setcookie("token_type", $results['token_type'], $expires_at, "/");
    $expires_in_one_year = time() + 60 * 60 * 24 * 365;
    setcookie("refresh_token", $results['refresh_token'], $expires_in_one_year, "/");

    return true;
}

/**
 * Try to fetch the user's token and return true if it's successful
 *
 * @param $redirect_url
 * @param $client_id
 * @param $client_secret
 * @return bool
 */
function authenticate_user($redirect_url, $client_id, $client_secret): bool
{
    if (!isset($_GET['code']) || !isset($_GET['state'])) {
        return false;
    }
    $code = $_GET['code'];
    $state = $_GET['state'];

    if (!check_state($state)) {
        return false;
    }

    $data = array(
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "grant_type" => "authorization_code",
        "code" => $code,
        "redirect_uri" => $redirect_url
    );
    return call_token_endpoint($data);
}

/**
 * Refresh the token if it's possible and return true if it's successful
 *
 * @return bool
 */
function refresh_token(): bool
{
    if (!isset($_COOKIE['refresh_token'])) {
        return false;
    }

    $data = array(
        "grant_type" => "refresh_token",
        "refresh_token" => $_COOKIE['refresh_token']
    );
    return call_token_endpoint($data);
}

/**
 * Check if the state in session is the same as the one passed as parameter
 *
 * @param $state
 * @return bool
 */
function check_state($state): bool
{
    if ($state == $_SESSION['state']) {
        return true;
    } else {
        return false;
    }
}

/**
 * Generate a random state and store it in the session to validate the redirection on the auth endpoint
 *
 * @return string
 */
function gen_state(): string
{
    $_SESSION['state'] = bin2hex(openssl_random_pseudo_bytes(12));

    return $_SESSION['state'];
}

/**
 * Generate the authorize endpoint with the client_id, redirect_uri, scope and all the necessary parameters
 *
 * @param $client_id
 * @param $redirect
 * @param $scope
 * @return string
 */
function get_authorize_endpoint($client_id, $redirect, $scope): string
{
    $state = gen_state();
    return 'https://discordapp.com/oauth2/authorize?response_type=code&prompt=none&client_id=' . $client_id . '&redirect_uri=' . $redirect . '&scope=' . $scope . "&state=" . $state;
}


##################################################################
## Data
##################################################################
/**
 * Retrieve the member details from the Discord API
 *
 * @param $guild_id
 * @return mixed|null
 */
function get_member_details($guild_id): mixed
{
    $url = $GLOBALS['base_url'] . "/users/@me/guilds/" . $guild_id . "/member";
    $headers = array('Content-Type: application/x-www-form-urlencoded', 'Authorization: ' . get_token_formatted());
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

##################################################################
## Authorization
##################################################################
/**
 * Make sure the user is authorized to access the page
 *
 * @param $guild_id
 * @param $moderator_role_id
 * @return bool
 */
function user_is_authorized($guild_id, $moderator_role_id): bool
{
    global $client_id;

    if (!isset($_COOKIE['access_token'])) {
        return false;
    }
    if (!isset($_COOKIE['client_id']) || $_COOKIE['client_id'] != $client_id) {
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
