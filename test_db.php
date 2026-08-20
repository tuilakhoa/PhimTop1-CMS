<?php
require_once 'includes/db.php';
$repo = getCommentRepository();
print_r($repo->getAllComments(1, 10));
