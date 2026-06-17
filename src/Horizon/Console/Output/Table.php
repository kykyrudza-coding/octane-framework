<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

use Horizon\Contracts\Console\ConsoleOutputContract;

final class Table
{
    private const string TOP_LEFT = '┌';
    private const string TOP_MID = '┬';
    private const string TOP_RIGHT = '┐';
    private const string MID_LEFT = '├';
    private const string MID_MID = '┼';
    private const string MID_RIGHT = '┤';
    private const string BOTTOM_LEFT = '└';
    private const string BOTTOM_MID = '┴';
    private const string BOTTOM_RIGHT = '┘';
    private const string HORIZONTAL = '─';
    private const string VERTICAL = '│';

    /**
     * @var list<string>
     */
    private array $headers = [];

    /**
     * @var list<list<string>>
     */
    private array $rows = [];

    public function __construct(
        private readonly ConsoleOutputContract $output,
    ) {}

    /**
     * @param  list<string>  $headers
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param  list<string>  $row
     */
    public function addRow(array $row): static
    {
        $this->rows[] = $row;

        return $this;
    }

    /**
     * @param  list<list<string>>  $rows
     */
    public function setRows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function render(): void
    {
        $widths = $this->columnWidths();

        if ($widths === []) {
            $this->output->line(Ansi::BRIGHT_BLACK.'No rows to display.'.Ansi::RESET);

            return;
        }

        $this->output->line($this->separator($widths, self::TOP_LEFT, self::TOP_MID, self::TOP_RIGHT));

        if ($this->headers !== []) {
            $this->output->line($this->row($this->headers, $widths, header: true));
            $this->output->line($this->separator($widths, self::MID_LEFT, self::MID_MID, self::MID_RIGHT));
        }

        foreach ($this->rows as $row) {
            $this->output->line($this->row($row, $widths));
        }

        $this->output->line($this->separator($widths, self::BOTTOM_LEFT, self::BOTTOM_MID, self::BOTTOM_RIGHT));
    }

    /**
     * @param  list<int>  $widths
     */
    private function separator(array $widths, string $left, string $mid, string $right): string
    {
        $parts = array_map(
            static fn (int $width): string => str_repeat(self::HORIZONTAL, $width + 4),
            $widths,
        );

        return Ansi::BRIGHT_BLACK
            .$left.implode($mid, $parts).$right
            .Ansi::RESET;
    }

    /**
     * @param  list<string>  $cells
     * @param  list<int>  $widths
     */
    private function row(array $cells, array $widths, bool $header = false): string
    {
        $border = Ansi::BRIGHT_BLACK.self::VERTICAL.Ansi::RESET;
        $parts = [];

        foreach ($widths as $index => $width) {
            $cell = (string) ($cells[$index] ?? '');
            $padded = $this->pad($cell, $width);

            $content = match (true) {
                $header => Ansi::combine(Ansi::BOLD, Ansi::CYAN).$padded.Ansi::RESET,
                $index === 0 => Ansi::BOLD.$padded.Ansi::RESET,
                default => $padded,
            };

            $parts[] = '  '.$content.'  ';
        }

        return $border.implode($border, $parts).$border;
    }

    /**
     * @return list<int>
     */
    private function columnWidths(): array
    {
        $all = $this->headers === []
            ? $this->rows
            : [$this->headers, ...$this->rows];

        if ($all === []) {
            return [];
        }

        $columns = max(array_map('count', $all));

        if ($columns === 0) {
            return [];
        }

        return array_map(
            static fn (int $index): int => max(
                array_map(
                    static fn (array $row): int => mb_strlen((string) ($row[$index] ?? '')),
                    $all,
                )
            ),
            range(0, $columns - 1),
        );
    }

    private function pad(string $value, int $width): string
    {
        $length = mb_strlen($value);

        if ($length >= $width) {
            return $value;
        }

        return $value.str_repeat(' ', $width - $length);
    }
}
