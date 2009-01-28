<?php
/*******************************************************************************
 * Copyright (c) 2007-2009 Intalio, Inc.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Antoine Toulme, Intalio Inc.
*******************************************************************************/

// Use a class to define the hooks to avoid bugs with already defined functions.
class Reference_backend {
    /*
     * Authenticate a user.
     * Returns the User object if the user is found, or false
     */
    function authenticate($User, $email, $password) {
        $User->userid = 5;
    }
    
    /**
     * Returns a user that is specialized in running the syncup script.
     */
    function syncupUser() {
        $User = new User();
        $User->loadFromID(1);
    }
    
    /**
     * Returns the genie user that represents the headless admin for most operations,
     * like importing a zip of translations.
     */
    function genieUser() {
        $User = new User();
        $User->loadFromID(1);
    }
}

function __register_backend_ref($addon) {
    $addon->register('user_authentication', array('Reference_backend', 'authenticate'));
    $addon->register('syncup_user', array('Reference_backend', 'syncupUser'));
    $addon->register('genie_user', array('Reference_backend', 'genieUser'));
}

global $register_function_backend;
$register_function_backend = '__register_backend_ref';

?>
