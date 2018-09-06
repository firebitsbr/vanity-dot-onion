<?php
// Main doc: https://gitweb.torproject.org/torspec.git
// Source: https://gitweb.torproject.org/torspec.git/tree/rend-spec-v2.txt, 1.5
require_once('base32_rfc4648.php');

/**
 * <XmlMatchOutput>
 *  <GeneratedDate>2014-08-05T07:14:50.329955Z</GeneratedDate>
 *  <Hash>prefix64kxpwmzdz.onion</Hash>
 *  <PrivateKey>-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCmYmTnwGOCpsPOqvs5mZQbIM1TTqOHK1r6zGvpk61ZaT7z2BCE
FPvdTdkZ4tQ3/95ufjhPx7EVDjeJ/JUbT0QAW/YflzUfFJuBli0J2eUJzhhiHpC/
1d3rb6Uhnwvv3xSnfG8m7LeI/Ao3FLtyZFgGZPwsw3BZYyJn3sD1mJIJrQIEB/ZP
ZwKBgCTUQTR4zcz65zSOfo95l3YetVhfmApYcQQd8HTxgTqEsjr00XzW799ioIWt
vaKMCtJlkWLz4N1EqflOH3WnXsEkNA5AVFe1FTirijuaH7e46fuaPJWhaSq1qERT
eQT1jY2jytnsJT0VR7e2F83FKINjLeccnkkiVknsjrOPrzkXAkEA0Ky+vQdEj64e
iP4Rxc1NreB7oKor40+w7XSA0hyLA3JQjaHcseg/bqYxPZ5J4JkCNmjavGdM1v6E
OsVVaMWQ7QJBAMweWSWtLp6rVOvTcjZg+l5+D2NH+KbhHbNLBcSDIvHNmD9RzGM1
Xvt+rR0FA0wUDelcdJt0R29v2t19k2IBA8ECQFMDRoOQ+GBSoDUs7PUWdcXtM7Nt
QW350QEJ1hBJkG2SqyNJuepH4PIktjfytgcwQi9w7iFafyxcAAEYgj4HZw8CQAUI
3xXEA2yZf9/wYax6/Gm67cpKc3sgKVczFxsHhzEml6hi5u0FG7aNs7jQTRMW0aVF
P8Ecx3l7iZ6TeakqGhcCQGdhCaEb7bybAmwQ520omqfHWSte2Wyh+sWZXNy49EBg
d1mBig/w54sOBCUHjfkO9gyiANP/uBbR6k/bnmF4dMc=
-----END RSA PRIVATE KEY-----
 *  </PrivateKey>
 *  <PublicModulusBytes>pmJk58BjgqbDzqr7OZmUGyDNU06jhyta+sxr6ZOtWWk+89gQhBT73U3ZGeLUN//ebn44T8exFQ43ifyVG09EAFv2H5c1HxSbgZYtCdnlCc4YYh6Qv9Xd62+lIZ8L798Up3xvJuy3iPwKNxS7cmRYBmT8LMNwWWMiZ97A9ZiSCa0=</PublicModulusBytes>
 *  <PublicExponentBytes>B/ZPZw==</PublicExponentBytes>
 * </XmlMatchOutput>
 */

/**
 * Public Key
 * After writing above <PrivateKey> to priv.key
 * Execute: openssl rsa -pubout < priv.key > priv.pub
 */
$public_key = 'MIGgMA0GCSqGSIb3DQEBAQUAA4GOADCBigKBgQCmYmTnwGOCpsPOqvs5mZQbIM1TTqOHK1r6zGvpk61ZaT7z2BCEFPvdTdkZ4tQ3/95ufjhPx7EVDjeJ/JUbT0QAW/YflzUfFJuBli0J2eUJzhhiHpC/1d3rb6Uhnwvv3xSnfG8m7LeI/Ao3FLtyZFgGZPwsw3BZYyJn3sD1mJIJrQIEB/ZPZw==';
$key = base64_decode($public_key);

// Hash Key with Sha1
$key_hash = hash('sha1', $key, true);
$key_hash_80 = substr($key_hash, strlen($key_hash)-10, 10); /* first 80 bits of this */

// Base32 Encode the 80-bit hash
$b32 = strtolower(Base32::encode($key_hash_80));
echo "does [prefix64kxpwmzdz] = [$b32]?\n";

echo "binary:\n";
for ($x = 0; $x < strlen($key_hash) - 1; $x++)
{
	$ch = ord(substr($key_hash, $x, 1));
	echo $x . ": (" . str_pad($ch, 3, " ", STR_PAD_LEFT) . ") " . str_pad(decbin($ch), 8, "0", STR_PAD_LEFT) . "\n";
}

echo "known:\n";
$known_hash_80 = Base32::decode(strtoupper("prefix64kxpwmzdz"));
for ($x = 0; $x < strlen($known_hash_80) - 1; $x++)
{
	$ch = ord(substr($known_hash_80, $x, 1));
	echo $x . ": (" . str_pad($ch, 3, " ", STR_PAD_LEFT) . ") " . str_pad(decbin($ch), 8, "0", STR_PAD_LEFT) . "\n";
}
