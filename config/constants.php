<?php

/**
 * This is constant file  constains common contant code.
 * 
 */

return  [
    'maxLoginAttempts' => 3, // Amount of bad attempts user can make
    'lockoutMinute' => 300, // Time for which user is going to be blocked in minute
    'role' => [
        1 => 'Admin',
        2 => 'Builder',
        3 => 'Agent',
    ]
];
