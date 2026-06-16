<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

use Horizon\Contracts\Console\ConsoleOutputContract;

final class OutputStyle
{
    public function __construct(
        private readonly ConsoleOutputContract $output,
    ) {
    }

    public function title(string $message): void
    {
        $length = mb_strlen($message) + 4;

        $this->output->line();
        $this->output->line('<info>┌'.str_repeat('─', $length).'┐</info>');
        $this->output->line(
            '<info>│</info>  <bold>'.$message.'</bold>  <info>│</info>',
        );
        $this->output->line('<info>└'.str_repeat('─', $length).'┘</info>');
        $this->output->line();
    }

    public function section(string $message): void
    {
        $this->output->line();
        $this->output->line("<bold>{$message}</bold>");
        $this->output->line(
            '<dim>'.str_repeat('─', mb_strlen($message)).'</dim>',
        );
    }

    public function info(string $message): void
    {
        $this->output->line("<info>ℹ</info> {$message}");
    }

    public function success(string $message): void
    {
        $this->output->line("<success>✓</success> {$message}");
    }

    public function warning(string $message): void
    {
        $this->output->line("<warning>!</warning> {$message}");
    }

    public function error(string $message): void
    {
        $this->output->error("✗ {$message}");
    }

    public function bullet(string $message): void
    {
        $this->output->line("  <dim>•</dim> {$message}");
    }

    public function keyValue(string $key, string $value): void
    {
        $this->output->line(
            sprintf(
                '  <info>%-16s</info> %s',
                $key,
                $value,
            ),
        );
    }
}
