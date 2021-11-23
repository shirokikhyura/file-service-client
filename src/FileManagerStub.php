<?php

namespace ShirokikhYura\FileService;

class FileManagerStub implements FileManagerInterface
{
    public function uploadFromBase64(string $base64, string $fileName) : array
    {
        return [
            'id' => 'test-file',
            'name' => 'test-file.test',
            'clientName' => 'client-test-file.test',
            'publicLink' => 'http://test.test/test-file.test'
        ];
    }

    public function download(string $fileId) : array
    {
        return [
            'name' => 'test',
            'original' => 'test.test',
            'content' => 'test content'
        ];
    }

    public function getPublicLink(string $fullName) : string
    {
        return 'http://test.test/test-file.test';
    }
}