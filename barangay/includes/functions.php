<?php
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function displayError($msg) {
    return "<div class='alert alert-error'>$msg</div>";
}

function displaySuccess($msg) {
    return "<div class='alert alert-success'>$msg</div>";
}
?>