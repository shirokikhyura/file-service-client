<?php

namespace ShirokikhYura\FileService;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class FileManager implements FileManagerInterface
{
    private ClientInterface $httpClient;

    private string $version;

    private string $externalUrl;

    /**
     * FileManager constructor.
     * @param ClientInterface $httpClient
     * @param string $version
     * @param string $externalUrl
     */
    public function __construct(ClientInterface $httpClient, string $version, string $externalUrl)
    {
        $this->httpClient = $httpClient;
        $this->version = $version;
        $this->externalUrl = $externalUrl;
    }

    /**
     * @inheritDoc
     * @throws FileManagerException
     * @return [
     *      "id" => "<id on file service>",
     *      "clientName" => "your_file_name.ext",
     *      "fullName" => "<id on file service>.ext",
     *      "publicLink" => <externalUri>/<id on file service>.ext
     * ]
     */
    public function uploadFromBase64(string $base64, string $fileName) : array
    {
        $temp = tmpfile();
        fwrite($temp, base64_decode($base64));
        fseek($temp, 0);
        $response = $this->makeRequest('post', 'upload', [
            RequestOptions::MULTIPART => [
                [
                    'name'     => 'file',
                    'contents' => $temp,
                    'filename' => $fileName
                ]
            ]
        ]);
        fclose($temp);

        if ($response->getStatusCode() != 200) {
            throw new FileManagerException("Wrong status code");
        }

        if (current($response->getHeader('Content-Type')) != 'application/json') {
            throw new FileManagerException("Wrong content");
        }

        try {
            $body = json_decode(
                $response->getBody()->getContents(),
                JSON_OBJECT_AS_ARRAY,
                JSON_THROW_ON_ERROR
            );

            return array_merge($body, [
                'publicLink' => $this->getPublicLink($body['fullName'])
            ]);
        } catch (Throwable $ex) {
            throw new FileManagerException("Wrong body");
        }
    }

    /**
     * @inheritDoc
     * @throws FileManagerException
     */
    public function download(string $fileId) : array
    {
        $uri = sprintf('download/%s', $fileId);
        $response = $this->makeRequest('get', $uri);

        if ($response->getStatusCode() != 200) {
            throw new FileManagerException("Wrong status code");
        }

        if (current($response->getHeader('Content-Type')) == 'application/json') {
            throw new FileManagerException("Wrong content");
        }

        return [
            'name' => current($response->getHeader('X-FS-name')),
            'original' => current($response->getHeader('X-FS-client-name')),
            'content' => $response->getBody()->getContents()
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPublicLink(string $fullName) : string
    {
        return sprintf('%s/%s', $this->externalUrl, $fullName);
    }

    /**
     * @throws FileManagerException
     */
    private function makeRequest(string $method, string $path, array $options = []) : ResponseInterface
    {
        try {
            return $this->httpClient->request(
                $method,
                sprintf('%s/%s', $this->version, $path),
                $options
            );
        } catch (GuzzleException $e) {
            throw new FileManagerException($e->getMessage(), $e->getCode(), $e);
        }
    }
}