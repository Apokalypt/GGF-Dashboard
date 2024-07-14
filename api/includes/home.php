<?php

require __DIR__ . "/../modules/discord.php";

global $client_id, $secret_id, $guild_id, $moderator_role_id, $redirect_url, $scopes;

if (user_is_authorized($guild_id, $moderator_role_id)) {
    header('Location: /dashboard');
    exit;
}

?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GGF - Discord</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/css/bootstrap.min.css" integrity="sha384-DhY6onE6f3zzKbjUPRc2hOzGAdEf4/Dz+WJwBvEYL/lkkIsI3ihufq9hk9K4lVoK" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<main class="p-0 base">
    <a class="btn btn-lg btn-discord btn-block" href="<?=$auth_url = url($client_id, $redirect_url, $scopes)?>">SE CONNECTER</a>
</main>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/js/bootstrap.bundle.min.js" integrity="sha384-BOsAfwzjNJHrJ8cZidOg56tcQWfp6y72vEJ8xQ9w6Quywb24iOsW913URv1IS4GD" crossorigin="anonymous"></script>
</body>
