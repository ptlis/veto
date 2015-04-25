<?php

/**
 * Veto.
 * PHP Microframework.
 *
 * @author brian ridley <ptlis@ptlis.net>
 * @copyright Damien Walsh 2013-2014
 * @version 0.1
 * @package veto
 */

namespace Veto\HTTP;

use League\Flysystem\MountManager;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    /**
     * @var MountManager
     */
    private $mountManager;

    /**
     * @var string
     */
    private $uploadedPath;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var string
     */
    private $clientFilename;

    /**
     * @var string
     */
    private $clientMediaType;


    /**
     * @param MountManager $mountManager
     * @param string $uploadedPath
     * @param int $size
     * @param int $errorCode
     * @param string $clientFilename
     * @param string $clientMediaType
     */
    public function __construct(
        MountManager $mountManager,
        $uploadedPath,
        $size,
        $errorCode,
        $clientFilename,
        $clientMediaType
    ) {
        $this->mountManager = $mountManager;
        $this->uploadedPath = $uploadedPath;
        $this->size = $size;
        $this->errorCode = $errorCode;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the move() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        $stream = $this->mountManager->readStream($this->getUploadedPath());

        return new MessageBody($stream);
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via move(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * use to ensure permissions and upload status are verified correctly.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $path Path to which to move the uploaded file.
     * @throws \InvalidArgumentException if the $path specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function move($path)
    {
        $this->mountManager->move(
            $this->getUploadedPath(),
            $path
        );
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->errorCode;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * Get the normalized local path
     *
     * @return string
     */
    private function getUploadedPath()
    {
        // TODO This almost certainly won't work on Windows
        return 'local://' . ltrim($this->uploadedPath, '/');
    }
}
