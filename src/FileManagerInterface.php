<?php

namespace ShirokikhYura\FileService;

interface FileManagerInterface
{
    /**
     * Upload from base64.
     *
     * @param string $base64
     * @param string $fileName
     */
    public function uploadFromBase64(string $base64, string $fileName) : array;

    /**
     * Download.
     *
     * @param string $fileId
     * @return mixed
     */
    public function download(string $fileId): array;

    /**
     * Make public link based on "externalUrl".
     *
     * @param string $fullName
     * @return string
     */
    public function getPublicLink(string $fullName): string;
}