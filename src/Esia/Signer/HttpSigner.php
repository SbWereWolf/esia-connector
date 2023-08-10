<?php

namespace Esia\Signer;

use Esia\Signer\Exceptions\SignFailException;
use Exception;

class HttpSigner
    extends AbstractSignerPKCS7
    implements SignerInterface
{

    /**
     * @var string
     */
    private string $signingServerUrl;
    private string $headers = '';
    private string $method;

    public function __construct(
        string $signingServerUrl,
        array $headers,
        string $method,
    ) {
        $this->signingServerUrl = $signingServerUrl;
        $this->method = $method;

        foreach ($headers as $key => $val) {
            $this->headers .= "$key: $val" . PHP_EOL;
        }
    }

    /**
     * @throws Exception
     */
    public function sign(string $message): string
    {
        $data = json_encode(['text' => $message], JSON_THROW_ON_ERROR);

        $options = [
            'http' => [
                'header' => $this->headers,
                'method' => $this->method,
                'content' => $data,
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents(
            $this->signingServerUrl,
            false,
            $context
        );

        $content = json_decode(
            $result,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $sign = $content['result'] ?? '';

        return $sign;
    }
}
