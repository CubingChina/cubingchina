<?php
unset($config['components']['errorHandler']);
$config['components']['urlManager']['rules'] = array_slice($config['components']['urlManager']['rules'], 2, null, true);