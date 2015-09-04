<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Validator\Constraints;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @deprecated Deprecated since version 2.2.
 */
class RecaptchaValidator extends ConstraintValidator
{
    const RECAPTCHA_VERIFY_SERVER = 'www.google.com';

    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    protected $privateKey;
    protected $enable;

    public function __construct(RequestStack $requestStack, $privateKey, $enable)
    {
        trigger_error('RecaptchaValidator is deprecated since 2.2 version.', E_USER_DEPRECATED);

        $this->requestStack = $requestStack;
        $this->privateKey = $privateKey;
        $this->enable = $enable;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        // if recaptcha is disabled, always valid
        if (!$this->enable) {
            return;
        }

        // define variable for recaptcha check answer
        if (empty($this->privateKey)) {
            throw new ValidatorException('Recaptcha: Public and private keys are required');
        }

        $request = $this->requestStack->getMasterRequest();
        $remoteIp = $request->server->get('REMOTE_ADDR');
        $challenge = $request->request->get('recaptcha_challenge_field');
        $response = $request->request->get('recaptcha_response_field');

        if (!$challenge && !$response) {
            $this->context->addViolation($constraint->message, array('{{ value }}' => $value));

            return;
        }

        if (!$this->checkAnswer($this->privateKey, $remoteIp, $challenge, $response)) {
            $this->context->addViolation($constraint->message, array('{{ value }}' => $value));

            return;
        }

        return;
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct
     *
     * @param string $privateKey
     * @param string $remoteIp
     * @param string $challenge
     * @param string $response
     * @param array $extraParams an array of extra variables to post to the server
     *
     * @return ReCaptchaResponse
     */
    private function checkAnswer($privateKey, $remoteIp, $challenge, $response, $extraParams = array())
    {
        if (empty($remoteIp)) {
            throw new ValidatorException('For security reasons, you must pass the remote ip to reCAPTCHA');
        }

        // discard spam submissions
        if (empty($challenge) || empty($response)) {
            return false;
        }

        $response = $this->httpPost(
            self::RECAPTCHA_VERIFY_SERVER,
            '/recaptcha/api/verify',
            array(
                'privatekey' => $privateKey,
                'remoteip' => $remoteIp,
                'challenge' => $challenge,
                'response' => $response
            ) + $extraParams
        );

        $answers = explode("\n", $response [1]);

        if (trim($answers[0]) == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server
     *
     * @param string $host
     * @param string $path
     * @param array $data
     * @param int port
     *
     * @return array response
     */
    private function httpPost($host, $path, $data, $port = 80)
    {
        $req = $this->getQSEncode($data);

        $httpRequest = "POST $path HTTP/1.0\r\n";
        $httpRequest .= "Host: $host\r\n";
        $httpRequest .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $httpRequest .= "Content-Length: " . strlen($req) . "\r\n";
        $httpRequest .= "User-Agent: reCAPTCHA/PHP\r\n";
        $httpRequest .= "\r\n";
        $httpRequest .= $req;

        $response = null;
        if (!$fs = fsockopen($host, $port, $errno, $errstr, 10)) {
            throw new ValidatorException('Could not open socket');
        }

        fwrite($fs, $httpRequest);

        while (!feof($fs)) {
            $response .= fgets($fs, 1160); // one TCP-IP packet
        }

        fclose($fs);

        $response = explode("\r\n\r\n", $response, 2);

        return $response;
    }

    /**
     * Encodes the given data into a query string format
     *
     * @param $data - array of string elements to be encoded
     *
     * @return string - encoded request
     */
    private function getQSEncode($data)
    {
        $req = null;
        foreach ($data as $key => $value) {
            $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
        }

        // cut the last '&'
        $req = substr($req, 0, strlen($req) - 1);

        return $req;
    }
}
