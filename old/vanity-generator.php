<?php
/**
 * Use custom base32 encoder
 * https://github.com/ademarre/binary-to-text-php
 */
require_once('Base2n.php');

/**
 * Using OpenSSL
 */

/**
 * Make our keys
 * https://gitweb.torproject.org/torspec.git/tree/tor-spec.txt
 * 0.3. Ciphers
 * 
 * For a public-key cipher, unless otherwise specified, we use RSA with
 * 1024-bit keys and a fixed exponent of 65537.  We use OAEP-MGF1
 * padding, with SHA-1 as its digest function.  We leave the optional
 * "Label" parameter unset. (For OAEP padding, see
 * ftp://ftp.rsasecurity.com/pub/pkcs/pkcs-1/pkcs-1v2-1.pdf)
 */
$config = array(
//	"digest_alg" => "sha1",
	"private_key_bits" => 1024,
	"private_key_type" => OPENSSL_KEYTYPE_RSA,
);
$key_resource = openssl_pkey_new($config);

// Extract Private Key into $private_key
openssl_pkey_export($key_resource, $private_key);

// for the sake of funsies, get public key
$public_key = openssl_pkey_get_details($key_resource);
$public_key = $public_key['key'];

/**
 * Make our hash
 * https://gitweb.torproject.org/torspec.git/tree/rend-spec-v2.txt
 * 1.5. Alice receives a z.onion address.
 *
 * When Alice receives a pointer to a location-hidden service, it is as a
 * hostname of the form "z.onion", where z is a base32 encoding of a
 * 10-octet hash of Bob's service's public key, computed as follows:
 *
 *       1. Let H = H(PK).
 *       2. Let H' = the first 80 bits of H, considering each octet from
 *          most significant bit to least significant bit.
 *       3. Generate a 16-character encoding of H', using base32 as defined
 *          in RFC 4648.
 *
 * (We only use 80 bits instead of the 160 bits from SHA1 because we
 * don't need to worry about arbitrary collisions, and because it will
 * make handling the url's more convenient.)
 *
 * [Yes, numbers are allowed at the beginning.  See RFC 1123. -NM] 
 */
$replacements = array(
	'-----BEGIN PUBLIC KEY-----' => '',
	'-----END PUBLIC KEY-----' => '',
	"\r" => '',
	"\n" => '',
);
$public_key_stripped = strtr($public_key, $replacements);
$public_key_raw = base64_decode($public_key_stripped);
$sha1_hash = hash("sha1", $public_key_raw, true);
$sha1_80 = substr($sha1_hash, 0, 10);

// base32 encode this
$b32 = new Base2n(5, 'abcdefghijklmnopqrstuvwxyz234567', FALSE, TRUE, TRUE);
$vanity_onion = $b32->encode($sha1_80);
echo '.';

// for the sake of recording, lets key-format this stuff
$vanity_folder = 'keys' . '/' . substr($vanity_onion, 0, 1) . '/' . substr($vanity_onion, 1, 1);
@mkdir($vanity_folder, 0775, true);
file_put_contents($vanity_folder . '/' . $vanity_onion . '-private.key', $private_key);
file_put_contents($vanity_folder . '/' . $vanity_onion . '-public.key', $public_key);
