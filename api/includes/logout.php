<?php

# Closing the session and deleting all values associated with the session
session_destroy();

# Redirecting the user back to login page
header('Location: /');
exit;
