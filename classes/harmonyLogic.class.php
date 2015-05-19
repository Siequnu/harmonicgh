<?php

class harmonyLogic {
    
    public function __construct() {
        require_once './classes/harmonyCatalog.class.php';
        require_once './classes/midiGenerator.class.php';        
    }
    
    public function getHarmony ($sequenceOfChords) { // Array ( [0] => C [1] => F [2] => G [3] => C .....)
        $this->harmonyCatalog = new harmonyCatalog;
        $this->sequenceOfChords = $sequenceOfChords;  
        $this->sequenceOfHarmony = array ();
        
        # Get array with all note positions
        $this->getAllNotePositions ($this->sequenceOfChords);
        
        # Make result array readable by Human
        #echo $this->createResultHTML ($this->sequenceOfHarmony);
        # Send array to MIDI Generator
        $this->midiGenerator = new midiGenerator;
        $file = $this->midiGenerator->generateMIDIHarmony ($this->sequenceOfHarmony);
        # Deal with result messages
        if (!$file) {
            echo "\n<p>The MIDI file could not be created, due to the following error: <pre>".htmlspecialchars($this->midiGenerator->getErrorMessage())."</pre></p>";
            return false;
        } 
        
        # Convert MIDI file to WAV
        $this->convertMIDIToWAV ($file);
        
        # Echo HTML5 tag with converted WAV file
        $location = '/harmonic/output/' . pathinfo ($file, PATHINFO_FILENAME) . '.wav';
        echo $this->getAudioHTMLTag ($location);
        
    }
    
    
    public function getAllNotePositions ($sequenceOfChords) {
        
        # Insert first chord positions into harmony array
        $this->sequenceOfHarmony[0] = $this->getPositionOfFirstChord ($sequenceOfChords[0]);
        
        # Go through the array of chords and generate keyboard positions for each chord
        $numberOfChordsToHarmonize = (count($sequenceOfChords)-1);
        $chordInSequenceOfChords = 1;
        $chordInSequenceOfHarmony = 0;
        
        for ($cycles = 1; $cycles <= $numberOfChordsToHarmonize; $cycles++) {
            
            # Generate 4 part position for chord
            $harmonizedChord = $this->harmonizeNextChord($chordInSequenceOfChords, $chordInSequenceOfHarmony);
            
            $chordInSequenceOfHarmony++;
            $chordInSequenceOfChords++;
            
            # Merge to existing array of Harmony
            foreach ($harmonizedChord as $notePosition) {
                $this->sequenceOfHarmony[$chordInSequenceOfHarmony][] = $notePosition;    
            }           
        }
        return true;
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
        
        # Check if chord is a repeat of last chord. If so, change position
       
        # Check if array has duplicate items
        while (count(array_unique($matchArray))<count($matchArray)) // Array has duplicates
        {
            # Return an array of duplicate items
            $arrayWithDuplicates = array_unique (array_diff_assoc ($matchArray, array_unique ($matchArray) ) );
            foreach ($arrayWithDuplicates as $key => $index) {
                # Action to do with duplicate keys
                # Get closest match from possibility soup for duplicate key
                $matchArray[$key] = $this->getClosestMatch ($possibilitySoup, $key);
            }  
        }
        # Array ( [0] => 14 [1] => 29 [2] => 33 [3] => 36 )
        # Check that at least one of each chord note is in the array
        
        
        # Sort array in ascending order
        #sort($matchArray);
        
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
        $chordPositionArray[0] = $possibleKeys[0][1];  // = $possibleKeys[bassNoteInChord][matchingNoteInSecondOctave]
        
        # Place all other notes consecutively on third octave or above
        $octave = 2;
        $lastLocationOnArray = 0;
        for ($noteInChord = 1; $noteInChord <= 3; $noteInChord++) {
            $chordPositionArray[$noteInChord] = $possibleKeys[$noteInChord][$octave];
            
            # Check if chord has been placed below last chord
            if ($chordPositionArray[$noteInChord] < $lastLocationOnArray) {
                
                # Add it up an octave
                $chordPositionArray[$noteInChord] = $possibleKeys[$noteInChord][$octave + 1];
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
     * Converts a MIDI file to WAV.
     *
     * @param str $file Filepath of MIDI file to be converted
     *
     * @return bool True if operation succeded, False if error occured.
     */ 
    public function convertMIDIToWAV ($file) {
        # Convert MIDI file to WAV using timidity in shell
        $cmd = "timidity -Ow \"{$file}\"";   
        $exitStatus = $this->execCmd($cmd);
        if ($exitStatus != 0) {
            #echo nl2br (htmlspecialchars (implode ("\n", $output)));
            echo "\n<p><pre>The WAV file could not be created, due to an error with the converter.</pre></p>";  
            return false;
        }
        return true;
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
    
    
    /*
     * Generate HTML5 audio tag for audio at a given location
     *
     * @param str $location Filename (within running directory) of the audiofile
     *
     * @return str HTML code
     */ 
    public function getAudioHTMLTag ($location) {
        
        $html = "<audio src=\"{$location}\" controls=\"controls\">
                Your browser does not support the AUDIO element
                </audio>";    
        return $html;
    }
    
     /*
	 * Executes a command and returns an exit status
	 *
	 * @param str $cmd The command
	 *
	 * @return bool The exit status
	 */
	private function execCmd ($cmd) {
		if (substr(php_uname(), 0, 5) == "Linux"){ 
			exec ($cmd, $output, $exitStatus);
		} else { 
        $cmd = '/usr/local/bin/' . $cmd;
        exec ($cmd, $output, $exitStatus);   
		}		
		return $exitStatus;
	}
    
}

?>