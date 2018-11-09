<?php
namespace Transbank\Webpay\Model\Libwebpay\soap;

/**
 * xmlseclibs.php
 *
 * Copyright (c) 2007-2010, Robert Richards <rrichards@cdatazone.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Robert Richards nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author     Robert Richards <rrichards@cdatazone.org>
 * @copyright  2007-2010 Robert Richards <rrichards@cdatazone.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    1.3.0-dev
 */
/*
  Functions to generate simple cases of Exclusive Canonical XML - Callable function is C14NGeneral()
  i.e.: $canonical = C14NGeneral($domelement, TRUE);
 */

/* helper function */
function sortAndAddAttrs($element, $arAtts) {
    $newAtts = array();
    foreach ($arAtts AS $attnode) {
        $newAtts[$attnode->nodeName] = $attnode;
    }
    ksort($newAtts);
    foreach ($newAtts as $attnode) {
        $element->setAttribute($attnode->nodeName, $attnode->nodeValue);
    }
}

/* helper function */

function canonical($tree, $element, $withcomments) {
    if ($tree->nodeType != XML_DOCUMENT_NODE) {
        $dom = $tree->ownerDocument;
    } else {
        $dom = $tree;
    }
    if ($element->nodeType != XML_ELEMENT_NODE) {
        if ($element->nodeType == XML_DOCUMENT_NODE) {
            foreach ($element->childNodes AS $node) {
                canonical($dom, $node, $withcomments);
            }
            return;
        }
        if ($element->nodeType == XML_COMMENT_NODE && !$withcomments) {
            return;
        }
        $tree->appendChild($dom->importNode($element, TRUE));
        return;
    }
    $arNS = array();
    if ($element->namespaceURI != "") {
        if ($element->prefix == "") {
            $elCopy = $dom->createElementNS($element->namespaceURI, $element->nodeName);
        } else {
            $prefix = $tree->lookupPrefix($element->namespaceURI);
            if ($prefix == $element->prefix) {
                $elCopy = $dom->createElementNS($element->namespaceURI, $element->nodeName);
            } else {
                $elCopy = $dom->createElement($element->nodeName);
                $arNS[$element->namespaceURI] = $element->prefix;
            }
        }
    } else {
        $elCopy = $dom->createElement($element->nodeName);
    }
    $tree->appendChild($elCopy);

    /* Create \DOMXPath based on original document */
    $xPath = new \DOMXPath($element->ownerDocument);

    /* Get namespaced attributes */
    $arAtts = $xPath->query('attribute::*[namespace-uri(.) != ""]', $element);

    /* Create an array with namespace URIs as keys, and sort them */
    foreach ($arAtts AS $attnode) {
        if (array_key_exists($attnode->namespaceURI, $arNS) &&
                ($arNS[$attnode->namespaceURI] == $attnode->prefix)) {
            continue;
        }
        $prefix = $tree->lookupPrefix($attnode->namespaceURI);
        if ($prefix != $attnode->prefix) {
            $arNS[$attnode->namespaceURI] = $attnode->prefix;
        } else {
            $arNS[$attnode->namespaceURI] = NULL;
        }
    }
    if (count($arNS) > 0) {
        asort($arNS);
    }

    /* Add namespace nodes */
    foreach ($arNS AS $namespaceURI => $prefix) {
        if ($prefix != NULL) {
            $elCopy->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:" . $prefix, $namespaceURI);
        }
    }
    if (count($arNS) > 0) {
        ksort($arNS);
    }

    /* Get attributes not in a namespace, and then sort and add them */
    $arAtts = $xPath->query('attribute::*[namespace-uri(.) = ""]', $element);
    sortAndAddAttrs($elCopy, $arAtts);

    /* Loop through the URIs, and then sort and add attributes within that namespace */
    foreach ($arNS as $nsURI => $prefix) {
        $arAtts = $xPath->query('attribute::*[namespace-uri(.) = "' . $nsURI . '"]', $element);
        sortAndAddAttrs($elCopy, $arAtts);
    }

    foreach ($element->childNodes AS $node) {
        canonical($elCopy, $node, $withcomments);
    }
}

/*
 * @author OrangePeople Software Ltda <soporte@orangepeople.cl>
 * helper function
 * Modification by Hermann Alexander Arriagada Méndez
 * for IssuerSerial
 */

function getIssuerName($X509Cert) {
    /*$handler = fopen($X509Cert, "r");
    $cert = fread($handler, 8192);
    fclose($handler);*/
	$cert = $X509Cert;
    $cert_as_array = openssl_x509_parse($cert);
    $name = $cert_as_array['name'];
    $name = str_replace("/", ",", $name);
    $name = substr($name, 1, strlen($name));
    return $name;
}

/*
 * @author OrangePeople Software Ltda <soporte@orangepeople.cl>
 * helper function
 * Modification by Hermann Alexander Arriagada Méndez
 * for IssuerSerial
 */

function getSerialNumber($X509Cert) {
    /*$handler = fopen($X509Cert, "r");
    $cert = fread($handler, 8192);
    fclose($handler);*/
	$cert = $X509Cert;
    $cert_as_array = openssl_x509_parse($cert);
    $serialNumber = $cert_as_array['serialNumber'];
    return $serialNumber;
}

/*
  $element - \DOMElement for which to produce the canonical version of
  $exclusive - boolean to indicate exclusive canonicalization (must pass TRUE)
  $withcomments - boolean indicating wether or not to include comments in canonicalized form
 */

function C14NGeneral($element, $exclusive = FALSE, $withcomments = FALSE) {
    /* IF PHP 5.2+ then use built in canonical functionality */
    $php_version = explode('.', PHP_VERSION);
    if (($php_version[0] > 5) || ($php_version[0] == 5 && $php_version[1] >= 2)) {
        return $element->C14N($exclusive, $withcomments);
    }

    /* Must be element or document */
    if (!$element instanceof \DOMElement && !$element instanceof \DOMDocument) {
        return NULL;
    }
    /* Currently only exclusive XML is supported */
    if ($exclusive == FALSE) {
        throw new Exception("Only exclusive canonicalization is supported in this version of PHP");
    }

    $copyDoc = new \DOMDocument();
    canonical($copyDoc, $element, $withcomments);
    return $copyDoc->saveXML($copyDoc->documentElement, LIBXML_NOEMPTYTAG);
}

class XMLSecurityKey {

    const TRIPLEDES_CBC = 'http://www.w3.org/2001/04/xmlenc#tripledes-cbc';
    const AES128_CBC = 'http://www.w3.org/2001/04/xmlenc#aes128-cbc';
    const AES192_CBC = 'http://www.w3.org/2001/04/xmlenc#aes192-cbc';
    const AES256_CBC = 'http://www.w3.org/2001/04/xmlenc#aes256-cbc';
    const RSA_1_5 = 'http://www.w3.org/2001/04/xmlenc#rsa-1_5';
    const RSA_OAEP_MGF1P = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';
    const DSA_SHA1 = 'http://www.w3.org/2000/09/xmldsig#dsa-sha1';
    const RSA_SHA1 = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
    const RSA_SHA256 = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';

    private $cryptParams = array();
    public $type = 0;
    public $key = NULL;
    public $passphrase = "";
    public $iv = NULL;
    public $name = NULL;
    public $keyChain = NULL;
    public $isEncrypted = FALSE;
    public $encryptedCtx = NULL;
    public $guid = NULL;

    /**
     * This variable contains the certificate as a string if this key represents an X509-certificate.
     * If this key doesn't represent a certificate, this will be NULL.
     */
    private $x509Certificate = NULL;

    /* This variable contains the certificate thunbprint if we have loaded an X509-certificate. */
    private $X509Thumbprint = NULL;

    public function __construct($type, $params = NULL) {
        srand();
        switch ($type) {
            case (XMLSecurityKey::TRIPLEDES_CBC):
                $this->cryptParams['library'] = 'mcrypt';
                $this->cryptParams['cipher'] = MCRYPT_TRIPLEDES;
                $this->cryptParams['mode'] = MCRYPT_MODE_CBC;
                $this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#tripledes-cbc';
                break;
            case (XMLSecurityKey::AES128_CBC):
                $this->cryptParams['library'] = 'mcrypt';
                $this->cryptParams['cipher'] = MCRYPT_RIJNDAEL_128;
                $this->cryptParams['mode'] = MCRYPT_MODE_CBC;
                $this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#aes128-cbc';
                break;
            case (XMLSecurityKey::AES192_CBC):
                $this->cryptParams['library'] = 'mcrypt';
                $this->cryptParams['cipher'] = MCRYPT_RIJNDAEL_128;
                $this->cryptParams['mode'] = MCRYPT_MODE_CBC;
                $this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#aes192-cbc';
                break;
            case (XMLSecurityKey::AES256_CBC):
                $this->cryptParams['library'] = 'mcrypt';
                $this->cryptParams['cipher'] = MCRYPT_RIJNDAEL_128;
                $this->cryptParams['mode'] = MCRYPT_MODE_CBC;
                $this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#aes256-cbc';
                break;
            case (XMLSecurityKey::RSA_1_5):
                $this->cryptParams['library'] = 'openssl';
                $this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#rsa-1_5';
                if (is_array($params) && !empty($params['type'])) {
                    if ($params['type'] == 'public' || $params['type'] == 'private') {
                        $this->cryptParams['type'] = $params['type'];
                        break;
                    }
                }
                throw new Exception('Certificate "type" (private/public) must be passed via parameters');
                return;
            case (XMLSecurityKey::RSA_OAEP_MGF1P):
                $this->cryptParams['library'] = 'openssl';
                $this->cryptParams['padding'] = OPENSSL_PKCS1_OAEP_PADDING;
                $this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';
                $this->cryptParams['hash'] = NULL;
                if (is_array($params) && !empty($params['type'])) {
                    if ($params['type'] == 'public' || $params['type'] == 'private') {
                        $this->cryptParams['type'] = $params['type'];
                        break;
                    }
                }
                throw new Exception('Certificate "type" (private/public) must be passed via parameters');
                return;
            case (XMLSecurityKey::RSA_SHA1):
                $this->cryptParams['library'] = 'openssl';
                $this->cryptParams['method'] = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
                $this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;
                if (is_array($params) && !empty($params['type'])) {
                    if ($params['type'] == 'public' || $params['type'] == 'private') {
                        $this->cryptParams['type'] = $params['type'];
                        break;
                    }
                }
                throw new Exception('Certificate "type" (private/public) must be passed via parameters');
                break;
            case (XMLSecurityKey::RSA_SHA256):
                $this->cryptParams['library'] = 'openssl';
                $this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
                $this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams['digest'] = 'SHA256';
                if (is_array($params) && !empty($params['type'])) {
                    if ($params['type'] == 'public' || $params['type'] == 'private') {
                        $this->cryptParams['type'] = $params['type'];
                        break;
                    }
                }
                throw new Exception('Certificate "type" (private/public) must be passed via parameters');
                break;
            case (XMLSecurityKey::DSA_SHA1):
                $this->cryptParams['library'] = 'openssl';
                $this->cryptParams['method'] = 'http://www.w3.org/2000/09/xmldsig#dsa-sha1';
                $this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;
                if (is_array($params) && !empty($params['type'])) {
                    if ($params['type'] == 'public' || $params['type'] == 'private') {
                        $this->cryptParams['type'] = $params['type'];
                        break;
                    }
                }
                throw new Exception('Certificate "type" (private/public) must be passed via parameters');
                break;
            default:
                throw new Exception('Invalid Key Type');
                return;
        }
        $this->type = $type;
    }

    public function generateSessionKey() {
        $key = '';
        if (!empty($this->cryptParams['cipher']) && !empty($this->cryptParams['mode'])) {
            $keysize = mcrypt_module_get_algo_key_size($this->cryptParams['cipher']);
            /* Generating random key using iv generation routines */
            if (($keysize > 0) && ($td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', $this->cryptParams['mode'], ''))) {
                if ($this->cryptParams['cipher'] == MCRYPT_RIJNDAEL_128) {
                    $keysize = 16;
                    if ($this->type == XMLSecurityKey::AES256_CBC) {
                        $keysize = 32;
                    } elseif ($this->type == XMLSecurityKey::AES192_CBC) {
                        $keysize = 24;
                    }
                }
                while (strlen($key) < $keysize) {
                    $key .= mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
                }
                mcrypt_module_close($td);
                $key = substr($key, 0, $keysize);
                $this->key = $key;
            }
        }
        return $key;
    }

    public static function getRawThumbprint($cert) {

        $arCert = explode("\n", $cert);
        $data = '';
        $inData = FALSE;

        foreach ($arCert AS $curData) {
            if (!$inData) {
                if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) == 0) {
                    $inData = TRUE;
                }
            } else {
                if (strncmp($curData, '-----END CERTIFICATE', 20) == 0) {
                    $inData = FALSE;
                    break;
                }
                $data .= trim($curData);
            }
        }

        if (!empty($data)) {
            return strtolower(sha1(base64_decode($data)));
        }

        return NULL;
    }

    public function loadKey($key, $isFile = FALSE, $isCert = FALSE) {
        if ($isFile) {
            $this->key = file_get_contents($key);
        } else {
            $this->key = $key;
        }
        if ($isCert) {
            $this->key = openssl_x509_read($this->key);
            openssl_x509_export($this->key, $str_cert);
            $this->x509Certificate = $str_cert;
            $this->key = $str_cert;
        } else {
            $this->x509Certificate = NULL;
        }

        if ($this->cryptParams['library'] == 'openssl') {
            if ($this->cryptParams['type'] == 'public') {
                if ($isCert) {
                    /* Load the thumbprint if this is an X509 certificate. */
                    $this->X509Thumbprint = self::getRawThumbprint($this->key);
                }
                $this->key = openssl_get_publickey($this->key);
            } else {
                $this->key = openssl_get_privatekey($this->key, $this->passphrase);
            }
        } else if ($this->cryptParams['cipher'] == MCRYPT_RIJNDAEL_128) {
            /* Check key length */
            switch ($this->type) {
                case (XMLSecurityKey::AES256_CBC):
                    if (strlen($this->key) < 25) {
                        throw new Exception('Key must contain at least 25 characters for this cipher');
                    }
                    break;
                case (XMLSecurityKey::AES192_CBC):
                    if (strlen($this->key) < 17) {
                        throw new Exception('Key must contain at least 17 characters for this cipher');
                    }
                    break;
            }
        }
    }

    private function encryptMcrypt($data) {
        $td = mcrypt_module_open($this->cryptParams['cipher'], '', $this->cryptParams['mode'], '');
        $this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $this->key, $this->iv);
        if ($this->cryptParams['mode'] == MCRYPT_MODE_CBC) {
            $bs = mcrypt_enc_get_block_size($td);
            for ($datalen0 = $datalen = strlen($data); (($datalen % $bs) != ($bs - 1)); $datalen++)
                $data.=chr(rand(1, 127));
            $data.=chr($datalen - $datalen0 + 1);
        }
        $encrypted_data = $this->iv . mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $encrypted_data;
    }

    private function decryptMcrypt($data) {
        $td = mcrypt_module_open($this->cryptParams['cipher'], '', $this->cryptParams['mode'], '');
        $iv_length = mcrypt_enc_get_iv_size($td);

        $this->iv = substr($data, 0, $iv_length);
        $data = substr($data, $iv_length);

        mcrypt_generic_init($td, $this->key, $this->iv);
        $decrypted_data = mdecrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        if ($this->cryptParams['mode'] == MCRYPT_MODE_CBC) {
            $dataLen = strlen($decrypted_data);
            $paddingLength = substr($decrypted_data, $dataLen - 1, 1);
            $decrypted_data = substr($decrypted_data, 0, $dataLen - ord($paddingLength));
        }
        return $decrypted_data;
    }

    private function encryptOpenSSL($data) {
        if ($this->cryptParams['type'] == 'public') {
            if (!openssl_public_encrypt($data, $encrypted_data, $this->key, $this->cryptParams['padding'])) {
                throw new Exception('Failure encrypting Data');
                return;
            }
        } else {
            if (!openssl_private_encrypt($data, $encrypted_data, $this->key, $this->cryptParams['padding'])) {
                throw new Exception('Failure encrypting Data');
                return;
            }
        }
        return $encrypted_data;
    }

    private function decryptOpenSSL($data) {
        if ($this->cryptParams['type'] == 'public') {
            if (!openssl_public_decrypt($data, $decrypted, $this->key, $this->cryptParams['padding'])) {
                throw new Exception('Failure decrypting Data');
                return;
            }
        } else {
            if (!openssl_private_decrypt($data, $decrypted, $this->key, $this->cryptParams['padding'])) {
                throw new Exception('Failure decrypting Data');
                return;
            }
        }
        return $decrypted;
    }

    private function signOpenSSL($data) {
        $algo = OPENSSL_ALGO_SHA1;
        if (!empty($this->cryptParams['digest'])) {
            $algo = $this->cryptParams['digest'];
        }

        if (!openssl_sign($data, $signature, $this->key, $algo)) {
            throw new Exception('Failure Signing Data: ' . openssl_error_string() . ' - ' . $algo);
            return;
        }
        return $signature;
    }

    private function verifyOpenSSL($data, $signature) {
        $algo = OPENSSL_ALGO_SHA1;
        $return_value = false;
        if (!empty($this->cryptParams['digest'])) {
            $algo = $this->cryptParams['digest'];
        }

        $verify_value = openssl_verify($data, $signature, $this->key, $algo);
        if ($verify_value == 1) {
            $return_value = true;
        }
        return $return_value;
    }

    public function encryptData($data) {
        switch ($this->cryptParams['library']) {
            case 'mcrypt':
                return $this->encryptMcrypt($data);
                break;
            case 'openssl':
                return $this->encryptOpenSSL($data);
                break;
        }
    }

    public function decryptData($data) {
        switch ($this->cryptParams['library']) {
            case 'mcrypt':
                return $this->decryptMcrypt($data);
                break;
            case 'openssl':
                return $this->decryptOpenSSL($data);
                break;
        }
    }

    public function signData($data) {
        switch ($this->cryptParams['library']) {
            case 'openssl':
                return $this->signOpenSSL($data);
                break;
        }
    }

    public function verifySignature($data, $signature) {
        switch ($this->cryptParams['library']) {
            case 'openssl':
                return $this->verifyOpenSSL($data, $signature);
                break;
        }
    }

    public function getAlgorith() {
        return $this->cryptParams['method'];
    }

    static function makeAsnSegment($type, $string) {
        switch ($type) {
            case 0x02:
                if (ord($string) > 0x7f)
                    $string = chr(0) . $string;
                break;
            case 0x03:
                $string = chr(0) . $string;
                break;
        }

        $length = strlen($string);

        if ($length < 128) {
            $output = sprintf("%c%c%s", $type, $length, $string);
        } else if ($length < 0x0100) {
            $output = sprintf("%c%c%c%s", $type, 0x81, $length, $string);
        } else if ($length < 0x010000) {
            $output = sprintf("%c%c%c%c%s", $type, 0x82, $length / 0x0100, $length % 0x0100, $string);
        } else {
            $output = NULL;
        }
        return($output);
    }

    /* Modulus and Exponent must already be base64 decoded */

    static function convertRSA($modulus, $exponent) {
        /* make an ASN publicKeyInfo */
        $exponentEncoding = XMLSecurityKey::makeAsnSegment(0x02, $exponent);
        $modulusEncoding = XMLSecurityKey::makeAsnSegment(0x02, $modulus);
        $sequenceEncoding = XMLSecurityKey:: makeAsnSegment(0x30, $modulusEncoding . $exponentEncoding);
        $bitstringEncoding = XMLSecurityKey::makeAsnSegment(0x03, $sequenceEncoding);
        $rsaAlgorithmIdentifier = pack("H*", "300D06092A864886F70D0101010500");
        $publicKeyInfo = XMLSecurityKey::makeAsnSegment(0x30, $rsaAlgorithmIdentifier . $bitstringEncoding);

        /* encode the publicKeyInfo in base64 and add PEM brackets */
        $publicKeyInfoBase64 = base64_encode($publicKeyInfo);
        $encoding = "-----BEGIN PUBLIC KEY-----\n";
        $offset = 0;
        while ($segment = substr($publicKeyInfoBase64, $offset, 64)) {
            $encoding = $encoding . $segment . "\n";
            $offset += 64;
        }
        return $encoding . "-----END PUBLIC KEY-----\n";
    }

    public function serializeKey($parent) {

    }

    /**
     * Retrieve the X509 certificate this key represents.
     *
     * Will return the X509 certificate in PEM-format if this key represents
     * an X509 certificate.
     *
     * @return  The X509 certificate or NULL if this key doesn't represent an X509-certificate.
     */
    public function getX509Certificate() {
        return $this->x509Certificate;
    }

    /* Get the thumbprint of this X509 certificate.
     *
     * Returns:
     *  The thumbprint as a lowercase 40-character hexadecimal number, or NULL
     *  if this isn't a X509 certificate.
     */

    public function getX509Thumbprint() {
        return $this->X509Thumbprint;
    }

}

class XMLSecEnc {

    const template = "<xenc:EncryptedData xmlns:xenc='http://www.w3.org/2001/04/xmlenc#'>
   <xenc:CipherData>
      <xenc:CipherValue></xenc:CipherValue>
   </xenc:CipherData>
   </xenc:EncryptedData>";
    const Element = 'http://www.w3.org/2001/04/xmlenc#Element';
    const Content = 'http://www.w3.org/2001/04/xmlenc#Content';
    const URI = 3;
    const XMLENCNS = 'http://www.w3.org/2001/04/xmlenc#';

    private $encdoc = NULL;
    private $rawNode = NULL;
    public $type = NULL;
    public $encKey = NULL;
    private $references = array();

    public function __construct() {
        $this->_resetTemplate();
    }

    private function _resetTemplate() {
        $this->encdoc = new \DOMDocument();
        $this->encdoc->loadXML(XMLSecEnc::template);
    }

    public function addReference($name, $node, $type) {
        if (!$node instanceOf \DOMNode) {
            throw new Exception('$node is not of type \DOMNode');
        }
        $curencdoc = $this->encdoc;
        $this->_resetTemplate();
        $encdoc = $this->encdoc;
        $this->encdoc = $curencdoc;
        $refuri = XMLSecurityDSig::generate_GUID();
        $element = $encdoc->documentElement;
        $element->setAttribute("Id", $refuri);
        $this->references[$name] = array("node" => $node, "type" => $type, "encnode" => $encdoc, "refuri" => $refuri);
    }

    public function setNode($node) {
        $this->rawNode = $node;
    }

    public function encryptNode($objKey, $replace = TRUE) {
        $data = '';
        if (empty($this->rawNode)) {
            throw new Exception('Node to encrypt has not been set');
        }
        if (!$objKey instanceof XMLSecurityKey) {
            throw new Exception('Invalid Key');
        }
        $doc = $this->rawNode->ownerDocument;
        $xPath = new \DOMXPath($this->encdoc);
        $objList = $xPath->query('/xenc:EncryptedData/xenc:CipherData/xenc:CipherValue');
        $cipherValue = $objList->item(0);
        if ($cipherValue == NULL) {
            throw new Exception('Error locating CipherValue element within template');
        }
        switch ($this->type) {
            case (XMLSecEnc::Element):
                $data = $doc->saveXML($this->rawNode);
                $this->encdoc->documentElement->setAttribute('Type', XMLSecEnc::Element);
                break;
            case (XMLSecEnc::Content):
                $children = $this->rawNode->childNodes;
                foreach ($children AS $child) {
                    $data .= $doc->saveXML($child);
                }
                $this->encdoc->documentElement->setAttribute('Type', XMLSecEnc::Content);
                break;
            default:
                throw new Exception('Type is currently not supported');
                return;
        }

        $encMethod = $this->encdoc->documentElement->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:EncryptionMethod'));
        $encMethod->setAttribute('Algorithm', $objKey->getAlgorith());
        $cipherValue->parentNode->parentNode->insertBefore($encMethod, $cipherValue->parentNode);

        $strEncrypt = base64_encode($objKey->encryptData($data));
        $value = $this->encdoc->createTextNode($strEncrypt);
        $cipherValue->appendChild($value);

        if ($replace) {
            switch ($this->type) {
                case (XMLSecEnc::Element):
                    if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                        return $this->encdoc;
                    }
                    $importEnc = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, TRUE);
                    $this->rawNode->parentNode->replaceChild($importEnc, $this->rawNode);
                    return $importEnc;
                    break;
                case (XMLSecEnc::Content):
                    $importEnc = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, TRUE);
                    while ($this->rawNode->firstChild) {
                        $this->rawNode->removeChild($this->rawNode->firstChild);
                    }
                    $this->rawNode->appendChild($importEnc);
                    return $importEnc;
                    break;
            }
        }
    }

    public function encryptReferences($objKey) {
        $curRawNode = $this->rawNode;
        $curType = $this->type;
        foreach ($this->references AS $name => $reference) {
            $this->encdoc = $reference["encnode"];
            $this->rawNode = $reference["node"];
            $this->type = $reference["type"];
            try {
                $encNode = $this->encryptNode($objKey);
                $this->references[$name]["encnode"] = $encNode;
            } catch (Exception $e) {
                $this->rawNode = $curRawNode;
                $this->type = $curType;
                throw $e;
            }
        }
        $this->rawNode = $curRawNode;
        $this->type = $curType;
    }

    public function decryptNode($objKey, $replace = TRUE) {
        $data = '';
        if (empty($this->rawNode)) {
            throw new Exception('Node to decrypt has not been set');
        }
        if (!$objKey instanceof XMLSecurityKey) {
            throw new Exception('Invalid Key');
        }
        $doc = $this->rawNode->ownerDocument;
        $xPath = new \DOMXPath($doc);
        $xPath->registerNamespace('xmlencr', XMLSecEnc::XMLENCNS);
        /* Only handles embedded content right now and not a reference */
        $query = "./xmlencr:CipherData/xmlencr:CipherValue";
        $nodeset = $xPath->query($query, $this->rawNode);

        if ($node = $nodeset->item(0)) {
            $encryptedData = base64_decode($node->nodeValue);
            $decrypted = $objKey->decryptData($encryptedData);
            if ($replace) {
                switch ($this->type) {
                    case (XMLSecEnc::Element):
                        $newdoc = new \DOMDocument();
                        $newdoc->loadXML($decrypted);
                        if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                            return $newdoc;
                        }
                        $importEnc = $this->rawNode->ownerDocument->importNode($newdoc->documentElement, TRUE);
                        $this->rawNode->parentNode->replaceChild($importEnc, $this->rawNode);
                        return $importEnc;
                        break;
                    case (XMLSecEnc::Content):
                        if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                            $doc = $this->rawNode;
                        } else {
                            $doc = $this->rawNode->ownerDocument;
                        }
                        $newFrag = $doc->createDocumentFragment();
                        $newFrag->appendXML($decrypted);
                        $parent = $this->rawNode->parentNode;
                        $parent->replaceChild($newFrag, $this->rawNode);
                        return $parent;
                        break;
                    default:
                        return $decrypted;
                }
            } else {
                return $decrypted;
            }
        } else {
            throw new Exception("Cannot locate encrypted data");
        }
    }

    public function encryptKey($srcKey, $rawKey, $append = TRUE) {
        if ((!$srcKey instanceof XMLSecurityKey) || (!$rawKey instanceof XMLSecurityKey)) {
            throw new Exception('Invalid Key');
        }
        $strEncKey = base64_encode($srcKey->encryptData($rawKey->key));
        $root = $this->encdoc->documentElement;
        $encKey = $this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:EncryptedKey');
        if ($append) {
            $keyInfo = $root->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyInfo'));
            $keyInfo->appendChild($encKey);
        } else {
            $this->encKey = $encKey;
        }
        $encMethod = $encKey->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:EncryptionMethod'));
        $encMethod->setAttribute('Algorithm', $srcKey->getAlgorith());
        if (!empty($srcKey->name)) {
            $keyInfo = $encKey->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyInfo'));
            $keyInfo->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyName', $srcKey->name));
        }
        $cipherData = $encKey->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:CipherData'));
        $cipherData->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:CipherValue', $strEncKey));
        if (is_array($this->references) && count($this->references) > 0) {
            $refList = $encKey->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:ReferenceList'));
            foreach ($this->references AS $name => $reference) {
                $refuri = $reference["refuri"];
                $dataRef = $refList->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:DataReference'));
                $dataRef->setAttribute("URI", '#' . $refuri);
            }
        }
        return;
    }

    public function decryptKey($encKey) {
        if (!$encKey->isEncrypted) {
            throw new Exception("Key is not Encrypted");
        }
        if (empty($encKey->key)) {
            throw new Exception("Key is missing data to perform the decryption");
        }
        return $this->decryptNode($encKey, FALSE);
    }

    public function locateEncryptedData($element) {
        if ($element instanceof \DOMDocument) {
            $doc = $element;
        } else {
            $doc = $element->ownerDocument;
        }
        if ($doc) {
            $xpath = new \DOMXPath($doc);
            $query = "//*[local-name()='EncryptedData' and namespace-uri()='" . XMLSecEnc::XMLENCNS . "']";
            $nodeset = $xpath->query($query);
            return $nodeset->item(0);
        }
        return NULL;
    }

    public function locateKey($node = NULL) {
        if (empty($node)) {
            $node = $this->rawNode;
        }
        if (!$node instanceof \DOMNode) {
            return NULL;
        }
        if ($doc = $node->ownerDocument) {
            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('xmlsecenc', XMLSecEnc::XMLENCNS);
            $query = ".//xmlsecenc:EncryptionMethod";
            $nodeset = $xpath->query($query, $node);
            if ($encmeth = $nodeset->item(0)) {
                $attrAlgorithm = $encmeth->getAttribute("Algorithm");
                try {
                    $objKey = new XMLSecurityKey($attrAlgorithm, array('type' => 'private'));
                } catch (Exception $e) {
                    return NULL;
                }
                return $objKey;
            }
        }
        return NULL;
    }

    static function staticLocateKeyInfo($objBaseKey = NULL, $node = NULL) {
        if (empty($node) || (!$node instanceof \DOMNode)) {
            return NULL;
        }
        if ($doc = $node->ownerDocument) {
            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('xmlsecenc', XMLSecEnc::XMLENCNS);
            $xpath->registerNamespace('xmlsecdsig', XMLSecurityDSig::XMLDSIGNS);
            $query = "./xmlsecdsig:KeyInfo";
            $nodeset = $xpath->query($query, $node);
            if ($encmeth = $nodeset->item(0)) {
                foreach ($encmeth->childNodes AS $child) {
                    switch ($child->localName) {
                        case 'KeyName':
                            if (!empty($objBaseKey)) {
                                $objBaseKey->name = $child->nodeValue;
                            }
                            break;
                        case 'KeyValue':
                            foreach ($child->childNodes AS $keyval) {
                                switch ($keyval->localName) {
                                    case 'DSAKeyValue':
                                        throw new Exception("DSAKeyValue currently not supported");
                                        break;
                                    case 'RSAKeyValue':
                                        $modulus = NULL;
                                        $exponent = NULL;
                                        if ($modulusNode = $keyval->getElementsByTagName('Modulus')->item(0)) {
                                            $modulus = base64_decode($modulusNode->nodeValue);
                                        }
                                        if ($exponentNode = $keyval->getElementsByTagName('Exponent')->item(0)) {
                                            $exponent = base64_decode($exponentNode->nodeValue);
                                        }
                                        if (empty($modulus) || empty($exponent)) {
                                            throw new Exception("Missing Modulus or Exponent");
                                        }
                                        $publicKey = XMLSecurityKey::convertRSA($modulus, $exponent);
                                        $objBaseKey->loadKey($publicKey);
                                        break;
                                }
                            }
                            break;
                        case 'RetrievalMethod':
                            /* Not currently supported */
                            break;
                        case 'EncryptedKey':
                            $objenc = new XMLSecEnc();
                            $objenc->setNode($child);
                            if (!$objKey = $objenc->locateKey()) {
                                throw new Exception("Unable to locate algorithm for this Encrypted Key");
                            }
                            $objKey->isEncrypted = TRUE;
                            $objKey->encryptedCtx = $objenc;
                            XMLSecEnc::staticLocateKeyInfo($objKey, $child);
                            return $objKey;
                            break;
                        case 'X509Data':
                            if ($x509certNodes = $child->getElementsByTagName('X509Certificate')) {
                                if ($x509certNodes->length > 0) {
                                    $x509cert = $x509certNodes->item(0)->textContent;
                                    $x509cert = str_replace(array("\r", "\n"), "", $x509cert);
                                    $x509cert = "-----BEGIN CERTIFICATE-----\n" . chunk_split($x509cert, 64, "\n") . "-----END CERTIFICATE-----\n";
                                    $objBaseKey->loadKey($x509cert, FALSE, TRUE);
                                }
                            }
                            break;
                    }
                }
            }
            return $objBaseKey;
        }
        return NULL;
    }

    public function locateKeyInfo($objBaseKey = NULL, $node = NULL) {
        if (empty($node)) {
            $node = $this->rawNode;
        }
        return XMLSecEnc::staticLocateKeyInfo($objBaseKey, $node);
    }

}
