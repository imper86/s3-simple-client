<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 22.09.18
 * Time: 11:19
 */

namespace Imper86\S3SimpleClient;


use Aws\S3\MultipartUploader;
use Aws\S3\S3UriParser;

class S3Client implements S3ClientInterface
{
    /**
     * @var \Aws\S3\S3Client
     */
    private $client;
    /**
     * @var S3UriParser
     */
    private $uriParser;
    /**
     * @var array
     */
    private $config;
    /**
     * @var \finfo
     */
    private $finfo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new \Aws\S3\S3Client($config);
        $this->uriParser = new S3UriParser();
        $this->finfo = new \finfo(FILEINFO_MIME_TYPE);
    }

    public function getBaseClient(): \Aws\S3\S3ClientInterface
    {
        return $this->client;
    }

    public function getDirectUrl(string $bucket, string $key): string
    {
        return $this->client->getObjectUrl($bucket, $key);
    }

    public function getPresignedUrl(string $buket, string $key, ?\DateInterval $expireInterval = null): string
    {
        if (null === $expireInterval) {
            $expireInterval = new \DateInterval('PT5H');
        }

        $expiresAt = new \DateTime();
        $expiresAt->add($expireInterval);

        $command = $this->client->getCommand('GetObject', ['Bucket' => $buket, 'Key' => $key]);
        $request = $this->client->createPresignedRequest($command, $expiresAt);

        return (string)$request->getUri();
    }

    public function upload(string $bucket, string $key, string $body, bool $public = false): void
    {
        $this->client->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'Body' => $body,
            'ContentType' => $this->finfo->buffer($body),
            'ACL' => $public ? 'public-read' : 'private',
        ]);
    }

    public function uploadMultipart(string $bucket, string $key, string $filePath): void
    {
        $uploader = new MultipartUploader($this->client, $filePath, ['Bucket' => $bucket, 'Key' => $key]);
        $uploader->upload();
    }

    public function download(string $bucket, string $key): string
    {
        $object = $this->client->getObject(['Bucket' => $bucket, 'Key' => $key]);

        return $object['Body']->getContents();
    }

    public function downloadByUrl(string $url): string
    {
        $parsed = $this->parseUrl($url);

        return $this->download($parsed['bucket'], $parsed['key']);
    }

    public function delete(string $bucket, string $key): void
    {
        $this->client->deleteObject(['Bucket' => $bucket, 'Key' => $key]);
    }

    public function deleteByUrl(string $url): void
    {
        $parsed = $this->parseUrl($url);

        $this->delete($parsed['bucket'], $parsed['key']);
    }

    public function objectExists(string $bucket, string $key): bool
    {
        return $this->client->doesObjectExist($bucket, $key);
    }

    public function objectExistsByUrl(string $url): bool
    {
        $parsed = $this->parseUrl($url);

        return $this->objectExists($parsed['bucket'], $parsed['key']);
    }

    private function parseUrl(string $url): array
    {
        return $this->uriParser->parse($url);
    }
}
