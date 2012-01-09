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
 * Console command for generating dummy email applications
 *
 * @category   Iba
 * @package    Iba
 * @copyright  Dutch Ministry of Foreign Affairs
 */
class Iba_Console_Command_Application_Generate extends Buza_Console_Command_Abstract
{
    /**
     * @var array
     */
    protected $_alphabet;

    /**
     * @var array
     */
    protected $_places;

    /**
     * @var array
     */
    protected $_lastNames;

    /**
     * @var array
     */
    protected $_streetNames;

    /**
     * @var randomMax
     */
    protected $_randomMax = 100;

    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('application:generate')
        ->setDescription('Generates dummy email applications to files or an IMAP server.')
        ->setDefinition(array(
            new InputOption('target', null, InputOption::VALUE_REQUIRED, 'Target must be "imap" or "file".', 'imap'),
            new InputOption('limit', null, InputOption::VALUE_REQUIRED, 'The amount of applications that will be generated.', '1000'),
            new InputOption('debug', null, InputOption::VALUE_REQUIRED, 'If true, debug information will be displayed.', 'false')
        ))
        ->setHelp(<<<EOT
Generates dummy email applications to files or an IMAP server.
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
        $this->_output->writeln('<comment>Started generating dummy email applications</comment>');

        $generateCount = $this->generateApplications();

        $seconds = round(microtime(true) - $startTime, 2);
        $this->_output->writeln(sprintf('<comment>Finished generating %s dummy email applications'
            . ' in %s seconds</comment>', $generateCount, $seconds));
    }

    /**
     * Generates email applications
     */
    public function generateApplications()
    {
        $this->_alphabet = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'w', 'v', 'y', 'z');

        $this->setPlaces();
        $this->setLastNames();
        $this->setStreetNames();
        $this->setMissions();
        $this->setCountries();

        // Create "applications" file folder if it doesn't exists
        $applicationsPath = APPLICATION_PATH . '/../data/applications';
        if (!file_exists($applicationsPath)) {
            $chmod = octdec('0'. Zend_Registry::get('config')->buza->permission->directory->writable);
            mkdir($applicationsPath, $chmod, true);
        }

        // Remove old generated files
        $it = new DirectoryIterator($applicationsPath);
        $fileCount = iterator_count($it) - 2; // Subtract 2 because of "." and ".." dot file
        foreach ($it as $file) {
            if ($file->isDot()) continue;
            unlink($file->getPathname());
        }
        $this->_output->writeln(sprintf("Removed %s old generated email applications", $fileCount));

        // Generate dummy email applications
        for ($i = 0; $i < $this->_input->getOption('limit'); $i++) {
            // Leave sometimes empty
            $mission = '';
            if (10 !== rand(1, $this->_randomMax)) {
                $mission = $this->_missions[array_rand($this->_missions)];
            }

            // Leave sometimes empty
            $country = '';
            if (10 !== rand(1, $this->_randomMax)) {
                $country = $this->_countries[array_rand($this->_countries)];
            }

            $candidateInitials   = $this->getRandomInitials();
            $candidateLastname   = $this->getRandomLastName();
            $candidateEmail      = $this->getRandomEmail($candidateLastname);
            $candidateInfix      = $this->getRandomLastNameInfix();

            // Sometimes invalid
            if (10 !== rand(1, $this->_randomMax)) {
                $candidateBirthdate  = sprintf('%02d-%02d-%04d', rand(1, 25), rand(1, 12), rand(1940, 1994));
            } else {
                $candidateBirthdate  = sprintf('%04d', rand(1940, 1994));
            }

            $referentInfix       = $this->getRandomLastNameInfix();
            $referentInitials    = $this->getRandomInitials();
            $referentLastName    = $this->getRandomLastName();
            $referentStreet      = $this->_streetNames[array_rand($this->_streetNames)];
            $referentHouseNumber = rand(14, 300);
            $referentEmail       = $this->getRandomEmail($referentLastName);

            // Leave sometimes empty
            $place = '';
            if (10 !== rand(1, $this->_randomMax)) {
                $place = $this->_places[array_rand($this->_places)];
            }

            $dummyAddition = '';
            if (5 == rand(1, 10)) {
                $dummyAddition = "Dit is dummytekst die door de applicatie moet worden genegeerd\n"
                    . "want hieronder begint het pas echt\n";
            }

            $emailApplication = <<<AANVRAAG
Postnaam:                    $mission
Achternaam Kandidaat:        $candidateLastname
Voorletters Kandidaat:       $candidateInitials
Tussenvoegsels Kandidaat:    $candidateInfix
Straatnaam Kandidaat:
Huisnummer Kandidaat:
Postcode Kandidaat:
Plaats Kandidaat:            $place
LandNaam Kandidaat:          $country
Geboortedatum Kandidaat:     $candidateBirthdate
Paspoortnummer Kandidaat:
E-mailadres Kandidaat:       $candidateEmail

Achternaam Referent:         $referentLastName
Voorletters Referent:        $referentInitials
Tussenvoegsels Referent:     $referentInfix
Straatnaam Referent:         $referentStreet
Huisnummer Referent:         $referentHouseNumber
Postcode Referent:
Plaats Referent:             $place
LandNaam Referent:           $country
E-mailadres Referent:        $referentEmail
AANVRAAG;

            $emailApplication = $dummyAddition . $emailApplication;

            if ('' != $dummyAddition) {
                $emailApplication .= "\nennnnn\n"
                    . "deze tekst ook";
            }

            if ($this->_input->getOption('target') == 'imap') {
                //mail('administrator@localhost','Referent aanvraag examen inburgering', $emailApplication);
                $this->_output->writeln("Sent email application to IMAP server");
            } else {
                $file = $applicationsPath . '/mail' . ($i + 1) . '.txt';
                file_put_contents($file, $emailApplication);
                $this->_output->writeln(sprintf('Created email application in file "%s"', $file));
            }
        }

        return $i;
    }

    public function setPlaces()
    {
        $this->_places = array('Roosendaal', 'Breda', 'Den Haag', 'Rotterdam', 'Echt', 'Sittard', 'Geleen', 'Nispen', 'Eindhoven',
            'Hengelo', 'Soest', 'Blaricum', 'Best', 'Waalwijk', 'Katwijk aan Zee', 'Soesdijk', 'Utrecht', 'Goile', 'Tilburg',
            'Helmond', 'Roermond', 'Valkenburg', 'Heerlen', 'Hoeven', 'Etten-Leur', 'Delft', 'Den Helder', 'Oisterwijk',
            'Dordrecht', 'Barendrecht', 'Lekkerkerk', 'Sliedrecht', 'Stellendam', 'Groningen', 'Haarlem', 'Hilversum',
            'Nijkerk', 'Amsterdam', 'Rockanje', 'Aalsum', 'Aalst', 'Aalsmeer', 'Abcoude', 'Baarle', 'Bavel', 'Bergen op Zoom',
            'Steenbergen', 'Biddinghuizen', 'Bladen', 'Den Bosch', 'Tiel', 'Nijmegen', 'Boxmeer', 'Boskoop', 'Budel', 'Burgum',
            'Chaam', 'Cuijk', 'De Bilt', 'Den Dolder', 'Veenendaal', 'Dirksland', 'Dokkum', 'Dongen', 'Drachten', 'Druten',
            'Ede', 'Wageningen', 'Epe', 'Gemert', 'Giesbeek', 'Gilze', 'Rijen', 'Hoogerheide', 'Ossendrecht', 'Ijmuiden',
            'Arnhem', 'Amersfoort');
    }

    public function setLastNames()
    {
        $this->_lastNames = array('Klaasen', 'Jansen', 'Berenhove', 'Naaktgeboren', 'Vermeer', 'Haver', 'Moelands', 'Schepers',
            'Mies', 'Ooms', 'Antons', 'Eekelen', 'Pals', 'Brouwer', 'Veen', 'Kruijs', 'Corstjens', 'Meys', 'Becker', 'Rollema',
            'Broeren', 'Dijk', 'Dijkstra', 'Klopland', 'Verbeek', 'Lacroix', 'Lagrand', 'Buijs', 'Aanraad', 'Alles', 'Ames',
            'Angel', 'Brandwijk', 'Blommerde', 'Buijk', 'Brunschot', 'Davidse', 'Lange', 'Dulk', 'Nijs', 'Wemmers', 'Don',
            'Druten', 'Frose', 'Kemper', 'Graper', 'Groenendijk', 'Hamstra', 'Huber', 'Reintgen', 'Jacobs', 'Janssen', 'Matilda');
    }

    public function setStreetNames()
    {
        $this->_streetNames = array('Bekerweg', 'Vijfhuizenberg', 'Spuiweg', 'Ministerstraat', 'Disketteweg', 'Heksenstraat',
            'Niemandsweg', 'Hulsdonkseweg', 'Laan van Egmon', 'Huijbergseweg', 'Hainksestraat', 'Lepelstraat',
            'Bernard van echt straat', 'Plantagebaan', 'De lange Zegge', 'Onze lieve vrouwenstraat', 'De weg naar de Wetenschap',
            'Hoekstraat', 'Netwerkweg', 'BuZaStraat', 'Stationsplein', 'Spoorstraat', 'Waterstraat', 'Sportweg', 'Laan van Belgie',
            'Planckenstraat', 'De Fuis', 'Annemariedijk');
    }

    public function setMissions()
    {
        $this->_missions[] = 'Nowhere';
        $this->_missions[] = 'Somewhere';
        $missions = $this->_em->getRepository('Iba\Entity\Mission')->findBy(array('availableForExamination' => 1));
        foreach ($missions as $mission) {
            $this->_missions[] = $mission->externalName;
        }
    }

    public function setCountries()
    {
        $this->_countries[] = 'Fantasy land';
        $this->_countries[] = 'Paradise';
        $countries = $this->_em->getRepository('Iba\Entity\Country')->findAll();
        foreach ($countries as $country) {
            if ($country->name != "Onbekend") {
                $this->_countries[] = $country->name;
            }
        }
    }

    public function getRandomInitials()
    {
        $numInitials = rand(1, 4);
        $initals = '';

        for ($i = 1; $i <= $numInitials; $i++) {
            // Leave sometimes empty
            if (10 !== rand(1, $this->_randomMax)) {
                $initals .= strtoupper($this->_alphabet[array_rand($this->_alphabet)]) . ($numInitials != $i ? '.' : '');
            }
        }

        return $initals;
    }

    public function getRandomLastName()
    {
        $lastName = '';

        // Leave sometimes empty
        if (10 !== rand(1, $this->_randomMax)) {
            $lastName = ucfirst($this->_lastNames[array_rand($this->_lastNames)]);
        }

        return $lastName;
    }

    public function getRandomLastNameInfix()
    {
        $randchoise = rand(1, 250);
        $prefixes = array('van', 'van der', 'de', 'van den');

        $prefix = ($randchoise < 75) ? $prefixes[array_rand($prefixes)] : '';

        return $prefix;
    }

    public function getRandomEmail($lastName)
    {
        $email = '';
        $domains = array('live.nl', 'hotmail.com', 'gmail.com', 'yahoomail.com', 'home.nl',
            'hetnet.nl', 'kpnmail.com', 'vodafone.nl', 'freemail.com', 'lightmail.com', 'hyvesmail.nl');

        // Leave sometimes empty
        if (10 !== rand(1, $this->_randomMax)) {
            $email = $lastName . rand(1, 500) . '@' . $domains[array_rand($domains)];
        }

        return $email;
    }
}
