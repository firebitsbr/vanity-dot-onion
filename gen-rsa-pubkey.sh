#!/bin/bash
set -e

b2hex() { echo -n $1|base64 --decode | xxd -p -u | tr -d \\n; }
modulus=$(b2hex "pmJk58BjgqbDzqr7OZmUGyDNU06jhyta+sxr6ZOtWWk+89gQhBT73U3ZGeLUN//ebn44T8exFQ43ifyVG09EAFv2H5c1HxSbgZYtCdnlCc4YYh6Qv9Xd62+lIZ8L798Up3xvJuy3iPwKNxS7cmRYBmT8LMNwWWMiZ97A9ZiSCa0=")
exponent=$(b2hex "B/ZPZw==")
asn1conf=$(echo -e "asn1=SEQUENCE:pubkeyinfo\n[pubkeyinfo]\nalgorithm=SEQUENCE:rsa_alg\npubkey=BITWRAP,SEQUENCE:rsapubkey\n[rsa_alg]\nalgorithm=OID:rsaEncryption\nparameter=NULL\n[rsapubkey]\nn=INTEGER:0x$modulus\ne=INTEGER:0x$exponent" | openssl asn1parse -genconf /dev/stdin -noout -out /dev/stdout | base64)
# echo "Secret message wohoo!" | openssl rsautl -encrypt -keyform der -pubin -inkey <(base64 --decode <<<$asn1conf)
echo $modulus
echo $exponent
echo $asn1conf
