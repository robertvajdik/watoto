<?php
require_once __DIR__ . '/includes/auth.php';
admin_logout();
redirect(SITE_URL . '/admin/index.php');
