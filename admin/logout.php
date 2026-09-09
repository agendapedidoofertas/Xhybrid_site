<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';

auth_boot_session();
logout_user();
header('Location: login.php');
exit;
