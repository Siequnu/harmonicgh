<?php
class chordCatalog {

    /*
     * Constructs a chord matrix
     */
    public function __construct()
    {
        # Creates a chord matrix
        $chordMatrix = array(
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
        
        $this->chordMatrix = $chordMatrix;
    }
    
    
    /*
     * Gets a matching chord from the matrix from a supplied matrix
     *
     * @param string $stemChord Chord which will trigger another chord
     *
     * @return string $matchingChord Returns next chord
     */
    public function getNextChord($stemChord){
        
        # Gets corresponding entry for $stemChord
        $matchingChord = $this->chordMatrix[$stemChord];
        
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
        # Move the internal pointer to the end of the array
        end($array);
        # Fetches key of element pointed by internal pointer
        $lastIndexKey = key($array);
        # Chooses random index
        $randomMatchingChord = rand(0,$lastIndexKey);
        # Dives into the matrix and retrieves the randomly picked entry
        $matchingChord = $array[$randomMatchingChord];
        return $matchingChord;
    }
 
    
}
?>