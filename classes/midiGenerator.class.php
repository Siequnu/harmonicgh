<?php

class midiGenerator {
    
	private $errorMessage = '';

	public function __construct () {
		# Do nothing
	}
	
	public function getErrorMessage () {return $this->errorMessage;}
	
    public function generateMIDIHarmony ($array) {
	
		# Transpose everything up 2 octaves
		foreach ($array as &$chord) {
			foreach ($chord as &$note) {
				$note = $note + 24;
			}
		}
		# Add Midi Header
		$midiInstructions = array ();
		$midiInstructions[] = 'MFile 0 1 500';
		$midiInstructions[] = 'MTrk';
		#$midiInstructions[] = '0 TimeSig 4/4 24 8';
		#$midiInstructions[] = '0 Tempo 750000';
		$midiInstructions[] = '0 PrCh ch=1 p=53';
		
		# Add main track with chords
		$midiTimeStamp = 1000;
		
		foreach ($array as &$chord) { // Open each chord array which contains 4 notes
			
			foreach ($chord as &$note) { //Add each note to an array, when 4 notes are in array, print out On and Off MIDI info
				$noteArray [] = $note;
			}
			
			# Print On message for 4 notes 
			foreach ($noteArray as $noteInNoteArray) {
				$midiInstructions[] = "$midiTimeStamp On ch=1 n=$noteInNoteArray v=60";
			}
			
			# Advance timestamp         
			$midiTimeStamp = $midiTimeStamp + 1000;
			
			# Print Off message for same notes, time stamp ready for next set of On.        
			foreach ($noteArray as $noteInNoteArray) {
				$midiInstructions[] = "$midiTimeStamp Off ch=1 n=$noteInNoteArray v=60";   
			}
			unset ($noteArray);
		}
		
		# Add Midi Footer
		$midiInstructions[] = "$midiTimeStamp Meta TrkEnd";
		$midiInstructions[] = 'TrkEnd';
		
		$midiText = implode ("\n", $midiInstructions);
		
		# Determine the file name or end
		if (!$file = $this->createFileName ()) {return false;}
		
		# Send MIDI to MIDI conversion class
		require_once './lib/midi/midi.class.php';
		$midi = new Midi();
		$midi->importTxt ($midiText);
		$midi->saveMidFile ($file);
		
		# Signal success
		return $file;
	}

	
	private function createFileName () {
		
		# Check if directory is writable
		$directory  = dirname ($_SERVER['SCRIPT_FILENAME']) . '/output/';
		if (!is_writable ($directory)) {
			$this->errorMessage = 'Could not write to output directory.';
			return false;
		}
		
		# Create a unique filename
		$originalUmask = umask (0000);
		$file = tempnam ($directory, 'midi');
		umask ($originalUmask);
		rename ($file, $file . '.midi');
		chmod ($file . '.midi', 0770);
		return $file . '.midi';
	}
	
}

?>