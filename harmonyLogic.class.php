<?php

class harmonyLogic {

    public function getHarmony ($sequenceOfChords) { # Array ( [0] => C [1] => F [2] => G [3] => C .....)
        include_once 'harmonyCatalog.class.php';
        $this->harmonyCatalog = new harmonyCatalog;
        $this->sequenceOfChords = $sequenceOfChords;
        
        # Set a simulated array for testing purposes
        #$this->sequenceOfChords = array ( 'C', 'F', 'G', 'C' );
        #$this->sequenceOfChords = array ('C', 'F', 'bdim', 'C', 'G', 'C', 'f#dim6', 'G', 'C', 'G', 'a', 'e', 'F', 'bdim7', 'E', 'a', 
        #'a', 'd', 'E', 'a', 'd', 'F', 'G', 'C', 
        #'C', 'd7', 'G', 'E6', 'a', 'F', 'G', 'C', );  
        
        # Insert first chord positions into harmony array
        $this->sequenceOfHarmony[0] = ($this->getPositionOfFirstChord ($this->sequenceOfChords[0]));  
        
        # Go through array of chords and generate keyboard positions for each chord
        $numberOfChordsToHarmonize = (count($this->sequenceOfChords)-1);
        $positionInSequenceOfChords = 1;
        $positionInSequenceOfHarmony = 0;
        
        for ($cycles = 1; $cycles <= $numberOfChordsToHarmonize; $cycles++) {
            
            # Generate 4 part position for chord
            $harmonizedChord = $this->harmonizeNextChord($positionInSequenceOfChords, $positionInSequenceOfHarmony);
            
            $positionInSequenceOfHarmony++;
            $positionInSequenceOfChords++;
            
            # Merge to existing array of Harmony
            foreach ($harmonizedChord as $notePosition) {
                $this->sequenceOfHarmony[$positionInSequenceOfHarmony][] = $notePosition;    
            }   
        }
        
        # Make result array readable by Human
        $html = $this->createResultHTML ($this->sequenceOfHarmony);
        
        return $html;
    }
       
    /*
     * Gets an array of calculated positions for the next chord
     *
     * @param int $positionInSequenceOfChords Position in chord array
     * @param int $positionInSequenceOfHarmony Position in harmony array
     *
     * @return array Array with positions for new chord
     */    
    public function harmonizeNextChord ($positionInSequenceOfChords, $positionInSequenceOfHarmony) {
        # Get notes of the next chord
        $secondChordNotes = $this->harmonyCatalog->harmonyIndex [$this->sequenceOfChords[$positionInSequenceOfChords]];
        
        # Find matching places on Keyboard for all 4 chord notes
        $possibleKeys = $this->findMatchingSoupForChord($secondChordNotes);
        
        # Place new bass at closest note to previous bass
        $closestMatchToBass = $this->getClosestMatch ($possibleKeys[0], $this->sequenceOfHarmony[$positionInSequenceOfHarmony][0]);
        
        # If higher than 24 (start of third octave), get same key an octave down
        $closestMatchToBass = ($closestMatchToBass >= 24 ? $closestMatchToBass - 12 : $closestMatchToBass);
        
        # Merge remaining positions into possibilitySoup for remaining 3 notes (T, A, B)
        for ($possibleNoteIndex = 1; $possibleNoteIndex <= 3; $possibleNoteIndex++) {            
            foreach ($possibleKeys[$possibleNoteIndex] as $possiblePosition) {
                $possibilitySoup [] = $possiblePosition;
            }
        }
        
        # Get closest notes from this soup for Bass [0] Tenor [1] Alto [2] and Soprano [3]
        $noteInChord= 0 ;
        unset ($matchArray);
        foreach ($this->sequenceOfHarmony[$positionInSequenceOfHarmony] as $notePosition) {
            $closestMatch = $this->getClosestMatch ($possibilitySoup, $notePosition);
            $matchArray[$noteInChord] = $closestMatch;
            
            # Remove chosen option from soup
            $possibilitySoup = array_diff($possibilitySoup, array($closestMatch));
            
            # Increment position counter
            $noteInChord++;
        }
        # Put already chosen Bass voice into array
        $matchArray[0] = $closestMatchToBass;         
        
        # Make sure all notes of chord are included
        # go through array, if a repeated note is found, keep going down till an empty slot is found
        while (count(array_unique($matchArray))<count($matchArray)) {
            // Array has duplicates
            
            # Takes an array and returns an array of duplicate items
            $arrayWithDuplicates = array_unique( array_diff_assoc( $matchArray, array_unique( $matchArray ) ) );
            foreach ($arrayWithDuplicates as $key => $index) {
                # Action to do with duplicate keys
                $matchArray[$key] = (($matchArray[$key]) -12);
            }  
        }
        
        # Sort array in ascending order
        sort($matchArray);
        
        return $matchArray;
    }
    
    
    /*
     * Find all matching keys on keyboard for each note of a chord
     *
     * @param array $chordNotes Array containing the 4 notes of a chord
     *
     * return array Array with all the possible keys for each note
     */
    public function findMatchingSoupForChord ($chordNotes) {
        
        # Find matching places on Keyboard for all 4 chord notes
        $noteInChord = 0;
        
        foreach ($chordNotes as $notes) {
            $possibleKeys [$noteInChord] = array_keys ($this->harmonyCatalog->keyboardLayout, $notes);
            $noteInChord++;
        }
        return $possibleKeys;
    }
    
    
    /*
     * Positions the first chord on the keyboard
     *
     * @param str $firstChord First chord (ie C) to be positioned
     *
     * @result array Array with keyboard positions (Array ( [0] => 19 [1] => 23 [2] => 26 [3] => 31 )
     */
    public function getPositionOfFirstChord ($firstChord) {
        
        # Find what notes Chord has
        $currentChordNotes = $this->harmonyCatalog->harmonyIndex [$firstChord];
        
        # Find matching places on Keyboard for chord notes
        $possibleKeys = $this->findMatchingSoupForChord ($currentChordNotes);
        
        # Place base in second octave
        $chordPositionArray[0] = $possibleKeys[0][1];  // = $possibleKeys[bassNoteInChord][secondOctave]
        
        # Place all other notes consecutively on third octave or above
        $octave = 2;
        $lastLocationOnArray = 0;
        for ($noteInChord = 1; $noteInChord <= 3; $noteInChord++) {
            $chordPositionArray[$noteInChord] = $possibleKeys[$noteInChord][$octave];
            
            # Check if chord has been placed below last chord
            if ($chordPositionArray[$noteInChord] < $lastLocationOnArray) {
                
                # Add it up an octave
                $octave = $octave + 1;
                $chordPositionArray[$noteInChord] = $possibleKeys[$noteInChord][$octave];
                
                # Reset the octave positioning
                $octave = $octave - 1;
            }
        
            #Update last location
            $lastLocationOnArray = $chordPositionArray[$noteInChord];
        }
        return $chordPositionArray; // With C Major returns Array ( [0] => 12 [1] => 28 [2] => 31 [3] => 36 )
    }
    
    
    /*
     * Gets closest number to numbers in an array
     *
     * @param array $array Array to be searched
     * @param int $nr Number to be compared
     *
     * return int Returns element from array that was closest
     */
    public function getClosestMatch($array, $nr) {
        
        sort($array);      // Sorts the array from lowest to highest

        # Will contain difference=>number (difference between $nr and the closest numbers which are lower than $nr)
        $diff_nr = array();

        # Traverse the array with numbers
        # Stores in $diff_nr the difference between the number immediately lower / higher and $nr; linked to that number
        foreach($array AS $num){
            
            if($nr > $num) $diff_nr[($nr - $num)] = $num;
            
                else if($nr <= $num){
                
                    # If the current number from $array is equal to $nr, or immediately higher, stores that number and difference
                    # and stops the foreach loop
                    $diff_nr[($num - $nr)] = $num;
                    break;
                }
            }
         
        krsort($diff_nr);        // Sorts the array by key (difference) in reverse order
        return end($diff_nr);    // returns the last element (with the smallest difference - which results to be the closest)
    }
    
    /*
     * Converts note numbers (1,2,3,4) to keyboard numbers readable by human
     *
     * @param array $array Array to be manipulated
     *
     * return str formatted HTML
     */
    public function createResultHTML ($array) {
        
        # Set loop variables
        $readableArray = array ();
        $chordNumber = 0;
        $html = "";
        
        # Loop through arrays and create HTML
        foreach ($array as $keyboardIndexArray) {
            $chordNumber++;
            $html.="<br />\n";
            foreach ($keyboardIndexArray as $keyboardIndex) {
                $html .= $this->harmonyCatalog->keyboardLayoutWithNoteNumbers[$keyboardIndex] . ', ';
            }
        }
        return $html;       
    }    
}

?>