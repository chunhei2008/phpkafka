<?php

declare(strict_types=1);

namespace Longyan\Kafka\Socket;

use Longyan\Kafka\Config\CommonConfig;
use Longyan\Kafka\Exception\ConnectionException;
use Longyan\Kafka\Exception\SocketException;

class StreamSocket implements SocketInterface
{
    /**
     * read socket max length 5MB.
     *
     * @var int
     */
    public const READ_MAX_LENGTH = 5242880;

    /**
     * max write socket buffer.
     *
     * @var int
     */
    public const MAX_WRITE_BUFFER = 2048;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var \Longyan\Kafka\Config\CommonConfig|null
     */
    protected $config;

    /**
     * @var resource
     */
    protected $socket;

    public function __construct(string $host, int $port, ?CommonConfig $config = null)
    {
        $this->host = $host;
        $this->port = $port;
        if (null === $config) {
            $config = new CommonConfig();
        }
        $this->config = $config;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getConfig(): CommonConfig
    {
        return $this->config;
    }

    public function connect(): void
    {
        $uri = sprintf('tcp://%s:%s', $this->host, $this->port);
        $context = stream_context_create([]);
        $socket = stream_socket_client(
            $uri,
            $errno,
            $errstr,
            $this->config->getSendTimeout(),
            \STREAM_CLIENT_CONNECT,
            $context
        );

        if (!\is_resource($socket)) {
            throw new ConnectionException(sprintf('Could not connect to %s (%s [%d])', $uri, $errstr, $errno));
        }
        $this->socket = $socket;
    }

    public function close(): bool
    {
        if (\is_resource($this->socket)) {
            fclose($this->socket);
            $this->socket = null;

            return true;
        } else {
            return false;
        }
    }

    public function send(string $data): int
    {
        // fwrite to a socket may be partial, so loop until we
        // are done with the entire buffer
        $failedAttempts = 0;
        $bytesWritten = 0;

        $bytesToWrite = \strlen($data);

        while ($bytesWritten < $bytesToWrite) {
            // wait for stream to become available for writing
            $writable = $this->select([$this->socket], $this->config->getSendTimeout(), false);

            if (false === $writable) {
                throw new SocketException('Could not write ' . $bytesToWrite . ' bytes to stream');
            }

            if (0 === $writable) {
                $res = $this->getMetaData();
                if (!empty($res['timed_out'])) {
                    throw new SocketException('Timed out writing ' . $bytesToWrite . ' bytes to stream after writing ' . $bytesWritten . ' bytes');
                }

                throw new SocketException('Could not write ' . $bytesToWrite . ' bytes to stream');
            }

            if ($bytesToWrite - $bytesWritten > self::MAX_WRITE_BUFFER) {
                // write max buffer size
                $wrote = fwrite($this->socket, substr($data, $bytesWritten, self::MAX_WRITE_BUFFER));
            } else {
                // write remaining buffer bytes to stream
                $wrote = fwrite($this->socket, substr($data, $bytesWritten));
            }

            if (-1 === $wrote || false === $wrote) {
                throw new SocketException('Could not write ' . \strlen($data) . ' bytes to stream, completed writing only ' . $bytesWritten . ' bytes');
            }

            if (0 === $wrote) {
                // Increment the number of times we have failed
                ++$failedAttempts;

                if ($failedAttempts > $this->config->getMaxWriteAttempts()) {
                    throw new SocketException('After ' . $failedAttempts . ' attempts could not write ' . \strlen($data) . ' bytes to stream, completed writing only ' . $bytesWritten . ' bytes');
                }
            } else {
                // If we wrote something, reset our failed attempt counter
                $failedAttempts = 0;
            }

            $bytesWritten += $wrote;
        }

        return $bytesWritten;
    }

    /**
     * @return string
     */
    public function recv(int $length): string
    {
        if ($length > self::READ_MAX_LENGTH) {
            throw new SocketException(sprintf('Invalid length %d given, it should be lesser than or equals to %d', $length, self::READ_MAX_LENGTH));
        }

        $readable = $this->select([$this->socket], $this->config->getRecvTimeout());

        if (false === $readable) {
            $this->close();
            throw new SocketException(sprintf('Could not read %d bytes from stream (not readable)', $length));
        }

        if (0 === $readable) { // select timeout
            $res = $this->getMetaData();
            $this->close();

            if (!empty($res['timed_out'])) {
                throw new SocketException(sprintf('Timed out reading %d bytes from stream', $length));
            }

            throw new SocketException(sprintf('Could not read %d bytes from stream (not readable)', $length));
        }

        $remainingBytes = $length;
        $data = $chunk = '';

        while ($remainingBytes > 0) {
            $chunk = fread($this->socket, $remainingBytes);

            if (false === $chunk || 0 === \strlen($chunk)) {
                // Zero bytes because of EOF?
                if (feof($this->socket)) {
                    $this->close();
                    throw new SocketException(sprintf('Unexpected EOF while reading %d bytes from stream (no data)', $length));
                }
                // Otherwise wait for bytes
                $readable = $this->select([$this->socket], $this->config->getRecvTimeout());
                if (1 !== $readable) {
                    throw new SocketException(sprintf('Timed out while reading %d bytes from stream, %d bytes are still needed', $length, $remainingBytes));
                }

                continue; // attempt another read
            }

            $data .= $chunk;
            $remainingBytes -= \strlen($chunk);
        }

        return $data;
    }

    protected function select(array $sockets, float $timeout, bool $isRead = true): int
    {
        $null = null;
        $timeoutSec = (int) $timeout;
        if ($timeoutSec < 0) {
            $timeoutSec = null;
        }
        $timeoutUsec = max((int) (1000000 * ($timeout - $timeoutSec)), 0);

        if ($isRead) {
            return stream_select($sockets, $null, $null, $timeoutSec, $timeoutUsec);
        }

        return stream_select($null, $sockets, $null, $timeoutSec, $timeoutUsec);
    }

    protected function getMetaData(): array
    {
        return stream_get_meta_data($this->socket);
    }
}