<?php
class chordCatalog {

    /*
     * Constructs a chord matrix
     */
    public function __construct()
    {
        # Creates a cypher progression matrix
        $cypherMatrix = array(
            'I' => array('V', 'vi', 'ii', 'iii'),
            'ii' => array('iii', 'V', 'V', 'vi','I'),
            'iii' => array('I', 'V', 'IV'),
            'IV' => array ('V', 'I', 'bVII', 'vi'),
            'V' => array( 'I', 'ii', 'iii'),
            'vi' => array('ii', 'viidim', 'IV'),
            'bVII' => 'IV',
            'viidim' => 'iii', 
        );
        $this->cypherMatrix = $cypherMatrix;
        
        # Creates a 12 tone cypher scale
        $cypherScale = array(
            '1' => 'I',
            '2' => 'bii',
            '3' => 'ii',
            '4' => 'biii',
            '5' => 'iii',
            '6' => 'IV',
            '7' => 'bV',
            '8' => 'V',
            '9' => 'bvi',
            '10' => 'vi',
            '11' => 'bVII',
            '12' => 'viidim',
        );
        $this->cypherScale = $cypherScale;
        
        # Creates a chord progression matrix
        $fullChordMatrix = array(
            'c' => array('g6', 'am', 'dmaj7', 'dm'),
            'dm' => array('emaj7', 'g', 'g6', 'amaj7'),
            'dmaj7' => array('g', 'g6'),
            'em' => array('c', 'emaj7'),
            'emaj7' => 'am',
            'g' => array('g7', 'c', 'dmaj7', 'em'),
            'g6' => array('dmaj7', 'amaj7'),
            'g7' => 'c',
            'am' => array('dm', 'bmaj7'),
            'amaj7' => array('dm', 'bb'),
            'bb' => 'g6',
            'bmaj7' => 'emaj7', 
        );
        $this->fullChordMatrix = $fullChordMatrix;
        
        # Creates a 12 tone scale array
        $serialScale = array(
            '1' => 'c',
            '2' => 'c#',
            '3' => 'd',
            '4' => 'd#',
            '5' => 'e',
            '6' => 'f',
            '7' => 'f#',
            '8' => 'g',
            '9' => 'g#',
            '10' => 'a',
            '11' => 'a#',
            '12' => 'b',
        );
        $this->serialScale = $serialScale;
        
    }
    
    
    /*
     * Gets a matching chord from the progression matrix from a supplied chord
     *
     * @param string $stemChord Chord which will trigger another chord
     *
     * @return string $matchingChord Returns next chord
     */
    public function getNextChord($stemChord){
        
        # Gets corresponding entry for $stemChord
        $matchingChord = $this->cypherMatrix[$stemChord];
        
        # Tests if $matchingChord is itself an array and if TRUE
        # retrieves random entry from its array   
        while (is_array ($matchingChord)==TRUE) {
                $matchingChord = $this->getRandomChordFromArray ($matchingChord);    
        }
        return $matchingChord;
    }
    
     
    /*
     * Gets a random chord from an ordered array
     *
     * @param array $array The array containing chords
     *
     * @return string $randomMatchingChord Random chord from array
     */
    public function getRandomChordFromArray($array){
        end($array);
        $lastIndexKey = key($array);
        $randomMatchingChord = rand(0,$lastIndexKey);
        $matchingChord = $array[$randomMatchingChord];
        return $matchingChord;
    }
 
     
}

?>