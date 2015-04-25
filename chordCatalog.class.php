<?php
class chordCatalog {

    /*
     * Constructs a chord matrix
     */
    public function __construct()
    {
        # Creates a major progression matrix
        $this->cypherMatrix = array(
            'I'         => array ('V', 'vi', 'ii', 'iii', 'VI6'),
            'ii'        => array ('iii', 'V', 'vi','I', 'VII65'),
            'II6'       => 'V',
            'iii'       => array ('I', 'V', 'IV'),
            'III6'      => 'vi',
            'IV'        => array ('V', 'I', 'bVII', 'vi', 'II6'),
            'V'         => array ('I', 'ii', 'iii', 'III6'),
            'vi'        => array ('ii', 'viidim', 'IV'),
            'VI6'       => 'ii',
            'bVII'      => 'IV',
            'viidim'    => 'I',
            'VII65'     => 'iii'
            
        );
        
       
        # Creates a 12 tone MAJOR cypher scale
        $this->cypherScale = array(
            '1' => 'I',
            '2' => 'VI6',
            '3' => 'ii',
            '4' => 'VII65',
            '5' => 'iii',
            '6' => 'IV',
            '7' => 'II6',
            '8' => 'V',
            '9' => 'III6',
            '10' => 'vi',
            '11' => 'bVII',
            '12' => 'viidim',
        );
        
        
        
        # Creates a MINOR cypher progression matrix
        $this->cypherMatrixMinor = array(
            'i' => array ('V', 'VI', 'iv', 'iidim56', 'I6'),
            'I6' => array ('iv', 'II6'),
            'iidim56' => 'V',
            'II' => 'v',
            'II6' => 'v',
            'III' => array ('i', 'V', 'iv'),
            'iv' => array ('V', 'i', 'iidim56', 'III'),
            'IV6' => 'VII',
            'v' => array ('III', 'V'),
            'V'=> 'i',
            'V65' => 'i',
            'VI' => array ('III', 'VII', 'iv'),
            'VII' => 'V',   
        );

        
        
         # Creates a 12 tone MINOR cypher scale
        $this->cypherScaleMinor = array(
            '1' => 'i',
            '2' => 'V65',
            '3' => 'iidim7',
            '4' => 'III', 
            '5' => 'I6', # Doesn't exist in minor scale
            '6' => 'iv',
            '7' => 'II6', # Another first inversion to cover exception
            '8' => 'V',
            '9' => 'VI',
            '10' => 'IV6',
            '11' => 'VII',
            '12' => 'V65',
        );
        
        # Creates a chord progression matrix
        $this->fullChordMatrix = array(
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
        
        # Creates a 12 tone scale array
        $this->serialScale = array(
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
        
        # Creates a 12 tone scale array in MAJOR
        $this->serialScaleMajor = array(
            '1' => 'C',
            '2' => 'C#',
            '3' => 'D',
            '4' => 'D#',
            '5' => 'E',
            '6' => 'F',
            '7' => 'F#',
            '8' => 'G',
            '9' => 'G#',
            '10' => 'A',
            '11' => 'A#',
            '12' => 'B',
            
        );
        
        
        
        
        
        $this->FIndexMajor = array(
            'I' => 'F',
            'ii' => 'g',
            'iii' => 'a',
            'IV' => 'Bb',
            'V' => 'C',
            'vi' => 'd',
            'viidim' => 'edim',
            
        );
        
        $this->GIndexMajor = array(
            'I' => 'G',
            'ii' => 'a',
            'iii' => 'b',
            'IV' => 'C',
            'V' => 'D',
            'vi' => 'e',
            'viidim' => 'f#dim6',
        );
        
        $this->GIndexMinor = array(
            'i'      => 'G',
            'iidim7' => 'adim7',
            'III'    => 'Bb',
            'iv'     => 'c7',
            'V'      => 'D',
            'VI'     => 'Eb',
            'VII' => 'f',
        );
        
        
        $this->CIndexMajor = array(
            'I'      => 'C',
            'ii'     => 'd7',
            'II6'    => 'D6',
            'iii'    => 'e',
            'III6'   => 'E6',
            'IV'     => 'F',
            'V'      => 'G',
            'vi'     => 'a',
            'VI6'    => 'A6',
            'bVII'   => 'Bb',
            'viidim' => 'bdim',
            'VII65'  => 'bhalfdim'
        );
        
         $this->AbIndexMajor = array(
            'I'      => 'Ab',
            'ii'     => 'bb',
            'II6'    => 'Bb6',
            'iii'    => 'c',
            'III6'   => 'C6',
            'IV'     => 'Db',
            'V'      => 'Eb',
            'vi'     => 'f',
            'VI6'    => 'F6',
            'bVII'   => 'Gb',
            'viidim' => 'gdim6',
            'VII65'  => 'ghalfdim',
        );
        
        
        $this->AIndexMinor = array(
            'i'       => 'a',
            'I6'      => 'A6',
            'iidim7'  => 'bdim7',
            'iidim56' => 'bdim56',
            'II'      => 'B',
            'II6'     => 'B6',
            'III'     => 'C',
            'iv'      => 'd',
            'IV6'     => 'D6',
            'v'       => 'e',
            'V'       => 'E',
            'V65'     => 'E65',
            'VI'      => 'F',
            'VII'     => 'G',
        );
        
        $this->CIndexMinor = array(
            'i'       => 'c',
            'I6'      => 'C6',
            'iidim7'  => 'ddim7',
            'iidim56' => 'ddim56',
            'II'      => 'D',
            'II6'     => 'D6',
            'III'     => 'Eb',
            'iv'      => 'f',
            'IV6'     => 'D6',
            'v'       => 'g',
            'V'       => 'G',
            'V65'     => 'G65',
            'VI'      => 'Ab',
            'VII'     => 'Bb',
            
        );
    
    # Progressions ending on same degree as start
        $this->progressionListMaj1 = array ("5", "6", "5", "5", "5", "5", "5"); # a "6-5" progression
        $this->progressionListMaj2 = array ("7", "2", "7", "1", "-5"); # a "5-2" progression
        
        $this->progressionListMin1 = array ("5", "5", "5", "5", "6", "5", "5"); # 6-5
        $this->progressionListMin2 = array ("7", "1", "7", "2", "7"); # 5-2
          
    }
    
    
    /*
     * Gets a matching chord from the progression matrix from a supplied chord
     *
     * @param string $stemChord Chord which will trigger another chord
     * @param string $startingKey M or m for major or minor chord lookup
     *
     * @return string $matchingChord Returns next chord
     */
    public function getNextChord($stemChord, $startingKey){
        
        # Gets corresponding entry for $stemChord

        if (ctype_upper ($startingKey)) {
                $matchingChord = $this->cypherMatrix[$stemChord];
          }
          else {
                $matchingChord = $this->cypherMatrixMinor[$stemChord];
          }
        
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