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
 * Console command for importing applications
 *
 * @category   Iba
 * @package    Iba
 * @copyright  Dutch Ministry of Foreign Affairs
 */
class Iba_Console_Command_Application_Import extends Buza_Console_Command_Abstract
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('application:import')
        ->setDescription('Imports applications from an IMAP server or files into the database.')
        ->setDefinition(array(
            new InputOption('source', null, InputOption::VALUE_REQUIRED, 'Source must be "imap" or "file".', 'imap'),
            new InputOption('debug', null, InputOption::VALUE_REQUIRED, 'If true, debug information will be displayed.', 'false')
        ))
        ->setHelp(<<<EOT
Imports applications from an IMAP server or files into the database.
EOT
        );
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        parent::execute($input, $output);

        $startTime = microtime(true);
        $this->_output->writeln('<comment>Started importing applications</comment>');

        switch ($input->getOption('source')) {
            case 'file':
                $importCount = $this->importFileApplications();
                break;
            default:
                $importCount = $this->importImapApplications();
                break;
        }

        if ($importCount == 0) {
            $this->_output->writeln(sprintf('No applications available to import', $importCount));
        }

        $seconds = round(microtime(true) - $startTime, 2);
        $this->_output->writeln(sprintf('<comment>Finished importing %s applications in %s seconds</comment>',
            $importCount, $seconds));
    }

    /**
     * Imports applications from files into the database
     */
    public function importFileApplications()
    {
        $path = APPLICATION_PATH . '/../data/applications';

        $it = new DirectoryIterator($path);
        $i = 0;
        foreach ($it as $file) {
            if ($file->isDot()) continue;

            $content = file_get_contents($file->getPathname());
            $emailApplication = array(
                'raw' => $content,
                'parsed' => $this->parseApplication($content)
            );

            $this->importApplication($emailApplication, $file->getPathname());
            $i++;
        }

        return $i;
    }

    /**
     * Imports applications from an IMAP server into the database
     */
    public function importImapApplications()
    {

    }

    /**
     * Imports the specified application into the database
     *
     * @param array          $emailApplication
     * @param integer|string $identifier Source IMAP: integer IMAP UID | Source FILE: string Pathname
     */
    public function importApplication(array $emailApplication, $identifier)
    {
        $this->_em->beginTransaction();

        // The application will be rejected if any exceptions are thrown during the
		// import process of this application
        try {
            $this->validateApplication($emailApplication);

            // Save referent
            $referent = new Iba\Entity\Referent();
            $referent->fillFromEmailApplication($emailApplication);
            $this->_em->persist($referent);

            // Save candidate
            $candidate = new Iba\Entity\Candidate();
            $candidate->fillFromEmailApplication($emailApplication);
            $this->_em->persist($candidate);

            // Save application
            $mission = $this->_em->getRepository('Iba\Entity\Mission')
                ->findOneBy(array('externalName' => $emailApplication['parsed']['Postnaam']));
            if (null === $mission) {
                throw new \Buza_Exception('No mission found');
            }

            $application = new Iba\Entity\Application();
            $application->mission   = $mission;
            $application->candidate = $candidate;
            $application->referent  = $referent;
            if ($this->_input->getOption('source') == 'imap') {
                $application->imapId = $identifier;
            }
            $this->_em->persist($application);
            $this->_em->flush();

            // Set application status
    		$application->setApplicationStatus(1, 'Application inserted', 'System');

            $this->_em->flush();
            $this->_em->commit();
        } catch (Exception $e) {
            $this->_output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($this->_input->getOption('debug') == 'true') {
                Buza_Controller_Error::logException($e, $priority = Zend_Log::NOTICE);
            }
            $this->_em->rollback();
            $this->rejectApplication($emailApplication, $identifier);
            return false;
        }

        $this->_output->writeln('<info>Inserted application</info>');
        if ($this->_input->getOption('debug') == 'true') {
            $this->_output->write(print_r($emailApplication['parsed'], true));
        }

        if ($this->_input->getOption('source') == 'imap') {
            $okFolder = Zend_Registry::get('config')->applicationIMAPOKFolder;
            $newUid = 0;
            // TODO: $imap->moveMessage($emailApplication['message']['uid'], $okFolder, $newUid);

            // Update the application with the new message uid
            $application->imapId = $newUid;
        } elseif ('file' == $this->_input->getOption('source')) {
            if (file_exists($identifier)) {
                unlink($identifier);
                $this->_output->writeln(sprintf('Removed application file "%s"', $identifier));
            }
        }

//		TODO: MailHelper::sendApplicationEmail($application, $mission, $referent,
//				$candidate, EmailTemplateModel::TEMPL_APPLICATIONCONFIRMATION);

		return true;
    }

    /**
	 * Rejects an email application
	 *
	 * @param array     $emailApplication
     * @param integer|string $identifier Source IMAP: integer IMAP UID | Source FILE: string Pathname
	 */
	public function rejectApplication(array $emailApplication, $identifier)
    {
        $this->_em->beginTransaction();

        try
        {
            $rejectedApplication = new Iba\Entity\RejectedApplication();
            $rejectedApplication->imapId = 0;
            $rejectedApplication->emailMessage = $emailApplication['raw'];
            $this->_em->persist($rejectedApplication);

            $this->_output->writeln('<info>Inserted application as rejected</info>');
            $this->_output->write(print_r($emailApplication['parsed'], true));

            if ('imap' == $this->_input->getOption('source')) {
                $failFolder = Zend_Registry::get('config')->applicationIMAPFailFolder;
    //            $newUid = 0;
    //            $imap->moveMessage($emailApplication['message']['uid'], $failFolder, $newUid);
                $this->_output->writeln(sprintf('Moved application to IMAP folder "%s"', $failFolder));
            } elseif ('file' == $this->_input->getOption('source')) {
                if (file_exists($identifier)) {
                    unlink($identifier);
                    $this->_output->writeln(sprintf('Removed application file "%s"', $identifier));
                }
            }

            $this->_em->flush();
            $this->_em->commit();
        } catch (Exception $e) {
            $this->_em->rollback();
        }
	}

    /**
     * Validates the application
     *
     * @param array $emailApplication
     */
    public function validateApplication(array $emailApplication)
    {
        $parsedApplication = $emailApplication['parsed'];

        $requiredFields = array('Postnaam', 'Achternaam Kandidaat', 'Voorletters Kandidaat',
            'Plaats Kandidaat', 'LandNaam Kandidaat', 'Geboortedatum Kandidaat',
            'Achternaam Referent', 'Voorletters Referent', 'Straatnaam Referent',
            'Huisnummer Referent', 'Plaats Referent', 'LandNaam Referent', 'E-mailadres Referent');

        foreach ($requiredFields as $field) {
            if ('' == trim($parsedApplication[$field])) {
                throw new Buza_Exception(sprintf('Application field "%s" not found', $field));
            }
        }
    }

    /**
     * Parses the specified raw application
     *
     * @param  string $rawApplication
     * @return array
     */
    public function parseApplication($rawApplication)
    {
        $applicationHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Application');
        return $applicationHelper->parse($rawApplication);
    }
}
