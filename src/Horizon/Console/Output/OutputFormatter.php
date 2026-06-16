<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

final class OutputFormatter
{
    public function format(string $message): string
    {
        return preg_replace_callback(
            '/<([a-z]+)>(.*?)<\/\1>/s',
            fn (array $matches): string => $this->apply(
                $matches[1],
                $matches[2],
            ),
            $message,
        ) ?? $message;
    }

    private function apply(string $style, string $message): string
    {
        $ansi = match ($style) {
            'info' => Ansi::CYAN,
            'success' => Ansi::GREEN,
            'warning' => Ansi::YELLOW,
            'error' => Ansi::RED,
            'bold' => Ansi::BOLD,
            'dim' => Ansi::DIM,
            default => '',
        };

        if ($ansi === '') {
            return $message;
        }

        return $ansi.$message.Ansi::RESET;
    }
}
