HarmonicGH generates harmony in a neo-baroque style.

Based on a seed chord a 32-chord harmonic sequence is generated with modulations (dominant, sub-mediant) and progressions.

harmonic.php: Main form.
	Number of verses: Insert wanted number of 32-chord verses here. Defaults to 2.
	Chord prompt: Insert starting chord here. This will be the root of the generated harmony. Currently only working with ‘C’ or ‘c’ (Major or minor).
	Radio-buttons: Choose to generate a list of chords or to generate 4-part harmony.


chordCatalog.class.php: Contains dataset of chord sequences and progressions.

chordGenerator.class.php: Contains functions to generate chord sequences.

harmonyCatalog.class.php: Stores data regarding 4-part harmonic structure of chords and progressions.

harmonyLogic.class.php: Contains functions to generate smart 4-part harmony.


# Current issues: 
	Rules to be added to harmony generation to avoid parallel 5ths and 8ths. 
	Modulation support to be extended to all tonalities.
	MIDI generation to be added.
