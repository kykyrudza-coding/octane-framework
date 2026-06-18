<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

use Horizon\Contracts\Console\Output\ConsoleOutputContract;

final class Spinner
{
    private const FRAMES = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];

    private int $frame = 0;

    public function __construct(
        private readonly ConsoleOutputContract $output,
    ) {}

    /**
     * Запустити closure із спінером.
     * Після виконання — очищає рядок і виводить результат.
     */
    public function run(string $label, callable $callback): mixed
    {
        $this->start($label);

        try {
            $result = $callback();
            $this->success($label);

            return $result;
        } catch (\Throwable $e) {
            $this->fail($label);
            throw $e;
        }
    }

    /**
     * Тікнути один кадр вручну (для ситуацій де ти сам контролюєш loop).
     */
    public function tick(string $label): void
    {
        $frame = self::FRAMES[$this->frame % count(self::FRAMES)];
        $this->frame++;

        // \r повертає курсор на початок рядка без нового рядка
        $this->output->write(
            "\r".Ansi::CYAN.$frame.Ansi::RESET.' '.$label.'  ',
        );
    }

    public function start(string $label): void
    {
        // Приховати курсор
        $this->output->write("\033[?25l");
        $this->tick($label);
    }

    public function success(string $label): void
    {
        $this->clear();
        $this->output->write("\033[?25h"); // показати курсор
        $this->output->line(Ansi::BRIGHT_GREEN.'✓'.Ansi::RESET.' '.$label);
    }

    public function fail(string $label): void
    {
        $this->clear();
        $this->output->write("\033[?25h");
        $this->output->line(Ansi::BRIGHT_RED.'✗'.Ansi::RESET.' '.$label);
    }

    private function clear(): void
    {
        // \r + очистка до кінця рядка
        $this->output->write("\r\033[K");
    }
}
