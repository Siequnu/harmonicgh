<?php

class chordGenerator {
    
     # Class defaults
     private $initialChord = 'C';
     private $chordsPerBlock = 32;
     
     public function __construct()
     {
          # Do nothing   
     }
    
    /*
     * Main entry into program 
     */
     public function main()
     {
          # Generate form if not submitted
          if (!isSet ($_POST['msg'])) {
               $html=$this->generateForm('How many verses', 'Which starting chord?', 'msg', 'initialchord', 'Magically Generate Harmony!');
               echo $html;
               return;
          }
        
          # Validate and assign form data, if nothing was entered in #-of-blocks default is 1
          if ($_POST['initialchord'] !== "C") {
               if ($_POST['initialchord'] !== "c") {
                    echo "Please enter C or c as starting chord";
                    die;
               }
          }
          
          $numberOfBlocks = $_POST['msg'];
          $testBlocks = $numberOfBlocks+1;
          if ($testBlocks == 1) {
               $numberOfBlocks = 1;
          }
          $this->initialChord = $_POST['initialchord'];
          
          # Calculate total number of chords needed
          $totalchords = $numberOfBlocks * $this->chordsPerBlock;
          $sequence = array ();
          
          # Generate sequence of chords
          for ($cycleNumbers = 0; $cycleNumbers < $numberOfBlocks; $cycleNumbers++) {
               $verseBlock = $this->generateChords ($this->initialChord);
               foreach ($verseBlock as $finalChord) {
                    $sequence [] = $finalChord;
               }
          }
     
          # Organize array into blocks and produce HTML
          $html = $this->organizeSequence ($sequence,$numberOfBlocks);
          echo $html;
          
     }
    
     
    
     /*
     * Loop that generates a verse of 32 chords starting with a given initial chord
     * 
     * @param string $firstChord Initial chord
     * 
     * @return string String of logically generated chords 
     */
     public function generateChords ($firstChord)
     {
         
          include_once 'chordCatalog.class.php';
          $chordCatalog = new chordCatalog;
          $this->chordCatalog = $chordCatalog;     
          
          # Determine Tonalities       
          # Determine if first chord M or m, convert into cipher 
          if (ctype_upper ($firstChord)) {
               $startingCypher = 'I';
               $startingKey = 'M';
               $dominantKey = 'M';
               $subMediantKey = 'm';
               $subMediantCypher = "i";
               $tonicTonality = $firstChord;
               $tonicTonalityIndex = array_search ($tonicTonality, $chordCatalog->serialScaleMajor);
               $dominantTonalityIndex = $this->serialIndexCorrector (($tonicTonalityIndex + 7));
               $dominantTonality = $chordCatalog->serialScaleMajor[($this->serialIndexCorrector (($tonicTonalityIndex + 7)))];
               $subMediantTonalityIndex = $this->serialIndexCorrector (($tonicTonalityIndex + 9));
               $subMediantTonality = $chordCatalog->serialScaleMajor[$subMediantTonalityIndex];
          }
          else {
               $startingCypher = 'i';
               $startingKey = 'm';
               $dominantKey = 'm';
               $subMediantKey = 'M';
               $subMediantCypher = "I";
               $tonicTonality = $firstChord;
               $tonicTonalityIndex = array_search ($tonicTonality, $chordCatalog->serialScale);
               $dominantTonalityIndex = $this->serialIndexCorrector (($tonicTonalityIndex + 7));
               $dominantTonality = $chordCatalog->serialScale[($this->serialIndexCorrector (($tonicTonalityIndex + 7)))];
               $subMediantTonalityIndex = $this->serialIndexCorrector (($tonicTonalityIndex + 8));
               $subMediantTonality = $chordCatalog->serialScale[$subMediantTonalityIndex];          
          }
          
          # Initialize an empty array and set the first Chord
          unset ($this->sequence);
          $this->sequence[] = $firstChord;

          # Get first cadence
          $firstCadenceArray = $this->cadenceGenerator ($startingCypher);
          
          # Convert cyphers into chords
          $firstCadenceArray = $this->chordCatalog->convertCypherIntoChord ($firstCadenceArray, $startingCypher, 'CIndexMajor', 'CIndexMinor');
          
          # Add cadence to sequence
          $this->addArrayToMainSequence ($firstCadenceArray);
        
          # Get dominant Cadence 
          $dominantCypher = (ctype_upper ($dominantKey) ? 'I' : 'i');
          $dominantCadenceArray = $this->cadenceGenerator ($dominantCypher);
          
          # Convert cyphers into chords
          $dominantCadenceArray = $this->chordCatalog->convertCypherIntoChord ($dominantCadenceArray, $dominantCypher, 'GIndexMajor', 'GIndexMinor');
          
          # Add cadence to sequence, cadence comes a 3 part array so add dominant chord before it
          $this->sequence[] = $dominantTonality;
          
          $this->addArrayToMainSequence ($dominantCadenceArray);
          
          # Get a harmonic progression for the next line of chords
          $modulationGoal = ((ctype_upper ($subMediantKey)) ? "vi" : "VI");
          $progressionSequence = $this->progressionGenerator ($startingCypher);
          
          # Convert cyphers into chords
          $progressionSequence = $this->chordCatalog->convertCypherIntoChord ($progressionSequence, $startingCypher, 'CIndexMajor', 'CIndexMinor');
          
          # Generate cadence for the end         
          $modulationGoal = ((ctype_upper ($modulationGoal)) ? "i" : "I" );
          $modulationCadence = $this->cadenceGenerator ($modulationGoal);
          
          # Convert cyphers into chords
          $modulationCadence = $this->chordCatalog->convertCypherIntoChord ($modulationCadence, $modulationGoal, 'AbIndexMajor', 'AIndexMinor');

          #Merge progression and modulation, and merge this to total sequence
          $indexToInsertCadence = 5; 
          foreach ($modulationCadence as $chord) {
               $progressionSequence [$indexToInsertCadence] = $chord;
               $indexToInsertCadence++;
          }
          
          $this->addArrayToMainSequence ($progressionSequence);
          
          # Next line is freely generated in submediant
          $freeSequence [] = $subMediantCypher;
          
          # Get a free choice for 4 of the next line of chords
          $generatedCypher = $subMediantCypher;
          if (ctype_upper ($subMediantCypher)) {
               for ($chordsGenerated = 0; $chordsGenerated <=3; $chordsGenerated++) {
                    $generatedCypher = $this->chordCatalog->getNextChord ($generatedCypher, "M");
                    $freeSequence [] = $generatedCypher;
               }  
          }
          else {
               for ($chordsGenerated = 0; $chordsGenerated <=3; $chordsGenerated++) {
                    $generatedCypher = $this->chordCatalog->getNextChord ($generatedCypher, "m");
                    $freeSequence [] = $generatedCypher;
               }    
          }
     
          # Convert cyphers into chords
          $freeSequence = $this->chordCatalog->convertCypherIntoChord ($freeSequence, $startingCypher, 'AIndexMinor', 'AbIndexMajor');
                    
          # Generate cadence for the end of this line         
          $initialIndex = (ctype_upper ($startingKey) ? 'I' : 'i');
          $modulationCadence = $this->cadenceGenerator ($initialIndex);
          
          # Convert cyphers into chords
          $modulationCadence = $this->chordCatalog->convertCypherIntoChord ($modulationCadence, $startingKey, 'CIndexMajor', 'CIndexMinor');
          
          # Merge cadence starting index 4 of sequence, and merge this to total sequence
          $indexToInsertCadence = 5; 
          foreach ($modulationCadence as $chord) {
               $freeSequence [$indexToInsertCadence] = $chord;
               $indexToInsertCadence++;
          }
          
          $this->addArrayToMainSequence ($freeSequence);
          
          unset ($freeSequence); 
          
          
          # Last line is freely generated in Tonic then cadence at end
          $freeSequence [] = $startingCypher;
          
          # Get a random chord for 4 of the next chords
          $generatedCypher = $startingCypher;
          if (ctype_upper ($startingCypher)) {
               for ($chordsGenerated = 0; $chordsGenerated <=3; $chordsGenerated++) {
                    $generatedCypher = $this->chordCatalog->getNextChord ($generatedCypher, "M");
                    $freeSequence [] = $generatedCypher;
               }  
          }
          else {
               for ($chordsGenerated = 0; $chordsGenerated <=3; $chordsGenerated++) {
                    $generatedCypher = $this->chordCatalog->getNextChord ($generatedCypher, "m");
                    $freeSequence [] = $generatedCypher;
               }    
          }
          # Convert cyphers into chords
          $freeSequence = $this->chordCatalog->convertCypherIntoChord ($freeSequence, $startingCypher, 'CIndexMajor', 'CIndexMinor');
                    
          # Generate cadence for the end of this line         
          $initialIndex = (ctype_upper ($startingCypher) ? 'I' : 'i');
          $modulationCadence = $this->cadenceGenerator ($initialIndex);
          
          # Convert cyphers into chords
          $modulationCadence = $this->chordCatalog->convertCypherIntoChord ($modulationCadence, $startingKey, 'CIndexMajor', 'CIndexMinor');
          
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
     * Generates a 2 part form
     *
     * @param str $prompt1 Message prompt
     * @param str $prompt2 Second Prompt
     * @param str $name1 Name of data entered
     * @param str $name2 Second Name
     * @param str $submitValue Message on submit button
     *
     * @return string
     */
     private function generateForm ($prompt1, $prompt2, $name1, $name2, $submitValue) {
          $formhtml='<html>
          <head></head>
          <body>
            
          <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
            
          ' . $prompt1 . ': <input type="text" name=' . $name1 . ' size="10">
          ' . $prompt2 . ': <input type="text" name=' . $name2 . ' size="10">
            
          <input type="submit" value="' . $submitValue . '">
            
          </form>
          
          </body>
            
          </html>';
          return $formhtml;
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
          # Determine if Major or Minor cadence
          if (ctype_upper ($initialIndex)) {   # 0 = minor initialCypher, 1 = Major initial cypher
               # Pick random cadence style and generate array
               $randomChoice = mt_rand (1,3);
               $resultArray = $this->cadenceArrayGeneratorMajor ($initialIndex, $randomChoice);
          } else {
               $randomChoice = mt_rand (1,2);
               $resultArray = $this->cadenceArrayGeneratorMinor ($initialIndex, $randomChoice);               }
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
          } else
          {
               $progressionSequence = $this->progressionGeneratorMinor ($startingCypher);
          }
          
          
          # Merge arrays and convert to Chords
          foreach ($progressionSequence as $cypher) {
               $progressionArray [] = $cypher;
          }
          return $progressionArray;     
     }
     
     
     public function progressionGeneratorMajor ($startingCypher) {
          $randomChoice = mt_rand (1,2);
          switch ($randomChoice) {
               case "1":
                    $chordOffset = 1;
                    foreach ($this->chordCatalog->progressionListMaj1 as $newOffset) {
                         $chordOffset = $this->serialIndexCorrector(($chordOffset + $newOffset));
                         $progressionArray [] = $this->chordCatalog->cypherScale [$chordOffset];
                    }
                    break;
               case "2":
                    $chordOffset = 1;
                    foreach ($this->chordCatalog->progressionListMaj2 as $newOffset) {
                         $chordOffset = $this->serialIndexCorrector(($chordOffset + $newOffset));
                         $progressionArray [] = $this->chordCatalog->cypherScale [$chordOffset];
                    }
                    break;
               }
          return $progressionArray;         
     }
     
     
     public function progressionGeneratorMinor ($startingCypher) {
          $randomChoice = mt_rand (1,2);
          switch ($randomChoice) {
               case "1":
                    $chordOffset = 1;
                    foreach ($this->chordCatalog->progressionListMin1 as $newOffset) {
                         $chordOffset = $this->serialIndexCorrector(($chordOffset + $newOffset));
                         $progressionArray [] = $this->chordCatalog->cypherScaleMinor [$chordOffset];
                    }
                    break;
               case "2":
                    $chordOffset = 1;
                    foreach ($this->chordCatalog->progressionListMin2 as $newOffset) {
                         $chordOffset = $this->serialIndexCorrector(($chordOffset + $newOffset));
                         $progressionArray [] = $this->chordCatalog->cypherScaleMinor [$chordOffset];
                    }
                    break;
               }
          return $progressionArray;         
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
     * Organizes string of chords into lines and blocks
     *
     * @param string $sequence Generated chords to be processed
     * @param int $numberOfBlocks Number of blocks of chords
     *
     * @return string $html HTML of formatted chords
     */ 
     private function organizeSequence($sequence,$numberOfBlocks)
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