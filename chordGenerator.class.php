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
        
          # Retrieve form data and assign it
          $numberOfBlocks = $_POST['msg'];
          $this->initialChord = $_POST['initialchord'];
          
          # Calculate total number of chords needed
          $totalchords = $numberOfBlocks * $this->chordsPerBlock;
          
          # Generate sequence of chords
          $sequence = $this->generateChords ($totalchords, $this->initialChord);
        
          # Organize array into blocks and produce HTML
          $html = $this->organizeSequence ($sequence,$numberOfBlocks);
          echo $html;
     }
    
    
     /*
     * Loop that generates chords starting with a given initial chord
     * 
     * @param int $totalchords Positive integer representing wanted number of chords
     * @param string $firstChord Initial chord
     * 
     * @return string String of logically generated chords 
     */
     public function generateChords ($totalChords, $firstChord)
     {
          include 'chordCatalog.class.php';
          $chordCatalog = new chordCatalog;
          $this->chordCatalog = $chordCatalog;
        
          # Convert first chord into cipher
          //DEV system to convert maj min chord into cypher
          $startingCypher = $firstChord;
          $startingCypher = 'I';
          
          # Initialise and populate an array of cyphers
          $this->sequence = array ();
          $this->sequence[0] = $startingCypher;
          for ($generationCycles = 1; $generationCycles < $totalChords; $generationCycles++)
          {   
               # Generate new chord based on existing array and write to new index
               $this->sequence[] = $chordCatalog->getNextChord(end($this->sequence));
          }
          # Set last chord same as first
          $this->sequence[($this->chordsPerBlock-1)] = $this->sequence[0];
          
          # Populate sequences and cadences from given indices
          
          #$neededCadences = array ('0', ($this->chordsPerBlock-1) )
          
          $index = '0';
          $this->cadenceGenerator ($index);
 
          # DEV Convert sequence of cyphers into chords
              
          return $this->sequence;
     }
     
     
     /*
      * Inserts a generated cadence into the sequence from a given index
      *
      * @param string $initialIndex Index of cypher, provides root and position
      */    
     public function cadenceGenerator ($initialIndex) {
          $initialCypher = $this->sequence[$initialIndex];
          
          # Pick random cadence style and generate array
          $randomChoice = mt_rand (1,3);
          $resultArray = $this->cadenceArrayGenerator ($initialCypher, $randomChoice);
          
          # Merge result with existing sequence starting after given index
          foreach ($resultArray as $generatedCypher) {
               $cypherIndex = array_search($generatedCypher, $resultArray);
               $this->sequence[($initialIndex+1+$cypherIndex)] = $generatedCypher;
          }
     }
     
    
     /*
      * Generates a 3 chord cadence from a given cypher
      *
      * @param string $initialCypher The root cypher of cadence
      * @param int $choiceIndicator Indicates which cadence to generate
      *
      * @return array $resultArray Array with cyphers in [0], [1]; [2] = root chord cypher
      */
     public function cadenceArrayGenerator ($initialCypher, $choiceIndicator) {
          # Calculate the index of root cypher, returns a string
          $chordIndex = array_search ($initialCypher, $this->chordCatalog->cypherScale); 
          # Calculate position of cadence chords
          switch ($choiceIndicator) {
               case 1:
                    $firstCypherOffset = 2;
                    $secondCypherOffset = 5;
                    break;
               case 2:
                    $firstCypherOffset = 5;
                    $secondCypherOffset = 2;
                    break;
               case 3:
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
               $serialIndex = ($serialIndex + 12);
          }
          if ($serialIndex > 12) {
               $serialIndex = ($serialIndex - 12);
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