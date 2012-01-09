<?php
/**
 * @category   Iba
 * @package    Iba
 * @copyright  Dutch Ministry of Foreign Affairs
 */

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console;

/**
 * Example console command
 *
 * @category   Iba
 * @package    Iba
 * @copyright  Dutch Ministry of Foreign Affairs
 */
class Iba_Console_Command_General_Example extends Buza_Console_Command_Abstract
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('general:example')
        ->setDescription('Example command line interface command.')
        ->setDefinition(array(
            new InputOption('type', null, InputOption::VALUE_REQUIRED, 'Type must be "foo" or "bar"')
        ))
        ->setHelp(<<<EOT
Example command line interface command.
EOT
        );
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $conn = $this->getHelper('db')->getConnection();
        $dialog = $this->getHelper('dialog');

        $output->writeln('<comment>ATTENTION</comment>: Foo text text text text text');
        $output->writeln('<error>Everything goes wrong!</error>');

        $name = $dialog->ask($output, "What is your name?\n", "Karel");
        $output->writeln(sprintf('Your name is "%s"', $name));

        $answer = $dialog->askConfirmation($output, 'Are you sure that you want to continue?');
        $output->writeln(sprintf('Your answer is "%s"', $answer));

        if ($input->isInteractive()) {
            $answer = $dialog->askConfirmation($output, $this->dialogGetQuestion('Do you confirm that this example is awesome', 'yes', '?'), true);
            if (!$answer) {
                $output->writeln('<error>Command aborted</error> Answer: ' . $answer);
                return 1;
            } else {
                $output->writeln('Answer: ' . $answer);
            }
        }

        $this->dialogWriteSection($output, 'Example section');

        $output->writeln('<info>This is an informational message.</info>');
    }
}
