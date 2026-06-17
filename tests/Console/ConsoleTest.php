<?php

declare(strict_types=1);

namespace Tests\Console;

use Horizon\Arch\Container;
use Horizon\Console\Command;
use Horizon\Console\CommandRegistry;
use Horizon\Console\ConsoleKernel;
use Horizon\Console\Input\ConsoleInput;
use Horizon\Console\Output\Ansi;
use Horizon\Console\Output\OutputFormatter;
use Horizon\Console\Output\ProgressBar;
use Horizon\Console\Output\Spinner;
use Horizon\Console\Output\Table;
use Horizon\Contracts\Console\ConsoleInputContract;
use Horizon\Contracts\Console\ConsoleOutputContract;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConsoleTest extends TestCase
{
    public function test_console_input_returns_argument_by_index(): void
    {
        $input = new ConsoleInput(['octane', 'list']);

        $this->assertSame('list', $input->argument(1));
    }

    public function test_console_input_returns_default_for_missing_argument(): void
    {
        $input = new ConsoleInput(['octane']);

        $this->assertSame('fallback', $input->argument(9, 'fallback'));
    }

    public function test_console_input_returns_all_arguments(): void
    {
        $argv = ['octane', 'version'];

        $this->assertSame($argv, (new ConsoleInput($argv))->arguments());
    }

    public function test_ansi_combine_merges_codes(): void
    {
        $this->assertSame("\033[1;92m", Ansi::combine(Ansi::BOLD, Ansi::BRIGHT_GREEN));
    }

    public function test_output_formatter_applies_known_style(): void
    {
        $formatted = (new OutputFormatter())->format('<success>Done</success>');

        $this->assertStringContainsString('Done', $formatted);
        $this->assertStringContainsString(Ansi::RESET, $formatted);
    }

    public function test_output_formatter_leaves_unknown_style_content(): void
    {
        $this->assertSame('Plain', (new OutputFormatter())->format('<unknown>Plain</unknown>'));
    }

    public function test_output_formatter_handles_nested_tags(): void
    {
        $formatted = (new OutputFormatter())->format('<bold><success>OK</success></bold>');

        $this->assertStringContainsString('OK', $formatted);
        $this->assertStringContainsString(Ansi::BOLD, $formatted);
    }

    public function test_command_name_delegates_to_static_command_name(): void
    {
        $this->assertSame('sample', (new ConsoleSampleCommand())->name());
    }

    public function test_command_default_description_is_empty(): void
    {
        $this->assertSame('', (new ConsoleSampleCommand())->description());
    }

    public function test_command_run_invokes_handle(): void
    {
        $output = new ConsoleMemoryOutput();
        $status = (new ConsoleSampleCommand())->run(new ConsoleInput(['octane']), $output);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertSame('handled', ConsoleSampleCommand::$lastAction);
    }

    public function test_command_registry_registers_without_instantiating_command(): void
    {
        ConsoleCountingCommand::$constructed = 0;
        $registry = new CommandRegistry(new Container());
        $registry->register(ConsoleCountingCommand::class);

        $this->assertSame(0, ConsoleCountingCommand::$constructed);
        $this->assertSame(['counting' => ConsoleCountingCommand::class], $registry->all());
    }

    public function test_command_registry_resolves_command_on_find(): void
    {
        $registry = new CommandRegistry(new Container());
        $registry->register(ConsoleSampleCommand::class);

        $this->assertInstanceOf(ConsoleSampleCommand::class, $registry->find('sample'));
    }

    public function test_command_registry_returns_null_for_unknown_or_null_command(): void
    {
        $registry = new CommandRegistry(new Container());

        $this->assertNull($registry->find(null));
        $this->assertNull($registry->find('missing'));
    }

    public function test_command_registry_rejects_non_command_class(): void
    {
        $this->expectException(RuntimeException::class);

        (new CommandRegistry(new Container()))->register(ConsoleNotACommand::class);
    }

    public function test_console_kernel_returns_failure_for_missing_command(): void
    {
        $kernel = new ConsoleKernel(new CommandRegistry(new Container()));

        $this->assertSame(1, $kernel->handle(['octane', 'missing']));
    }

    public function test_table_renders_headers_and_rows(): void
    {
        $output = new ConsoleMemoryOutput();
        (new Table($output))
            ->setHeaders(['Name', 'Email'])
            ->addRow(['Ada', 'ada@test'])
            ->render();

        $this->assertStringContainsString('Name', $output->buffer);
        $this->assertStringContainsString('Ada', $output->buffer);
    }

    public function test_progress_bar_renders_current_and_total(): void
    {
        $output = new ConsoleMemoryOutput();
        $bar = new ProgressBar($output, 4);
        $bar->advance(2);
        $bar->finish();

        $this->assertStringContainsString('(4/4)', $output->buffer);
    }

    public function test_spinner_run_returns_callback_result_on_success(): void
    {
        $output = new ConsoleMemoryOutput();

        $result = (new Spinner($output))->run('Work', fn () => 'done');

        $this->assertSame('done', $result);
        $this->assertStringContainsString('Work', $output->buffer);
    }

    public function test_spinner_marks_failure_and_rethrows(): void
    {
        $output = new ConsoleMemoryOutput();

        try {
            (new Spinner($output))->run('Work', fn () => throw new RuntimeException('failed'));
        } catch (RuntimeException $exception) {
            $this->assertSame('failed', $exception->getMessage());
            $this->assertStringContainsString('Work', $output->buffer);

            return;
        }

        $this->fail('Spinner did not rethrow callback exception.');
    }
}

final class ConsoleMemoryOutput implements ConsoleOutputContract
{
    public string $buffer = '';

    public string $errors = '';

    public function write(string $message): void
    {
        $this->buffer .= $message;
    }

    public function line(string $message = ''): void
    {
        $this->buffer .= $message.PHP_EOL;
    }

    public function error(string $message): void
    {
        $this->errors .= $message.PHP_EOL;
    }

    public function newLine(int $count = 1): void
    {
        $this->buffer .= str_repeat(PHP_EOL, $count);
    }
}

final class ConsoleSampleCommand extends Command
{
    public static string $lastAction = '';

    public static function commandName(): string
    {
        return 'sample';
    }

    public function handle(ConsoleInputContract $input, ConsoleOutputContract $output): int
    {
        self::$lastAction = 'handled';

        return self::SUCCESS;
    }
}

final class ConsoleCountingCommand extends Command
{
    public static int $constructed = 0;

    public function __construct()
    {
        self::$constructed++;
    }

    public static function commandName(): string
    {
        return 'counting';
    }

    public function handle(ConsoleInputContract $input, ConsoleOutputContract $output): int
    {
        return self::SUCCESS;
    }
}

final class ConsoleNotACommand {}
