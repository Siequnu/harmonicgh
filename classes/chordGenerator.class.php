<?php

class chordGenerator {

     # Class defaults
     private $chordsPerBlock = 32;
     private $numberOfBlocks = 4;
     private $initialChord = 'C';

     public function __construct() {
          require_once './classes/chordCatalog.class.php';
          require_once './classes/harmonyLogic.class.php';
     }


    /*
     * Main entry into program
     */
     public function main()
     {
          # Generate form and assign data
          $formData = $this->generateForm();

          if ($formData) {
               # Assign form data
               $this->assignFormData($formData);

               # Calculate total number of chords needed
               $totalchords = $this->numberOfBlocks * $this->chordsPerBlock;

               # Initialize array
               $sequence = array ();

               # Generate sequence of baroque style chords
               $sequence = $this->chordGenerator($sequence);

               # Sort data according to form selection
               $this->formatSequence ($sequence);
          }
     }


      /*
     * Generates a form for data entry
     *
     * @param str $prompt1 Message prompt
     * @param str $prompt2 Second Prompt
     * @param str $name1 Name of data entered
     * @param str $name2 Second Name
     * @param str $submitValue Message on submit button
     *
     * @return string
     */
     private function generateForm () {

          # Load the form module
         require_once ('./lib/ultimateform/ultimateForm.php');
         require_once ('./lib/ultimateform/pureContent.php');
         require_once ('./lib/ultimateform/application.php');

          # Create a form instance
          $form = new form (array (
            'div'                    => 'form-download',
            'submitButtonText'       => 'Generate Baroque Harmony',
			'formCompleteText'       => false,
			'requiredFieldIndicator' => false,
			'submitButtonAccesskey'  => false,
           ));

          $form->heading (2, 'Generative Baroque Harmony Creation');
          $form->heading ('p', 'Please complete the form below');

          # Create a standard input box
          $form->input (array (
              'name'					=> 'verses',
              'title'					=> 'Number of verses',
              'enforceNumeric'		    => true,
              'size'					=> 3,
              'default'                 => 2,
            ));

          # Create a standard input box
          $form->input (array (
              'name'					=> 'key',
              'title'					=> '"C" major or "c" minor',
              'enforceNumeric'		    => false,
              'size'					=> 1,
              'default'                 => 'C',
            ));

          # A set of radio buttons
          $form->radiobuttons (array (
          'name'					=> 'radiobuttons',
          'values'			        => array ('Generate list of chords', 'Generate audio file with harmony',),
          'title'			 		=> 'Generation options',
          'description'			    => '',
          'output'			    	=> array (),
          'required'				=> true,
          'default'			    	=> 'Generate audio file with harmony',
          ));

          # Process form and return result
          $result = $form->process ();
          return $result;

          #<link rel="stylesheet" href="./content/style.css">
        
     }


     /*
      * Assigns form Data to variables
      */
     public function assignFormData ($formData) {

          # Check valid key has been entered
          if (htmlspecialchars($formData['key'] !== "C")) {
               if (htmlspecialchars($formData['key'] !== "c")) {
                         echo 'Harmony generation currently only works with C or c. Please enter one of these options.';
                         die;
               }
          }

          # Enter defaults if form has been submitted empty
          if (!isSet ($formData['key'])) {
               $this->numberOfBlocks = 2;
               $this->initialChord = 'C';
               $this->radioButtonOption = 'harmony';
          }

          # Assign values
          if ($formData['radiobuttons'] == 'Generate list of chords') {
               $this->radioButtonOption = 'chord';
          } else {
               $this->radioButtonOption = 'harmony';
          }

          $this->numberOfBlocks = $formData['verses'];
          $this->initialChord = $formData['key'];

     }


     /*
      * Returns an array of generated chords
      */
     private function chordGenerator ($sequence) {
          for ($cycleNumbers = 0; $cycleNumbers < $this->numberOfBlocks; $cycleNumbers++) {
               $verseBlock = $this->generateBaroqueChords ($this->initialChord);
               foreach ($verseBlock as $finalChord) {
                    $sequence [] = $finalChord;
               }
          }
          return $sequence;
     }


     /*
     * Loop that generates a verse of 32 chords starting with a given initial chord
     *
     * @param string $firstChord Initial chord
     *
     * @return string String of logically generated chords
     */
     public function generateBaroqueChords ($firstChord)
     {
          $this->chordCatalog = new chordCatalog;

          # Determine Tonalities
          # Determine if first chord M or m, convert into cipher
          if (ctype_upper ($firstChord)) {
               $startingCypher = 'I';
               $startingKey = 'M';
               $dominantKey = 'M';
               $subMediantKey = 'm';
               $subMediantCypher = "i";
               $tonicTonality = $firstChord;
               $tonicTonalityIndex = array_search ($tonicTonality, $this->chordCatalog->serialScaleMajor);
               $dominantTonality = $this->chordCatalog->serialScaleMajor[($this->serialIndexCorrector (($tonicTonalityIndex + 7)))];
          }
          else {
               $startingCypher = 'i';
               $startingKey = 'm';
               $dominantKey = 'm';
               $subMediantKey = 'M';
               $subMediantCypher = "I";
               $tonicTonality = $firstChord;
               $tonicTonalityIndex = array_search ($tonicTonality, $this->chordCatalog->serialScale);
               $dominantTonality = $this->chordCatalog->serialScale[($this->serialIndexCorrector (($tonicTonalityIndex + 7)))];
          }

          # Initialize an empty array and set the first Chord
          unset ($this->sequence);
          $this->sequence[] = $firstChord;

          # Add first cadence Array to sequence
          $this->addArrayToMainSequence ($this->generateCadenceWithChords ($firstChord, 'CIndexMajor', 'CIndexMinor'));

          # Get dominant Cadence
          $dominantCadenceArray = $this->generateCadenceWithChords ($dominantKey, 'GIndexMajor', 'GIndexMinor');

          # Add cadence to sequence, cadence comes a 3 part array so add dominant chord before it
          $this->sequence[] = $dominantTonality;

          $this->addArrayToMainSequence ($dominantCadenceArray);

          # Get a harmonic progression for the next line of chords
          $modulationGoal = ((ctype_upper ($subMediantKey)) ? "vi" : "VI");
          $progressionSequence = $this->progressionGenerator ($startingCypher);

          # Convert cyphers into chords
          $progressionSequence = $this->chordCatalog->convertCypherIntoChord ($progressionSequence, $startingCypher, 'CIndexMajor', 'CIndexMinor');

          # Generate cadence
          $modulationCadence = $this->generateCadenceWithChords ($subMediantKey, 'AbIndexMajor', 'AIndexMinor');

          #Merge progression and modulation, and merge this to total sequence
          $indexToInsertCadence = 5;
          foreach ($modulationCadence as $chord) {
               $progressionSequence [$indexToInsertCadence] = $chord;
               $indexToInsertCadence++;
          }

          $this->addArrayToMainSequence ($progressionSequence);

          # Next line is freely generated in submediant
          $freeSequence = $this->getFreeSequence ($subMediantCypher, '4', $subMediantCypher);

          # Convert cyphers into chords
          $freeSequence = $this->chordCatalog->convertCypherIntoChord ($freeSequence, $startingCypher, 'AIndexMinor', 'AbIndexMajor');

          $modulationCadence = $this->generateCadenceWithChords ($startingCypher, 'CIndexMajor', 'CIndexMinor');

          # Merge cadence starting index 4 of sequence, and merge this to total sequence
          $indexToInsertCadence = 5;
          foreach ($modulationCadence as $chord) {
               $freeSequence [$indexToInsertCadence] = $chord;
               $indexToInsertCadence++;
          }

          $this->addArrayToMainSequence ($freeSequence);

          unset ($freeSequence);

          # Get a random chord for 4 of the next chords
          $freeSequence = $this->getFreeSequence ($startingCypher, '4', $startingCypher);

          # Convert cyphers into chords
          $freeSequence = $this->chordCatalog->convertCypherIntoChord ($freeSequence, $startingCypher, 'CIndexMajor', 'CIndexMinor');

          $modulationCadence = $this->generateCadenceWithChords ($startingCypher, 'CIndexMajor', 'CIndexMinor');

          #Merge progression and modulation, and merge this to total sequence
          $indexToInsertCadence = 5;
          foreach ($modulationCadence as $chord) {
               $freeSequence [$indexToInsertCadence] = $chord;
               $indexToInsertCadence++;
          }

          $this->addArrayToMainSequence ($freeSequence);

          return $this->sequence;
     }


     /*
      *   Generate a Cadence and return an array with converted cyphers into chords
      *
      *   @param string $majorMinorDecider tested with ctype_upper to calculate either major or minor cadence
      *   @param string $majorChordLibrary chordCatalog array index for majorLibrary
      *   @param string $minorChordLibrary chordCatalog array index for minor library
      *
      *   @return array [0] [1] [2] with converted chords (ie C F G C)
      */
     public function generateCadenceWithChords ($majorMinorDecider, $majorChordLibrary, $minorChordLibrary) {
          # Generate cadence
          $modulationGoal = ((ctype_upper ($majorMinorDecider)) ? "I" : "i" );
          $modulationCadence = $this->cadenceGenerator ($modulationGoal);

          # Convert cyphers into chords
          $modulationCadence = $this->chordCatalog->convertCypherIntoChord ($modulationCadence, $modulationGoal, $majorChordLibrary, $minorChordLibrary);

          return $modulationCadence;
     }


     /*
      *    Sequentially add an array contents to the main sequence
      *
      *    @param array $arrayToBeAdded Array to be added at end of sequence
      */
     public function addArrayToMainSequence ($arrayToBeAdded) {
          foreach ($arrayToBeAdded as $value) {
               $this->sequence[] = $value;
          }
     }


     /*
      *    Returns a generated cadence from a given index
      *
      *    @param str $initialIndex i or I to generate a cadence
      *
      *    @return array Array with 0, 1, 2 with ii V I (for example)
      */
     public function cadenceGenerator ($initialIndex) {

          # Determine if Major or Minor cadence, pick the cadence style
          $randomChoice = (ctype_upper ($initialIndex) ? mt_rand (1,3) : mt_rand (1,2));

          # Generate array
          if (ctype_upper ($initialIndex)) {
               $resultArray = $this->cadenceArrayGeneratorMajor ($initialIndex, $randomChoice);
          } else {
               $resultArray = $this->cadenceArrayGeneratorMinor ($initialIndex, $randomChoice);
          }
          return $resultArray;
     }


     /*
      * Generates a 3 chord cadence from a given cypher
      *
      * @param string $initialCypher The root cypher of cadence
      * @param int $choiceIndicator Indicates which cadence to generate
      *
      * @return array $resultArray Array with cyphers in [0], [1]; [2] = root chord cypher
      */
     public function cadenceArrayGeneratorMajor ($initialCypher, $choiceIndicator) {

          # Calculate the index of root cypher, returns a string
          $chordIndex = array_search ($initialCypher, $this->chordCatalog->cypherScale);

          # Calculate position of cadence chords
          switch ($choiceIndicator) {
               case 1: # I II V I
                    $firstCypherOffset = 2;
                    $secondCypherOffset = 5;
                    break;
               case 2: # I IV V I
                    $firstCypherOffset = 5;
                    $secondCypherOffset = 2;
                    break;
               case 3: # I IV VII I
                    $firstCypherOffset = 5;
                    $secondCypherOffset = 6;
                    break;
          }
          $firstCypherIndex = $this->serialIndexCorrector (($chordIndex + $firstCypherOffset));
          $secondCypherIndex = $this->serialIndexCorrector (($firstCypherIndex + $secondCypherOffset));

          # Convert index positions into cyphers and place in array
          $resultArray = array ($this->chordCatalog->cypherScale[$firstCypherIndex], $this->chordCatalog->cypherScale[$secondCypherIndex], $initialCypher);
          return $resultArray;
     }

     public function cadenceArrayGeneratorMinor ($initialCypher, $choiceIndicator) {
          # Calculate the index of root cypher, returns a string
          $chordIndex = array_search ($initialCypher, $this->chordCatalog->cypherScaleMinor);
          # Calculate position of cadence chords
          switch ($choiceIndicator) {
               case 1: # i iidim7 V i
                    $firstCypherOffset = 2;
                    $secondCypherOffset = 5;
                    break;
               case 2: #i iv V i
                    $firstCypherOffset = 5;
                    $secondCypherOffset = 2;
                    break;
          }
          $firstCypherIndex = $this->serialIndexCorrector (($chordIndex + $firstCypherOffset));
          $secondCypherIndex = $this->serialIndexCorrector (($firstCypherIndex + $secondCypherOffset));
          # Convert index positions into cyphers and place in array
          $resultArray = array ($this->chordCatalog->cypherScaleMinor[$firstCypherIndex], $this->chordCatalog->cypherScaleMinor[$secondCypherIndex], $initialCypher);
          return $resultArray;
     }


     /*
      * Returns a generated harmonic progression from a given index
      *
      * @param str $initialIndex i or I to generate a cadence
      *
      * @return array Array with harmonic progression
      */
     public function progressionGenerator ($startingCypher) {

          # Set initial index as first chord
          $progressionArray [] = $startingCypher;

          # Generate progression sequence
          if (ctype_upper ($startingCypher)) {
               $progressionSequence = $this->progressionGeneratorMajor ($startingCypher);
          } else {
               $progressionSequence = $this->progressionGeneratorMinor ($startingCypher);
          }

          # Merge arrays and convert to Chords
          foreach ($progressionSequence as $cypher) {
               $progressionArray [] = $cypher;
          }
          return $progressionArray;
     }


     public function progressionGeneratorMajor ($startingCypher) {
          $progressionList = (mt_rand (1,2) == 1 ? $this->chordCatalog->progressionListMaj1 : $this->chordCatalog->progressionListMaj2);
          $chordOffset = 1;
          foreach ($progressionList as $newOffset) {
                $chordOffset = $this->serialIndexCorrector(($chordOffset + $newOffset));
                $progressionArray [] = $this->chordCatalog->cypherScale [$chordOffset];
          }
          return $progressionArray;
     }


     public function progressionGeneratorMinor ($startingCypher) {
          $progressionList = (mt_rand (1,2) == 1 ? $this->chordCatalog->progressionListMin1 : $this->chordCatalog->progressionListMin2);
          $chordOffset = 1;
          foreach ($progressionList as $newOffset) {
                $chordOffset = $this->serialIndexCorrector(($chordOffset + $newOffset));
                $progressionArray [] = $this->chordCatalog->cypherScaleMinor [$chordOffset];
          }
          return $progressionArray;
     }


     /* Generates a random sequence starting on a given cypher
      *
      * @param str $startingCypher The stem of the random sequence. Will be in the first position. (ie. i or IV)
      * @param int $numberOfRandomChords How many random chords in string (excluding stem chord)
      * @param str $majMinIndicator checked with ctype_upper to determine major or minor
      *
      * @return array Array with random sequence
      */
     public function getFreeSequence ($startingCypher, $numberOfRandomChords, $majMinIndicator) {
          $generatedCypher = $startingCypher;
          $freeSequence [] = $startingCypher;
          $majorOrMinor = (ctype_upper ($majMinIndicator) ? 'M' : 'm');
          for ($chordsGenerated = 1; $chordsGenerated <=$numberOfRandomChords; $chordsGenerated++) {
                    $generatedCypher = $this->chordCatalog->getNextChord ($generatedCypher, $majorOrMinor);
                    $freeSequence [] = $generatedCypher;
          }
          return $freeSequence;
     }


     /*
      * Gets serial index of a note
      *
      * @param string $note The note you want to check
      *
      * @return int The serial index of the note
      */
     public function getNoteSerialIndex ($note) {
          $noteIndex = array_search ($note, $this->chordCatalog->serialScale);
          return $noteIndex;
     }


    /*
     * Makes sure serial index is between 1 and 12
     *
     * @param int $serialIndex The index to be checked
     *
     *@return int The equivalent index between 1-12
     */
     public function serialIndexCorrector ($serialIndex) {
          if ($serialIndex <= 0) {
               $serialIndex += 12;
          }
          if ($serialIndex > 12) {
               $serialIndex -= 12;
          }

          return $serialIndex;
     }


    /*
      * Takes the sequence of chords and sends it somewhere for processing
      *
      * @param array $sequence Sequence to be processed
      */
     public function formatSequence ($sequence) {

          if ($this->radioButtonOption == 'chord') {
               # Organize array into blocks and produce HTML
               $html = $this->organizeSequenceToHTML ($sequence,$this->numberOfBlocks);
               echo $html;
          } else {
               # Send to 4 voice Harmony Generator
               $this->harmonyLogic = new harmonyLogic;
               $this->harmonyLogic->getHarmony ($sequence);
          }
     }


    /*
     * Organizes string of chords into lines and blocks
     *
     * @param string $sequence Generated chords to be processed
     * @param int $numberOfBlocks Number of blocks of chords
     *
     * @return string $html HTML of formatted chords
     */
     private function organizeSequenceToHTML($sequence,$numberOfBlocks)
     {
          # Start the HTML
          $html='<head></head>
          <body>';

          $numberOfLines = ($this->chordsPerBlock/8);
          $numberOfColumns = ($this->chordsPerBlock/4);

          # Process array into blocks, lines, columns
          $arrayPosition=0;
          for($block=1; $block<=$numberOfBlocks; $block++) {
               for($line=1; $line<=$numberOfLines; $line++) {
                    for ($column = 1; $column <= $numberOfColumns; $column++)
                    {
                         $html .= $sequence[$arrayPosition] . ', ';
                         $arrayPosition++;
                    }
                    $html.="<br />\n";
               }
               if  ($block != $numberOfBlocks){
               $html .= "<br />\n";
               }
          }
          return $html;
    }


}

?>