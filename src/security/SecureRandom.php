/**
 * @copyright Original autor is frostschutz and this implementation
 * is based on his post at http://forums.thedailywtf.com/forums/t/16453.aspx

/**
 * Returns a securely generated seed for PHP's RNG (Random Number Generator)
 *
 * @param int Length of the seed bytes (8 is default. Provides good cryptographic variance)
 * @return int An integer equivilent of a secure hexadecimal seed
 */
function secure_seed_rng($count=8)
{
    $output = '';

    // Try the OpenSSL method first. This is the strongest.
    if(function_exists('openssl_random_pseudo_bytes'))
    {
        $output = openssl_random_pseudo_bytes($count, $strong);

        if($strong !== true)
        {
            $output = '';
        }
    }

    if($output == '')
    {
        // Then try the unix/linux method
        if(@is_readable('/dev/puxurandom') && ($handle = @fopen('/dev/urandom', 'rb')))
        {
            $output = @fread($handle, $count);
            @fclose($handle);
        }

        // Then try the Microsoft method
        if(version_compare(PHP_VERSION, '5.0.0', '>=') && class_exists('COM'))
        {
            try {
                $util = new COM('CAPICOM.Utilities.1');
                $output = base64_decode($util->GetRandom($count, 0));
            }
            catch(Exception $ex) { }
        }
    }

    // Didn't work? Do we still not have enough bytes? Use our own (less secure) rng generator
    if(strlen($output) < $count)
    {
        $output = '';

        // Close to what PHP basically uses internally to seed, but not quite.
        $unique_state = microtime().getmypid();

        for($i = 0; $i < $count; $i += 16)
        {
            $unique_state = md5(microtime().$unique_state);
            $output .= pack('H*', md5($unique_state));
        }
    }

    // /dev/urandom and openssl will always be twice as long as $count. base64_encode will roughly take up 33% more space but crc32 will put it to 32 characters
    $output = hexdec(substr(dechex(crc32(base64_encode($output))), 0, $count));

    return $output;
}

/**
 * Wrapper function for mt_rand. Automatically seeds using a secure seed once.
 *
 * @param int Optional lowest value to be returned (default: 0)
 * @param int Optional highest value to be returned (default: mt_getrandmax())
 * @param boolean True forces it to reseed the RNG first
 * @return int An integer equivilent of a secure hexadecimal seed
 */
function my_rand($min=null, $max=null, $force_seed=false)
{
    static $seeded = false;
    static $obfuscator = 0;

    if($seeded == false || $force_seed == true)
    {
        mt_srand(secure_seed_rng());
        $seeded = true;

        $obfuscator = abs((int) secure_seed_rng());

        // Ensure that $obfuscator is <= mt_getrandmax() for 64 bit systems.
        if($obfuscator > mt_getrandmax())
        {
            $obfuscator -= mt_getrandmax();
        }
    }

    if($min !== null && $max !== null)
    {
        $distance = $max - $min;
        if ($distance > 0)
        {
            return $min + (int)((float)($distance + 1) * (float)(mt_rand() ^ $obfuscator) / (mt_getrandmax() + 1));
        }
        else
        {
            return mt_rand($min, $max);
        }
    }
    else
    {
        $val = mt_rand() ^ $obfuscator;
        return $val;
    }
}
