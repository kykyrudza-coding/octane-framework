<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

use Horizon\Contracts\Console\Output\ConsoleOutputContract;

final class ConsoleOutput implements ConsoleOutputContract
{
    public function __construct(
        private readonly OutputFormatter $formatter,
    ) {
    }

    public function write(string $message): void
    {
        fwrite(
            STDOUT,
            $this->formatter->format($message),
        );
    }

    public function line(string $message = ''): void
    {
        $this->write($message.PHP_EOL);
    }

    public function error(string $message): void
    {
        fwrite(
            STDERR,
            $this->formatter->format(
                "<error>{$message}</error>",
            ).PHP_EOL,
        );
    }

    public function newLine(int $count = 1): void
    {
        $this->write(str_repeat(PHP_EOL, $count));
    }
}
