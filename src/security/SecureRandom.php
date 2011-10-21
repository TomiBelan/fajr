<?php
/**
 * Provides cryptographically secure random bytes generation
 *
 * @copyright  Based on frostschutz's post at
 *             http://forums.thedailywtf.com/forums/t/16453.aspx
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 * @package    Fajr
 * @subpackage Security
 * @author     frostschutz
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\security;

use libfajr\base\Preconditions;
use Exception;
use RuntimeException;

/**
 * Provides api for generating cryptographically strong random bytes.
 *
 * @package    Fajr
 * @subpackage Security
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class SecureRandom {
  /** @var array(SecureRandomProvider) providers of random bytes */
  private $providers;

  /**
   * Construct SecureRandom which will be using specified providers
   * When calling randomBytes(), providers will be queried and
   * first which will return random bytes will be used.
   *
   * @param array(SecureRandomProvider) providers
   */
  public function __construct(array $providers) {
    foreach ($providers as $provider) {
      Preconditions::check($provider instanceof SecureRandomProvider);
    }
    $this->providers = $providers;
  }

  /**
   * Returns a securely generated random *bytes* using the supplied providers.
   * Providers will be queried in the same order as the were given to constructor
   * and first which will return random bytes will be used.
   *
   * @param int $count number of random bytes to be generated
   *
   * @returns bytes generated bytes
   * @throws RuntimeException if any error occurs (or no provider returns random bytes)
   *    Warning: Never catch and ignore exceptions from this function!
   */
  public function randomBytes($count)
  {
    Preconditions::check(is_int($count));
    Preconditions::check($count > 0);

    // TODO(ppershing): verify following claim
    // Try the OpenSSL method first. This is the strongest.

    $output = false;
    foreach ($this->providers as $provider) {
      $output = $provider->randomBytes($count);
      if ($output !== false) {
        break;
      }
    }

    // No method was able to generate random bytes.
    // Do not ignore this and throw security error as
    // caller may forgot to check return value.
    if ($output == false) {
      throw new RuntimeException("Unable to generate random bytes.");
    }

    // internal check on sanity
    if (strlen($output) != $count) {
      throw new Exception("Wrong number random bytes were generated. ".
          "This may be a severe internal error in secure random.");
    }

    return $output;
  }
}
