<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

use Horizon\Contracts\Console\Output\ConsoleOutputContract;

final class ProgressBar
{
    private int $current = 0;

    private int $width = 40;

    public function __construct(
        private readonly ConsoleOutputContract $output,
        private readonly int $total,
    ) {}

    public function advance(int $step = 1): void
    {
        $this->current = min($this->current + $step, $this->total);
        $this->render();
    }

    public function finish(): void
    {
        $this->current = $this->total;
        $this->render();
        $this->output->line(); // переходимо на новий рядок
    }

    private function render(): void
    {
        $percent  = $this->total > 0 ? $this->current / $this->total : 0;
        $filled   = (int) round($this->width * $percent);
        $empty    = $this->width - $filled;

        $bar = Ansi::combine(Ansi::BRIGHT_GREEN, Ansi::BOLD)
            .str_repeat('█', $filled)
            .Ansi::RESET
            .Ansi::BRIGHT_BLACK
            .str_repeat('░', $empty)
            .Ansi::RESET;

        $pct   = str_pad((int) ($percent * 100).'%', 4, ' ', STR_PAD_LEFT);
        $stats = Ansi::BRIGHT_BLACK
            ." {$pct} ({$this->current}/{$this->total})"
            .Ansi::RESET;

        $this->output->write("\r [{$bar}]{$stats}");
    }
}
