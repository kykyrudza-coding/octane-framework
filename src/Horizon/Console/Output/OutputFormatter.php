<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

final class OutputFormatter
{
    /**
     * Named style map → one or more ANSI constants to combine.
     *
     * @var array<string, list<string>>
     */
    private array $styles = [
        // Semantic
        'info'    => [Ansi::CYAN],
        'success' => [Ansi::BRIGHT_GREEN],
        'warning' => [Ansi::BRIGHT_YELLOW],
        'error'   => [Ansi::BRIGHT_RED],
        'muted'   => [Ansi::BRIGHT_BLACK],
        'label'   => [Ansi::BRIGHT_WHITE],

        // Typography
        'bold'      => [Ansi::BOLD],
        'dim'       => [Ansi::DIM],
        'italic'    => [Ansi::ITALIC],
        'underline' => [Ansi::UNDERLINE],

        // Combined
        'strong'  => [Ansi::BOLD, Ansi::BRIGHT_WHITE],
        'code'    => [Ansi::DIM,  Ansi::BRIGHT_CYAN],
        'link'    => [Ansi::UNDERLINE, Ansi::BRIGHT_BLUE],
        'comment' => [Ansi::DIM,  Ansi::BRIGHT_BLACK],

        // Badge backgrounds (text буде чорний для контрасту)
        'badge-info'    => [Ansi::BG_BRIGHT_CYAN,    Ansi::BLACK, Ansi::BOLD],
        'badge-success' => [Ansi::BG_BRIGHT_GREEN,   Ansi::BLACK, Ansi::BOLD],
        'badge-warning' => [Ansi::BG_BRIGHT_YELLOW,  Ansi::BLACK, Ansi::BOLD],
        'badge-error'   => [Ansi::BG_BRIGHT_RED,     Ansi::WHITE, Ansi::BOLD],
    ];

    public function format(string $message): string
    {
        // Рекурсивно обробляємо теги (дозволяє вкладення)
        return preg_replace_callback(
            '/<([a-z][\w-]*)>(.*?)<\/\1>/s',
            fn (array $matches): string => $this->apply(
                $matches[1],
                $this->format($matches[2]),  // <-- рекурсія для вкладень
            ),
            $message,
        ) ?? $message;
    }

    private function apply(string $style, string $content): string
    {
        $codes = $this->styles[$style] ?? null;

        if ($codes === null) {
            return $content;
        }

        return Ansi::combine(...$codes).$content.Ansi::RESET;
    }
}
