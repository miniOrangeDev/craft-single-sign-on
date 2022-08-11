<?php

namespace miniorangedev\craftsinglesignon\utilities;

use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;

include_once 'xmlseclibs.php';
// use \RobRichards\XMLSecLibs\XMLSecurityKey;
use \RobRichards\XMLSecLibs\XMLSecurityDSig;
use \RobRichards\XMLSecLibs\XMLSecEnc;

class Utilities {

	public static function generateID() {
		return '_' . self::stringToHex(self::generateRandomBytes(21));
	}

	public static function stringToHex($bytes) {
		$ret = '';
		for($i = 0; $i < strlen($bytes); $i++) {
			$ret .= sprintf('%02x', ord($bytes[$i]));
		}
		return $ret;
	}

	public static function generateRandomBytes($length, $fallback = TRUE) {
        return openssl_random_pseudo_bytes($length);
	}

	public static function createAuthnRequest($acsUrl, $issuer, $destination, $force_authn = 'false', $sso_binding_type = 'HttpRedirect', $saml_nameid_format= '') {
		$saml_nameid_format = 'urn:oasis:names:tc:SAML:' . $saml_nameid_format;
		$requestXmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
						'<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns="urn:oasis:names:tc:SAML:2.0:assertion" ID="' . self::generateID() .
						'" Version="2.0" IssueInstant="' . self::generateTimestamp() . '"';
		if( $force_authn == 'true') {
			$requestXmlStr .= ' ForceAuthn="true"';
		}
		$requestXmlStr .= ' ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" AssertionConsumerServiceURL="' . $acsUrl .
						'" Destination="' . htmlspecialchars($destination) . '"><saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . '</saml:Issuer><samlp:NameIDPolicy AllowCreate="true" Format="' . $saml_nameid_format . '"
                        /></samlp:AuthnRequest>';
		if(empty($sso_binding_type) || $sso_binding_type == 'HttpRedirect') {
			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			// update_option('mo_saml_request',$base64EncodedStr);
			$urlEncoded = urlencode($base64EncodedStr);
			$requestXmlStr = $urlEncoded;
		}else {
			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			// update_option('mo_saml_request',$base64EncodedStr);
		}
		return $requestXmlStr;
	}

	public static function createLogoutRequest($nameId, $issuer, $destination, $sessionIndex = '', $slo_binding_type = 'HttpRedirect'){

		$requestXmlStr='<?xml version="1.0" encoding="UTF-8"?>' .
						'<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="'. self::generateID() .
						'" IssueInstant="' . self::generateTimestamp() .
						'" Version="2.0" Destination="'. htmlspecialchars($destination) . '">
						<saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . '</saml:Issuer>
						<saml:NameID xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">'. $nameId[0] . '</saml:NameID>';
		if(!empty($sessionIndex)) {
			$requestXmlStr .= '<samlp:SessionIndex>' . $sessionIndex[0] . '</samlp:SessionIndex>';
		}
		$requestXmlStr .= '</samlp:LogoutRequest>';

		if(empty($slo_binding_type) || $slo_binding_type == 'HttpRedirect') {
			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			$urlEncoded = urlencode($base64EncodedStr);
			$requestXmlStr = $urlEncoded;
		}
		return $requestXmlStr;
	}

	public static function createLogoutResponse( $inResponseTo, $issuer, $destination, $slo_binding_type = 'HttpRedirect'){

		$requestXmlStr='<?xml version="1.0" encoding="UTF-8"?>' .
						'<samlp:LogoutResponse xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ' .
								'ID="' . self::generateID() . '" ' .
								'Version="2.0" IssueInstant="' . self::generateTimestamp() . '" ' .
								'Destination="' . $destination . '" ' .
								'InResponseTo="' . $inResponseTo . '">' .
							'<saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . '</saml:Issuer>' .
							'<samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status></samlp:LogoutResponse>';

		if(empty($slo_binding_type) || $slo_binding_type == 'HttpRedirect') {
			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			$urlEncoded = urlencode($base64EncodedStr);
			$requestXmlStr = $urlEncoded;
		}
		return $requestXmlStr;
	}

	public static function generateTimestamp($instant = NULL) {
		if($instant === NULL) {
			$instant = time();
		}
		return gmdate('Y-m-d\TH:i:s\Z', $instant);
	}

	public static function xpQuery(DOMNode $node, $query)
    {

        static $xpCache = NULL;

        if ($node instanceof DOMDocument) {
            $doc = $node;
        } else {
            $doc = $node->ownerDocument;
        }

        if ($xpCache === NULL || !$xpCache->document->isSameNode($doc)) {
            $xpCache = new DOMXPath($doc);
            $xpCache->registerNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xpCache->registerNamespace('saml_protocol', 'urn:oasis:names:tc:SAML:2.0:protocol');
            $xpCache->registerNamespace('saml_assertion', 'urn:oasis:names:tc:SAML:2.0:assertion');
            $xpCache->registerNamespace('saml_metadata', 'urn:oasis:names:tc:SAML:2.0:metadata');
            $xpCache->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
            $xpCache->registerNamespace('xenc', 'http://www.w3.org/2001/04/xmlenc#');
        }

        $results = $xpCache->query($query, $node);
        $ret = array();
        for ($i = 0; $i < $results->length; $i++) {
            $ret[$i] = $results->item($i);
        }

		return $ret;
    }

	public static function parseNameId(DOMElement $xml)
    {
        $ret = array('Value' => trim($xml->textContent));

        foreach (array('NameQualifier', 'SPNameQualifier', 'Format') as $attr) {
            if ($xml->hasAttribute($attr)) {
                $ret[$attr] = $xml->getAttribute($attr);
            }
        }

        return $ret;
    }

	public static function xsDateTimeToTimestamp($time)
    {
        $matches = array();

        // We use a very strict regex to parse the timestamp.
        $regex = '/^(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)T(\\d\\d):(\\d\\d):(\\d\\d)(?:\\.\\d+)?Z$/D';
        if (preg_match($regex, $time, $matches) == 0) {
            echo sprintf("nvalid SAML2 timestamp passed to xsDateTimeToTimestamp: ".$time);
            exit;
        }

        // Extract the different components of the time from the  matches in the regex.
        // intval will ignore leading zeroes in the string.
        $year   = intval($matches[1]);
        $month  = intval($matches[2]);
        $day    = intval($matches[3]);
        $hour   = intval($matches[4]);
        $minute = intval($matches[5]);
        $second = intval($matches[6]);

        // We use gmmktime because the timestamp will always be given
        //in UTC.
        $ts = gmmktime($hour, $minute, $second, $month, $day, $year);

        return $ts;
    }

	public static function extractStrings(DOMElement $parent, $namespaceURI, $localName)
    {


        $ret = array();
        for ($node = $parent->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node->namespaceURI !== $namespaceURI || $node->localName !== $localName) {
                continue;
            }
            $ret[] = trim($node->textContent);
        }

        return $ret;
    }

	public static function validateElement(DOMElement $root)
    {
    	//$data = $root->ownerDocument->saveXML($root);
    	//echo htmlspecialchars($data);

        /* Create an XML security object. */
        $objXMLSecDSig = new XMLSecurityDSig();

        /* Both SAML messages and SAML assertions use the 'ID' attribute. */
        $objXMLSecDSig->idKeys[] = 'ID';


        /* Locate the XMLDSig Signature element to be used. */
        $signatureElement = self::xpQuery($root, './ds:Signature');
        //print_r($signatureElement);

        if (count($signatureElement) === 0) {
            /* We don't have a signature element to validate. */
            return FALSE;
        } elseif (count($signatureElement) > 1) {
        	echo sprintf("XMLSec: more than one signature element in root.");
        	exit;
        }/*  elseif ((in_array('Response', $signatureElement) && $ocurrence['Response'] > 1) ||
            (in_array('Assertion', $signatureElement) && $ocurrence['Assertion'] > 1) ||
            !in_array('Response', $signatureElement) && !in_array('Assertion', $signatureElement)
        ) {
            return false;
        } */

        $signatureElement = $signatureElement[0];
        $objXMLSecDSig->sigNode = $signatureElement;

        /* Canonicalize the XMLDSig SignedInfo element in the message. */
        $objXMLSecDSig->canonicalizeSignedInfo();

       /* Validate referenced xml nodes. */
        if (!$objXMLSecDSig->validateReference()) {
        	echo sprintf("XMLsec: digest validation failed");
        	exit;
        }

		/* Check that $root is one of the signed nodes. */
        $rootSigned = FALSE;
        /** @var DOMNode $signedNode */
        foreach ($objXMLSecDSig->getValidatedNodes() as $signedNode) {
            if ($signedNode->isSameNode($root)) {
                $rootSigned = TRUE;
                break;
            } elseif ($root->parentNode instanceof DOMDocument && $signedNode->isSameNode($root->ownerDocument)) {
                /* $root is the root element of a signed document. */
                $rootSigned = TRUE;
                break;
            }
        }

		if (!$rootSigned) {
			echo sprintf("XMLSec: The root element is not signed.");
			exit;
        }

        /* Now we extract all available X509 certificates in the signature element. */
        $certificates = array();
        foreach (self::xpQuery($signatureElement, './ds:KeyInfo/ds:X509Data/ds:X509Certificate') as $certNode) {
            $certData = trim($certNode->textContent);
            $certData = str_replace(array("\r", "\n", "\t", ' '), '', $certData);
            $certificates[] = $certData;
			//echo "CertDate: " . $certData . "<br />";
        }

        $ret = array(
            'Signature' => $objXMLSecDSig,
            'Certificates' => $certificates,
            );

		//echo "Signature validated";


        return $ret;
    }



	public static function validateSignature(array $info, XMLSecurityKey $key)
    {


        /** @var XMLSecurityDSig $objXMLSecDSig */
        $objXMLSecDSig = $info['Signature'];

        $sigMethod = self::xpQuery($objXMLSecDSig->sigNode, './ds:SignedInfo/ds:SignatureMethod');
        if (empty($sigMethod)) {
            echo sprintf('Missing SignatureMethod element');
            exit();
        }
        $sigMethod = $sigMethod[0];
        if (!$sigMethod->hasAttribute('Algorithm')) {
            echo sprintf('Missing Algorithm-attribute on SignatureMethod element.');
            exit;
        }
        $algo = $sigMethod->getAttribute('Algorithm');

        if ($key->type === XMLSecurityKey::RSA_SHA1 && $algo !== $key->type) {
            $key = self::castKey($key, $algo);
        }

        /* Check the signature. */
        if (! $objXMLSecDSig->verify($key)) {
        	echo sprintf('Unable to validate Signature');
        	exit;
        }
    }

    public static function castKey(XMLSecurityKey $key, $algorithm, $type = 'public')
    {

    	// do nothing if algorithm is already the type of the key
    	if ($key->type === $algorithm) {
    		return $key;
    	}

    	$keyInfo = openssl_pkey_get_details($key->key);
    	if ($keyInfo === FALSE) {
    		echo sprintf('Unable to get key details from XMLSecurityKey.');
    		exit;
    	}
    	if (!isset($keyInfo['key'])) {
    		echo sprintf('Missing key in public key details.');
    		exit;
    	}

    	$newKey = new XMLSecurityKey($algorithm, array('type'=>$type));
    	$newKey->loadKey($keyInfo['key']);

    	return $newKey;
    }

	public static function processResponse($currentURL, $certFingerprint, $signatureData,
		SAML2SPResponse $response, $certNumber, $relayState) {
        
		$assertion = current($response->getAssertions());

		$notBefore = $assertion->getNotBefore();
		if ($notBefore !== NULL && $notBefore > time() + 60) {
			exit('Received an assertion that is valid in the future. Check clock synchronization on IdP and SP.');
		}

		$notOnOrAfter = $assertion->getNotOnOrAfter();
		if ($notOnOrAfter !== NULL && $notOnOrAfter <= time() - 60) {
			exit('Received an assertion that has expired. Check clock synchronization on IdP and SP.');
		}

		$sessionNotOnOrAfter = $assertion->getSessionNotOnOrAfter();
		if ($sessionNotOnOrAfter !== NULL && $sessionNotOnOrAfter <= time() - 60) {
			exit('Received an assertion with a session that has expired. Check clock synchronization on IdP and SP.');
		}

		/* Validate Response-element destination. */
		$msgDestination = $response->getDestination();
		if(substr($msgDestination, -1) == '/') {
			$msgDestination = substr($msgDestination, 0, -1);
		}
		if(substr($currentURL, -1) == '/') {
			$currentURL = substr($currentURL, 0, -1);
		}

		if ($msgDestination !== NULL && $msgDestination !== $currentURL) {
			echo 'Destination in response doesn\'t match the current URL. Destination is "' .
				htmlspecialchars($msgDestination) . '", current URL is "' . htmlspecialchars($currentURL) . '".';
			exit;
		}

		$responseSigned = self::checkSign($certFingerprint, $signatureData, $certNumber, $relayState);

		/* Returning boolean $responseSigned */
		return $responseSigned;
	}

	public static function checkSign($certFingerprint, $signatureData, $certNumber, $relayState) {
		$certificates = $signatureData['Certificates'];

		if (count($certificates) === 0) {
			$storedCerts = maybe_unserialize(get_option('saml_x509_certificate'));
			$pemCert = $storedCerts[$certNumber];
		}else{
			$fpArray = array();
			$fpArray[] = $certFingerprint;
			$pemCert = self::findCertificate($fpArray, $certificates, $relayState);
            if($pemCert==false)
                return false;
		}

		$lastException = NULL;

		$key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'public'));
		$key->loadKey($pemCert);

		try {
			/*
			 * Make sure that we have a valid signature
			 */
			self::validateSignature($signatureData, $key);
			return TRUE;
		} catch (Exception $e) {
			$lastException = $e;
		}


		/* We were unable to validate the signature with any of our keys. */
		if ($lastException !== NULL) {
			throw $lastException;
		} else {
			return FALSE;
		}

	}

	public static function validateIssuerAndAudience($samlResponse, $spEntityId, $issuerToValidateAgainst, $relayState) {
		$issuer = current($samlResponse->getAssertions())->getIssuer();
		$assertion = current($samlResponse->getAssertions());
		$audiences = $assertion->getValidAudiences();
		if(strcmp($issuerToValidateAgainst, $issuer) === 0) {
			if(!empty($audiences)) {
				if(in_array($spEntityId, $audiences, TRUE)) {
					return TRUE;
				} else {
					if($relayState=='testValidate' or $relayState =='testNewCertificate'){
						$Error_message=mo_options_error_constants::Error_invalid_audience;
					    $Cause_message = mo_options_error_constants::Cause_invalid_audience;
                    ob_end_clean();

                    echo '<div style="font-family:Calibri;padding:0 3%;">';
                    echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                    <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Invalid Audience URI.</p>
                    <p>Please contact your administrator and report the following error:</p>
                    <p><strong>Possible Cause: </strong>The value of \'Audience URI\' field on Identity Provider\'s side is incorrect</p>
                    <p>Expected one of the Audiences to be: '.$spEntityId.'<p>
					<p><strong>Solution:</strong></p>
					<ol>
						<li>Copy the Expected Audience URI from above and paste it in the Audience URI field at Identity Provider side.</li>
					</ol>
					</div>
                    <div style="margin:3%;display:block;text-align:center;">
                    <div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';   exit;
					mo_saml_download_logs($Error_message,$Cause_message);
				}
                else
                {
                    wp_die("We could not sign you in. Please contact your Administrator","Error :Invalid Audience URI");
                }
				}
			}
		} else {
			if($relayState=='testValidate' or $relayState =='testNewCertificate'){
				ob_end_clean();
			
			$Error_message=mo_options_error_constants::Error_issuer_not_verfied;
	        $Cause_message = mo_options_error_constants::Cause_issuer_not_verfied;
			 echo '<div style="font-family:Calibri;padding:0 3%;">';
			 echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
			 <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Issuer cannot be verified.</p>
			 <p>Please contact your administrator and report the following error:</p>
			 <p><strong>Possible Cause: </strong>IdP Entity ID configured in the plugin is incorrect</p>
			 <p><strong>Entity ID in SAML Response: </strong>'.$issuer.'<p>
			 <p><strong>Entity ID configured in the plugin: </strong>'.$issuerToValidateAgainst.'</p>
			 <p><strong>Solution:</strong></p>
				<ol>
					<li>Copy the Entity ID of SAML Response from above and paste it in Entity ID or Issuer field under Service Provider Setup tab.</li>
				</ol>
			 </div>
			 <div style="margin:3%;display:block;text-align:center;">
			 <div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';
			 mo_saml_download_logs($Error_message,$Cause_message);
			 exit;
		}
         else
                {
                    wp_die("We could not sign you in. Please contact your Administrator","Error :Issuer cannot be verified");
                }
		}
	}

	private static function findCertificate(array $certFingerprints, array $certificates, $relayState) {

		$candidates = array();

		foreach ($certificates as $cert) {
			$fp = strtolower(sha1(base64_decode($cert)));
			if (!in_array($fp, $certFingerprints, TRUE)) {
				$candidates[] = $fp;
				return false;
			}

			/* We have found a matching fingerprint. */
			$pem = "-----BEGIN CERTIFICATE-----\n" .
				chunk_split($cert, 64) .
				"-----END CERTIFICATE-----\n";

			return $pem;
		}

		if($relayState=='testValidate' or $relayState =='testNewCertificate'){
			$pem = "-----BEGIN CERTIFICATE-----<br>" .
					chunk_split($cert, 64) .
					"<br>-----END CERTIFICATE-----";

			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
			<div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Unable to find a certificate matching the configured fingerprint.</p>
			<p>Please contact your administrator and report the following error:</p>
			<p><strong>Possible Cause: </strong>\'X.509 Certificate\' field in plugin does not match the certificate found in SAML Response.</p>
			<p><strong>Certificate found in SAML Response: </strong><br><br>'.$pem.'</p>
					</div>
					<div style="margin:3%;display:block;text-align:center;">
					<div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';

					exit;
            }
            else{

                  wp_die("We could not sign you in. Please contact your Administrator","Error :Certificate not found");
            }
	}

	    /**
     * Decrypt an encrypted element.
     *
     * This is an internal helper function.
     *
     * @param  DOMElement     $encryptedData The encrypted data.
     * @param  XMLSecurityKey $inputKey      The decryption key.
     * @param  array          &$blacklist    Blacklisted decryption algorithms.
     * @return DOMElement     The decrypted element.
     * @throws Exception
     */
    private static function doDecryptElement(DOMElement $encryptedData, XMLSecurityKey $inputKey, array &$blacklist)
    {
        $enc = new XMLSecEnc();
        $enc->setNode($encryptedData);

        $enc->type = $encryptedData->getAttribute("Type");
        $symmetricKey = $enc->locateKey($encryptedData);
        if (!$symmetricKey) {
        	echo sprintf('Could not locate key algorithm in encrypted data.');
        	exit;
        }

        $symmetricKeyInfo = $enc->locateKeyInfo($symmetricKey);
        if (!$symmetricKeyInfo) {
			echo sprintf('Could not locate <dsig:KeyInfo> for the encrypted key.');
			exit;
        }
        $inputKeyAlgo = $inputKey->getAlgorith();
        if ($symmetricKeyInfo->isEncrypted) {
            $symKeyInfoAlgo = $symmetricKeyInfo->getAlgorith();
            if (in_array($symKeyInfoAlgo, $blacklist, TRUE)) {
                echo esc_html(sprintf('Algorithm disabled: ' . var_export($symKeyInfoAlgo, TRUE)));
                exit;
            }
            if ($symKeyInfoAlgo === XMLSecurityKey::RSA_OAEP_MGF1P && $inputKeyAlgo === XMLSecurityKey::RSA_1_5) {
                /*
                 * The RSA key formats are equal, so loading an RSA_1_5 key
                 * into an RSA_OAEP_MGF1P key can be done without problems.
                 * We therefore pretend that the input key is an
                 * RSA_OAEP_MGF1P key.
                 */
                $inputKeyAlgo = XMLSecurityKey::RSA_OAEP_MGF1P;
            }
            /* Make sure that the input key format is the same as the one used to encrypt the key. */
            if ($inputKeyAlgo !== $symKeyInfoAlgo) {
                echo esc_html(sprintf( 'Algorithm mismatch between input key and key used to encrypt ' .
                    ' the symmetric key for the message. Key was: ' .
                    var_export($inputKeyAlgo, TRUE) . '; message was: ' .
                    var_export($symKeyInfoAlgo, TRUE)));
                exit;
            }
            /** @var XMLSecEnc $encKey */
            $encKey = $symmetricKeyInfo->encryptedCtx;
            $symmetricKeyInfo->key = $inputKey->key;
            $keySize = $symmetricKey->getSymmetricKeySize();
            if ($keySize === NULL) {
                /* To protect against "key oracle" attacks, we need to be able to create a
                 * symmetric key, and for that we need to know the key size.
                 */
				echo esc_html(sprintf('Unknown key size for encryption algorithm: ' . var_export($symmetricKey->type, TRUE)));
				exit;
            }
            try {
                $key = $encKey->decryptKey($symmetricKeyInfo);
                if (strlen($key) != $keySize) {
                	echo esc_html(sprintf('Unexpected key size (' . strlen($key) * 8 . 'bits) for encryption algorithm: ' .
                        var_export($symmetricKey->type, TRUE)));
                	exit;
                }
            } catch (Exception $e) {
                /* We failed to decrypt this key. Log it, and substitute a "random" key. */

                /* Create a replacement key, so that it looks like we fail in the same way as if the key was correctly padded. */
                /* We base the symmetric key on the encrypted key and private key, so that we always behave the
                 * same way for a given input key.
                 */
                $encryptedKey = $encKey->getCipherValue();
                $pkey = openssl_pkey_get_details($symmetricKeyInfo->key);
                $pkey = sha1(serialize($pkey), TRUE);
                $key = sha1($encryptedKey . $pkey, TRUE);
                /* Make sure that the key has the correct length. */
                if (strlen($key) > $keySize) {
                    $key = substr($key, 0, $keySize);
                } elseif (strlen($key) < $keySize) {
                    $key = str_pad($key, $keySize);
                }
            }
            $symmetricKey->loadkey($key);
        } else {
            $symKeyAlgo = $symmetricKey->getAlgorith();
            /* Make sure that the input key has the correct format. */
            if ($inputKeyAlgo !== $symKeyAlgo) {
            	echo esc_html(sprintf( 'Algorithm mismatch between input key and key in message. ' .
                    'Key was: ' . var_export($inputKeyAlgo, TRUE) . '; message was: ' .
                    var_export($symKeyAlgo, TRUE)));
            	exit;
            }
            $symmetricKey = $inputKey;
        }
        $algorithm = $symmetricKey->getAlgorith();
        if (in_array($algorithm, $blacklist, TRUE)) {
            echo esc_html(sprintf('Algorithm disabled: ' . var_export($algorithm, TRUE)));
            exit;
        }
        /** @var string $decrypted */
        $decrypted = $enc->decryptNode($symmetricKey, FALSE);
        /*
         * This is a workaround for the case where only a subset of the XML
         * tree was serialized for encryption. In that case, we may miss the
         * namespaces needed to parse the XML.
         */
        $xml = '<root xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" '.
                     'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
            $decrypted .
            '</root>';
        $newDoc = new DOMDocument();
        if (!@$newDoc->loadXML($xml)) {
        	//echo sprintf('Failed to parse decrypted XML. Maybe the wrong sharedkey was used?');
        	throw new Exception('Failed to parse decrypted XML. Maybe the wrong sharedkey was used?');
        }
        $decryptedElement = $newDoc->firstChild->firstChild;
        if ($decryptedElement === NULL) {
        	echo sprintf('Missing encrypted element.');
        	throw new Exception('Missing encrypted element.');
        }

        if (!($decryptedElement instanceof DOMElement)) {
        	echo sprintf('Decrypted element was not actually a DOMElement.');
        }

        return $decryptedElement;
    }



    /**
     * Decrypt an encrypted element.
     *
     * @param  DOMElement     $encryptedData The encrypted data.
     * @param  XMLSecurityKey $inputKey      The decryption key.
     * @param  array          $blacklist     Blacklisted decryption algorithms.
     * @return DOMElement     The decrypted element.
     * @throws Exception
     */
    public static function decryptElement(DOMElement $encryptedData, XMLSecurityKey $inputKey, array $blacklist = array(), XMLSecurityKey $alternateKey = NULL)
    {

        try {
            return self::doDecryptElement($encryptedData, $inputKey, $blacklist);
        } catch (Exception $e) {

        	try {

        		//return self::doDecryptElement($encryptedData, $alternateKey, $blacklist);
        	} catch(Exception $t) {

        	}
        	/*
        	 * Something went wrong during decryption, but for security
        	 * reasons we cannot tell the user what failed.
        	 */

			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                    <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Invalid Audience URI.</p>
                    <p>Please contact your administrator and report the following error:</p>
                    <p><strong>Possible Cause: </strong>Incorrect certificate added on the Identity Provider for Encryption</p>
					<p><strong>Solution:</strong> Please check if the certificate added in Identity Provider is same as the certificate provided in the Plugin</p>
					</div>
                    <div style="margin:3%;display:block;text-align:center;">
                    <div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';   exit;

			exit;
        }
    }


	public static function get_mapped_groups($saml_params, $saml_groups)
	{
			$groups = array();

		if (!empty($saml_groups)) {
			$saml_mapped_groups = array();
			$i=1;
			while ($i < 10) {
				$saml_mapped_groups_value = $saml_params->get('group'.$i.'_map');

				$saml_mapped_groups[$i] = explode(';', $saml_mapped_groups_value);
				$i++;
			}
		}

		foreach ($saml_groups as $saml_group) {
			if (!empty($saml_group)) {
				$i = 0;
				$found = false;

				while ($i < 9 && !$found) {
					if (!empty($saml_mapped_groups[$i]) && in_array($saml_group, $saml_mapped_groups[$i], TRUE)) {
						$groups[] = $saml_params->get('group'.$i);
						$found = true;
					}
					$i++;
				}
			}
		}

		return array_unique($groups);
	}


	public static function getEncryptionAlgorithm($method){
		switch($method){
			case 'http://www.w3.org/2001/04/xmlenc#tripledes-cbc':
				return XMLSecurityKey::TRIPLEDES_CBC;
				break;

			case 'http://www.w3.org/2001/04/xmlenc#aes128-cbc':
				return XMLSecurityKey::AES128_CBC;

			case 'http://www.w3.org/2001/04/xmlenc#aes192-cbc':
				return XMLSecurityKey::AES192_CBC;
				break;

			case 'http://www.w3.org/2001/04/xmlenc#aes256-cbc':
				return XMLSecurityKey::AES256_CBC;
				break;

			case 'http://www.w3.org/2001/04/xmlenc#rsa-1_5':
				return XMLSecurityKey::RSA_1_5;
				break;

			case 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p':
				return XMLSecurityKey::RSA_OAEP_MGF1P;
				break;

			case 'http://www.w3.org/2000/09/xmldsig#dsa-sha1':
				return XMLSecurityKey::DSA_SHA1;
				break;

			case 'http://www.w3.org/2000/09/xmldsig#rsa-sha1':
				return XMLSecurityKey::RSA_SHA1;
				break;

			case 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256':
				return XMLSecurityKey::RSA_SHA256;
				break;

			case 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384':
				return XMLSecurityKey::RSA_SHA384;
				break;

			case 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512':
				return XMLSecurityKey::RSA_SHA512;
				break;

			default:
				echo esc_html(sprintf('Invalid Encryption Method: '.$method));
				exit;
				break;
		}
	}

	/**
     * Insert a Signature-node.
     *
     * @param XMLSecurityKey $key           The key we should use to sign the message.
     * @param array          $certificates  The certificates we should add to the signature node.
     * @param DOMElement     $root          The XML node we should sign.
     * @param DOMNode        $insertBefore  The XML element we should insert the signature element before.
     */
    public static function insertSignature(
        XMLSecurityKey $key,
        array $certificates,
        DOMElement $root,
        DOMNode $insertBefore = NULL
    ) {
        $objXMLSecDSig = new XMLSecurityDSig();
        $objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        switch ($key->type) {
            case XMLSecurityKey::RSA_SHA256:
                $type = XMLSecurityDSig::SHA256;
                break;
            case XMLSecurityKey::RSA_SHA384:
                $type = XMLSecurityDSig::SHA384;
                break;
            case XMLSecurityKey::RSA_SHA512:
                $type = XMLSecurityDSig::SHA512;
                break;
            default:
                $type = XMLSecurityDSig::SHA1;
        }

        $objXMLSecDSig->addReferenceList(
            array($root),
            $type,
            array('http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N),
            array('id_name' => 'ID', 'overwrite' => FALSE)
        );

        $objXMLSecDSig->sign($key);

        foreach ($certificates as $certificate) {
            $objXMLSecDSig->add509Cert($certificate, TRUE);
        }

        $objXMLSecDSig->insertSignature($root, $insertBefore);
    }
	public static function getRemainingDaysOfCurrentCertificate(){
		$certificate =  get_option('mo_saml_current_cert');
		$parsed_certificate =  openssl_x509_parse($certificate);
		$validTo_time = $parsed_certificate['validTo_time_t'];
		$difference  = $validTo_time - time();
		return round($difference / (60 * 60 * 24));
	}

	public static function getExpiryDateOfCurrentCertificate(){
		$certificate =  get_option('mo_saml_current_cert');
		$parsed_certificate =  openssl_x509_parse($certificate);
		return $parsed_certificate['validTo_time_t'];
	}

	public static function signXML($xml, $insertBeforeTagName = "", $new_cert = false) {
		$param =array( 'type' => 'private');
		$key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, $param);
        if($new_cert){
			$privateKeyPath = file_get_contents(plugin_dir_path(__FILE__) . 'resources' . DIRECTORY_SEPARATOR . 'miniorange_sp_2020_priv.key');
			$publicCertificate = file_get_contents(plugin_dir_path(__FILE__) . 'resources' . DIRECTORY_SEPARATOR . 'miniorange_sp_2020.crt');
		} else {
			$privateKeyPath = get_option( 'mo_saml_current_cert_private_key' );
			$publicCertificate = get_option( 'mo_saml_current_cert' );
		}

		$key->loadKey( $privateKeyPath, FALSE);
		$document = new DOMDocument();
		$document->loadXML($xml);
		$element = $document->firstChild;
		if( !empty($insertBeforeTagName) ) {
			$domNode = $document->getElementsByTagName( $insertBeforeTagName )->item(0);
			self::insertSignature($key, array ( $publicCertificate ), $element, $domNode);
		} else {
			self::insertSignature($key, array ( $publicCertificate ), $element);
		}
		$requestXML = $element->ownerDocument->saveXML($element);
		$base64EncodedXML = base64_encode($requestXML);
		return $base64EncodedXML;
	}

	public static function postSAMLRequest($url, $samlRequestXML, $relayState, $idpName='') {
		$html = "<html><head><script src='https://code.jquery.com/jquery-1.11.3.min.js'></script><script type=\"text/javascript\">$(function(){document.forms['saml-request-form'].submit();});</script></head><body>Please wait...<form action=\"" . $url . "\" method=\"post\" id=\"saml-request-form\"><input type=\"hidden\" name=\"SAMLRequest\" value=\"" . $samlRequestXML . "\" /><input type=\"hidden\" name=\"RelayState\" value=\"" . htmlentities($relayState) . "\" />";
		if(!empty($idpName)){
			$html = "<input type=\"hidden\" name=\"userName\" value=\"" . htmlentities($idpName) . "\" />";
		}
		$html.= "</form></body></html>";
		echo $html;
		exit();
	}



	public static function postSAMLResponse($url, $samlResponseXML, $relayState) {
		echo "<html><head><script src='https://code.jquery.com/jquery-1.11.3.min.js'></script><script type=\"text/javascript\">$(function(){document.forms['saml-request-form'].submit();});</script></head><body>Please wait...<form action=\"" . $url . "\" method=\"post\" id=\"saml-request-form\"><input type=\"hidden\" name=\"SAMLResponse\" value=\"" . $samlResponseXML . "\" /><input type=\"hidden\" name=\"RelayState\" value=\"" . htmlentities($relayState) . "\" /></form></body></html>";
		exit();
	}


	public static function sanitize_certificate( $certificate ) {
		$certificate = trim($certificate);
		$certificate = preg_replace("/[\r\n]+/", "", $certificate);
		$certificate = str_replace( "-", "", $certificate );
		$certificate = str_replace( "BEGIN CERTIFICATE", "", $certificate );
		$certificate = str_replace( "END CERTIFICATE", "", $certificate );
		$certificate = str_replace( " ", "", $certificate );
		$certificate = chunk_split($certificate, 64, "\r\n");
		$certificate = "-----BEGIN CERTIFICATE-----\r\n" . $certificate . "-----END CERTIFICATE-----";
		return $certificate;
	}

	public static function desanitize_certificate( $certificate ) {
		$certificate = preg_replace("/[\r\n]+/", "", $certificate);
		$certificate = str_replace( "-----BEGIN CERTIFICATE-----", "", $certificate );
		$certificate = str_replace( "-----END CERTIFICATE-----", "", $certificate );
		$certificate = str_replace( " ", "", $certificate );
		return $certificate;
	}

	public static function mo_saml_wp_remote_call($url, $args = array(), $is_get=false){

		// Uncomment the following two lines while pointing to TEST
		// $array = array('sslverify' => false);
		// $args = array_merge($args, $array);

		if(!$is_get)
			$response = wp_remote_post($url, $args);
		else
			$response = wp_remote_get($url, $args);

		if(!is_wp_error($response)){			
			return $response['body'];
		} else {
            $show_message = new saml_mo_login();
            update_option('mo_saml_message', 'Unable to connect to the Internet. Please try again.');
            $show_message->mo_saml_show_error_message();
            return false;
        }
    }

	/**
	 * Chceks if a user is logged in or not, additional checks for guest login.
	 * @return bool true if user is logged in, false if not logged in. 
	 */
	public static function mo_saml_is_user_logged_in(){
		if(is_user_logged_in())
			return true;

		if(!empty(get_option('mo_enable_guest_login')) && get_option('mo_enable_guest_login') == 'true')
			if(!empty($_SESSION['mo_guest_login']['sessionIndex']) || !empty($_COOKIE['sessionIndex']))
				return true;

		return false;
	}

}
?>
