<?php

namespace Esia\Signer;

use JsonException;

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

    /**
     * @param string $signingServerUrl
     * @param array $headers
     * @param string $method
     * @noinspection PhpMissingParentConstructorInspection
     */
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
     * @param string $message
     * @return string
     * @throws JsonException
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

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $sign = $content['result'] ?? '';

        return $sign;
    }
}
