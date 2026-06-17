<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

use Horizon\Contracts\Console\ConsoleOutputContract;

final class Table
{
    /** @var list<string> */
    private array $headers = [];

    /** @var list<list<string>> */
    private array $rows = [];

    public function __construct(
        private readonly ConsoleOutputContract $output,
    ) {}

    /** @param list<string> $headers */
    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /** @param list<string> $row */
    public function addRow(array $row): static
    {
        $this->rows[] = $row;

        return $this;
    }

    /** @param list<list<string>> $rows */
    public function setRows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function render(): void
    {
        $widths = $this->columnWidths();

        $this->output->line($this->separator($widths, '┌', '┬', '┐'));
        $this->output->line($this->row($this->headers, $widths, header: true));
        $this->output->line($this->separator($widths, '├', '┼', '┤'));

        foreach ($this->rows as $row) {
            $this->output->line($this->row($row, $widths));
        }

        $this->output->line($this->separator($widths, '└', '┴', '┘'));
    }

    /** @param list<int> $widths */
    private function separator(array $widths, string $left, string $mid, string $right): string
    {
        $parts = array_map(
            static fn (int $w): string => str_repeat('─', $w + 2),
            $widths,
        );

        return Ansi::BRIGHT_BLACK
            .$left.implode($mid, $parts).$right
            .Ansi::RESET;
    }

    /**
     * @param list<string> $cells
     * @param list<int>    $widths
     */
    private function row(array $cells, array $widths, bool $header = false): string
    {
        $border = Ansi::BRIGHT_BLACK.'│'.Ansi::RESET;
        $parts  = [];

        foreach ($widths as $i => $width) {
            $cell    = $cells[$i] ?? '';
            $padded  = mb_str_pad($cell, $width);

            $parts[] = ' '.($header
                    ? Ansi::combine(Ansi::BOLD, Ansi::BRIGHT_WHITE).$padded.Ansi::RESET
                    : $padded
                ).' ';
        }

        return $border.implode($border, $parts).$border;
    }

    /** @return list<int> */
    private function columnWidths(): array
    {
        $all = empty($this->headers)
            ? $this->rows
            : [$this->headers, ...$this->rows];

        $cols = empty($all) ? 0 : count($all[0]);

        return array_map(
            static fn (int $i): int => max(
                array_map(
                    static fn (array $row): int => mb_strlen($row[$i] ?? ''),
                    $all,
                )
            ),
            range(0, $cols - 1),
        );
    }
}
