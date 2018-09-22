<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 22.09.18
 * Time: 11:10
 */

namespace Imper86\S3SimpleClient;


interface S3ClientInterface
{
    public function getDirectUrl(string $bucket, string $key): string;

    public function getPresignedUrl(string $buket, string $key, ?\DateInterval $expireInterval = null): string;

    public function upload(string $bucket, string $key, string $body, bool $public = false): void;

    public function uploadMultipart(string $bucket, string $key, string $filePath): void;

    public function download(string $bucket, string $key): string;

    public function downloadByUrl(string $url): string;

    public function delete(string $bucket, string $key): void;

    public function deleteByUrl(string $url): void;

    public function objectExists(string $bucket, string $key): bool;

    public function objectExistsByUrl(string $url): bool;
}
