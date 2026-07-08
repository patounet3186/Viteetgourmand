<?php

session_unset();
session_destroy();

header('Location: ?page=home');
exit;