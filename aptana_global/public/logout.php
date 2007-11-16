<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/
require_once("../aptana.inc.php");
require_once(BABEL_BASE_DIR . 'code/logout.code.php');
$lastURL = GetSessionVar('s_pageLast');
exitTo("" . ($lastURL?$lastURL:"index.php"));
?>