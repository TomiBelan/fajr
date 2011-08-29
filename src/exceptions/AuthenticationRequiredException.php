<?php
/**
 * Contains exception emitted when autorization is required, but the user is not
 * logged in.
 * 
 * The result of throwing this exception is display of the login dialog
 *
 * @copyright  Copyright (c) 2011 The Fajr authors.
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Exceptions
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */

namespace fajr\exceptions;

use Exception;

/**
 * This exception is thrown when the user is not logged in, but it is
 * required for the current request.
 *
 * @package    Fajr
 * @subpackage Exceptions
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
class AuthenticationRequiredException extends Exception
{

}