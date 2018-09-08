<?php
// Main doc: https://gitweb.torproject.org/torspec.git
// Source: https://gitweb.torproject.org/torspec.git/tree/rend-spec-v2.txt, 1.5
require_once('base32_rfc4648.php');

$private_key = <<<EOK
-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQCuvF9M1eCjLAHQzEoY3AjRDES4MtMbZnoVWnLSMQKS1TUu1czm
2CPWX4zHKVfQlEblZ9CZ5rlJDeVksz69rga4a6/zzi3xGok0kOOA6y4XGsO6spjA
nTTaWaFRljwqt1/g2Je9HmEMPtBngxMfi93LCGD28OIn9fmlQmIDkqbUtQIEDUNw
GQKBgAIKQG2xC78aE0j1pHVbdZgX3VrXGYfzDlVwmzcDuDAy19AWw0ATmdnaHhhS
UsogG2ZmaeOhwftG5avcjw6mRMJvF9Ck/m1wqOYRm7fYGnGVEQMW7XKZ3URcett2
c484r5yjkHFH3INE14+lHmZgy47m59WvrVPexwBmIfWiFcfJAkEA3JCT7y2pE0+G
4pBwziTojpK03y+VNVtVVqI4D8Hff9lzwgZ2fjc41+qV8SHdDz+hmy5fMvGGTY8w
kjLbX+1VDQJBAMrO71z1RBVCoB+diNw3n/rJfXKPdmnRBH+sFhAFWETaAe9hvVEk
BlTAJRhE8mk23htOLT/rrY1csCbwuJ6i5EkCQGklBacsQCmcjgNcRzTK5R/RvWuK
5BVNhOU/JoDA1O+f7+h0f+uXTjdFjCM3RZnk66Do3s49UFwbRXpHqRwSAC0CQQC9
Wbl0ZJn+z8L/K1uUcf2L9hMCRyvrS7NkTPWJR+yRs56eTPPsfO+JpFfllSU1HrXJ
3TAkpK/Cz9lqX35ShWhpAkAAiOrM9cM1gl90T1rcP8lSn/aKzaaL6zsBCJPsUKC+
yeYM3pI7Re4yLrOdMyfC4tl6eMLkFqewP0Zk5BVg75lJ
-----END RSA PRIVATE KEY-----
EOK;

$public_key = <<<EOK
-----BEGIN PUBLIC KEY-----
MIGgMA0GCSqGSIb3DQEBAQUAA4GOADCBigKBgQCuvF9M1eCjLAHQzEoY3AjRDES4
MtMbZnoVWnLSMQKS1TUu1czm2CPWX4zHKVfQlEblZ9CZ5rlJDeVksz69rga4a6/z
zi3xGok0kOOA6y4XGsO6spjAnTTaWaFRljwqt1/g2Je9HmEMPtBngxMfi93LCGD2
8OIn9fmlQmIDkqbUtQIEDUNwGQ==
-----END PUBLIC KEY-----
EOK;

$matches = array();
if (!preg_match('~^-----BEGIN ([A-Z ]+)-----\s*?([A-Za-z0-9+=/\r\n]+)\s*?-----END \1-----\s*$~D', $public_key, $matches) === 0) {
	die('Invalid PEM format encountered.'."\n");
}
$public_key_decoded = base64_decode(str_replace(array("\r", "\n"), array('', ''), $matches[2]));
// sources of substr($, 22):
// > https://gist.github.com/DonnchaC/d6428881f451097f329e
// > https://www.dlitz.net/software/pycrypto/api/current/Crypto.PublicKey.RSA._RSAobj-class.html#exportKey
echo "onion-name: " . strtolower(Base32::encode(sha1(substr($public_key_decoded, 22), true))) . "\n";
echo "\n";

$key_resource = openssl_pkey_get_private($private_key);
//$key_resource = openssl_pkey_get_public($public_key);
if ($key_resource === false) { die('error loading private key'); }
$key_details = openssl_pkey_get_details($key_resource);
echo "bits: " . $key_details['bits'] . "\n";
echo "key: " . $key_details['key'] . "\n";
echo "type: ";
switch ($key_details['type'])
{
	case OPENSSL_KEYTYPE_RSA:
		echo "OPENSSL_KEYTYPE_RSA\n";
		$decode = array(
			"modulus" => "n",
			"public exponent" => "e",
			"private exponent" => "d",
			"prime 1" => "p",
			"prime 2" => "q",
			"exponent1, d mod (p-1)" => "dmp1",
			"exponent2, d mod (q-1)" => "dmq1",
			"coefficient, (inverse of q) mod p" => "iqmp",
		);
		foreach ($decode as $english => $key)
		{
			if (array_key_exists($key, $key_details['rsa']))
			{
				echo "  " . $english . ":\n";
				echo "    " . bin2hex($key_details['rsa'][$key]) . "\n";
				echo "    " . base64_encode($key_details['rsa'][$key]) . "\n";
				echo "    " . sha1($key_details['rsa'][$key]) . "\n";
				echo "    " . strtolower(Base32::encode(sha1($key_details['rsa'][$key], true))) . "\n";
			}
		}
		break;
	case OPENSSL_KEYTYPE_DSA:
		echo "OPENSSL_KEYTYPE_DSA\n";
		break;
	case OPENSSL_KEYTYPE_DH:
		echo "OPENSSL_KEYTYPE_DH\n";
		break;
	case OPENSSL_KEYTYPE_EC:
		echo "OPENSSL_KEYTYPE_EC\n";
		break;
	default:
		echo "unknown\n";
		break;
}
