<?php

function sanitizeInput(string $input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

?>