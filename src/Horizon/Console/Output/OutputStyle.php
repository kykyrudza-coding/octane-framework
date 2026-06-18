<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

use Horizon\Contracts\Console\Output\ConsoleOutputContract;

final readonly class OutputStyle
{
    public function __construct(
        private ConsoleOutputContract $output,
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

    public function badge(string $label, string $type = 'info'): void
    {
        $tag = "badge-$type";
        $this->output->line(" <{$tag}> {$label} </{$tag}>");
    }

    public function divider(string $label = ''): void
    {
        $width = 60;

        if ($label === '') {
            $this->output->line('<dim>'.str_repeat('─', $width).'</dim>');
            return;
        }

        $pad  = (int) (($width - mb_strlen($label) - 2) / 2);
        $line = str_repeat('─', $pad).' '.$label.' '.str_repeat('─', $pad);
        $this->output->line("<dim>{$line}</dim>");
    }

    public function twoColumn(string $left, string $right, int $width = 60): void
    {
        $dots = str_repeat('.', max(1, $width - mb_strlen($left) - mb_strlen($right)));
        $this->output->line($left.'<dim>'.$dots.'</dim>'.$right);
    }

    public function spinner(): Spinner
    {
        return new Spinner($this->output);
    }

    public function progress(int $total): ProgressBar
    {
        return new ProgressBar($this->output, $total);
    }

    public function table(): Table
    {
        return new Table($this->output);
    }

    public function newLine(int $count = 1): void
    {
        $this->output->newLine($count);
    }
}
