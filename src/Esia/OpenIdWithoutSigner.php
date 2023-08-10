<?php

namespace Esia;

use Esia\Http\GuzzleHttpClient;
use Esia\Signer\SignerInterface;
use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use Psr\Log\NullLogger;

/**
 * Class OpenIdWithoutSigner
 */
class OpenIdWithoutSigner extends OpenId
{
    public function __construct(
        ConfigInterface $config,
        ClientInterface $client = null,
        SignerInterface $signer = null,
    ) {
        $this->config = $config;
        $this->client = $client ?? new GuzzleHttpClient(new Client());
        $this->logger = new NullLogger();

        if ($signer) {
            $this->signer = $signer;
        }
    }
}
