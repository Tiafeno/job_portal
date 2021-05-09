<?php
require_once 'jp-helper-actions.php';
foreach (glob("elements/*.php") as $filename) {
    include $filename;
}