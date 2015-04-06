<?php

class chordGenerator {
    
    # Class defaults
    private $initialChord = 'c';
    private $chordsPerBlock = 16;

    
    public function __construct()
    {
        # Do nothing
    }
    
    
    /*
     * Generates the form
     *
     * @return string
     */
    private function generateForm () {
         $formhtml='<html>
    
    
            <head></head>
            <body>
            
            <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
            
            How many verses: <input type="text" name="msg" size="30">
            
            <input type="submit" value="Magically Generate Harmony!">
            
            </form>
            
            </body>
            
            </html>';
            return $formhtml;
    }
    
    
    /*
     * Changes the initial chord
     *
     * @param string $initialChord Sets the initial chord
     */
    public function setInitialChord($initialChord)
    {
        $this->initialChord = $initialChord;
    }
    
    
    /*
     * Main entry into program 
     */
    public function main()
    {
        # Generate form if not submitted
        if (!isSet ($_POST['msg'])) {
            $html=$this->generateForm();
            echo $html;
            return;
        }
        
        # Retrieve form data and assign it
        $numberOfBlocks = $_POST['msg'];
        
        # Calculate total number of chords needed
        $totalchords = $numberOfBlocks * $this->chordsPerBlock;
        
        # Check if initial chord is to be set otherwise set to default 'c'
        if (isSet ($_GET["initialchord"])) {
            $this->setInitialChord ($_GET["initialchord"]);
        }
        else {
            $this->setInitialChord ('c');
        }
        
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
     * 
     */
    private function generateChords ($totalChords, $firstChord)
    {
        # Initialise chordCatalog.php
        include 'chordCatalog.class.php';
        $chordCatalog1 = new chordCatalog;
        
        # Initialise an array of chords
        $this->sequence = array ();

        # Set the first item to be the first chord
        $this->sequence[] = $firstChord;
        
        
        for ($generationCycles = 1; $generationCycles <= $totalChords; $generationCycles++)
        {   
            # Generate new chord based on existing array
            # Write the newly generated chord in to the array
            $this->sequence[] = $chordCatalog1->getNextChord(end($this->sequence));
        }
        
        return $this->sequence;
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
        $html="\n";
        
        $numberOfLines = sqrt($this->chordsPerBlock);
        $numberOfColumns = sqrt($this->chordsPerBlock);
        
        # Organize chords into pattern
        
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