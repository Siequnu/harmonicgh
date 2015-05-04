HarmonicGH generates harmony in a neo-baroque style. This is my first project using PHP and Git.

Based on a seed chord a 32-chord harmonic sequence is generated with modulations (dominant, sub-mediant) and progressions. An audio file with the created harmony can be played in the browser.

index.php: Main form.
	Number of verses: Insert wanted number of 32-chord verses here.
	Chord prompt: Insert starting chord here. This will be the root of the generated harmony. Currently only works with C for major or c for minor.
	Radio-buttons: Choose to generate a list of chords or to generate and play 4-part harmony in browser.


/classes/
chordCatalog.class.php: Contains dataset of chord sequences and progressions.

chordGenerator.class.php: Contains functions to generate chord sequences.

harmonyCatalog.class.php: Stores data regarding 4-part harmonic structure of chords and progressions.

harmonyLogic.class.php: Contains functions to generate smart 4-part harmony.

midiGenerator.class.php: Contains functions to generate a MIDI file from an array of chords

lib/midi/midi.class.php: 3rd party class containing MIDI functions.

content/style.css: CSS sheet

# Deployment notes:
	MIDI generation requires an output folder in the main directory which is writeable by the webserver process.
	MIDI to WAV conversion requires timidity (eg. $ brew install timidity).

# Current issue:
	Rules to be added to harmony generation to improve voice leading	
	Modulation support to be extended to all tonalities.