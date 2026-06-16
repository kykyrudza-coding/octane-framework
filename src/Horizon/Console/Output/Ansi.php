<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

final class Ansi
{
    public const RESET = "\033[0m";

    public const BLACK = "\033[30m";
    public const RED = "\033[31m";
    public const GREEN = "\033[32m";
    public const YELLOW = "\033[33m";
    public const BLUE = "\033[34m";
    public const MAGENTA = "\033[35m";
    public const CYAN = "\033[36m";
    public const WHITE = "\033[37m";

    public const BOLD = "\033[1m";
    public const DIM = "\033[2m";
}
