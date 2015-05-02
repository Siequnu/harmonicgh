<?php

class midiGenerator {
    
    public function generateMIDIHarmony ($array) {
    
    # Transpose everything up 1 octaves
    foreach ($array as &$chord) {
        foreach ($chord as &$note) {
            $note = $note + 24;
        }
    }

        
    # Add Midi Header
    $html = 'MFile 0 1 1000
MTrk
0 TimeSig 4/4 24 8
0 Tempo 750000
0 PrCh ch=1 p=41'; 
    
    # Add main track with chords      
    $midiTimeStamp = 1000;
    
    foreach ($array as &$chord) { // Open each chord array which contains 4 notes
        
        foreach ($chord as &$note) { //Add each note to an array, when 4 notes are in array, print out On and Off MIDI info
            $noteArray [] = $note;
        }
        
        # Print On message for 4 notes 
        foreach ($noteArray as $noteInNoteArray) {
            $html .= "
$midiTimeStamp On ch=1 n=$noteInNoteArray v=60";
        }
        
        # Advance timestamp         
        $midiTimeStamp = $midiTimeStamp + 1000;
        
        # Print Off message for same notes, time stamp ready for next set of On.        
        foreach ($noteArray as $noteInNoteArray) {
            $html .= "
$midiTimeStamp Off ch=1 n=$noteInNoteArray v=60";   
        }
        unset ($noteArray);
    }
    
    # Add Midi Footer
    $html .= "
$midiTimeStamp Meta TrkEnd
TrkEnd";
	
    $file = $this->createFileStructure();
	
    include './lib/midi/midi.class.php';
    $midi = new Midi();
	$midi->importTxt($html);
	$midi->saveMidFile($file);
    
    echo "File generated";
    }

	
	private function createFileStructure () {
		# This is 3rd party code from the MIDI library by Valentino
		# TODO replace with my own routine
		session_start();
		$save_dir = 'tmp/sess_'.session_id().'/';
		
		//clean up, remove files belonging to expired session
		$sessions = array();
		$handle = opendir (session_save_path());
		while (false !== ($file = readdir ($handle)))
			if ($file!='.' && $file!='..') $sessions[] = $file;
		closedir($handle);
		$handle = opendir('tmp/');
		while (false !== ($dir = readdir ($handle)))
			if (is_dir($dir) && $dir!='.' && $dir!='..' && !in_array($dir,$sessions)) rm("tmp/$dir/");
		closedir($handle);
		
		// removes non-empty dir
		function rm($dir){
			$handle = opendir($dir);
			while (false !== ($file = readdir ($handle)))
				if ($file!='.' && $file!='..') unlink("$dir/$file");
			closedir($handle);
			rmdir($dir);
		}
		
		if (!is_dir('tmp')) mkdir('tmp');
		if (!is_dir($save_dir)) mkdir($save_dir);	
		srand((double)microtime()*1000000);
		$file = $save_dir.rand().'.mid';
		return $file;
	}
	
	
}

?>