<?php
session_start();
// This file simply keeps the session alive
// No need to do anything else
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?> 