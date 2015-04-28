HarmonicGH generates harmony in a neo-baroque style.

harmonic.php: Main form
	Number of verses: Insert wanted number of 32-chord verses here. Defaults to 2.

	Chord prompt: Insert starting chord here. This will be the root of the generated harmony. Currently only working with “C” or “c” (major or minor).
	Radio-buttons: Choose to generate a list of chords or to generate 4-part harmony.


chordCatalog.class.php: Stores data of chord sequences

chordGenerator.class.php: Contains functions to generate chord sequences

harmonyCatalog.class.php: Stores data regarding harmonic structure of chords and progressions

harmonyLogic.class.php: Contains functions to generate smart 4-part harmony.