<?php
/**
 * Command line script to generate a dummy file of a specific byte size
 *
 * @link https://github.com/jmillerdesign/Dummy-File-Creator
 * @author J. Miller <j@jmillerdesign.com>
 */
class DummyFileCreator {
/**
 * Error messages
 *
 * @var array
 */
	protected $_errors = array();

/**
 * Constructor
 *
 * @param array $args Arguments passed in via CLI
 *                    $args[0] is the path to this script
 *                    $args[1] is the path to the file that will be created
 *                    $args[2] is the file size to generate (including suffix, e.g. 10MiB)
 */
	public function __construct($args) {
		$this->scriptPath = isset($args[0]) ? $args[0] : null;
		$this->exportPath = isset($args[1]) ? $args[1] : null;
		$this->exportSize = isset($args[2]) ? $this->strToBytes($args[2]) : null;

		if (!$this->exportPath) {
			$this->_errors[] = 'Missing argument: export path';
		}

		if (!$this->exportSize) {
			if (isset($args[2])) {
				$this->_errors[] = 'Invalid export size: ' . $args[2];
			} else {
				$this->_errors[] = 'Missing argument: export size';
			}
		}
	}

/**
 * Create dummy file
 *
 * @return boolean True if file created successfully
 */
	public function createFile() {
		if ($this->_errors) {
			return false;
		}

		if ($fh = fopen($this->exportPath, 'w')) {
			fwrite($fh, str_repeat('0', $this->exportSize), $this->exportSize);
			fclose($fh);
		}

		return (filesize($this->exportPath) == $this->exportSize);
	}

/**
 * Wrap a string in a color code for command-line output
 * @param string $color Color key
 * @param string $str String to wrap in color
 * @return string Colorized string
 */
	public function color($color, $str) {
		switch ($color) {
			case 'red':   $colorCode = '0;31'; break;
			case 'green': $colorCode = '0;32'; break;
			case 'brown': $colorCode = '0;33'; break;
		}
		return "\033[" . $colorCode . "m$str\033[0m";
	}

/**
 * Display error message
 *
 * @return void
 */
	public function displayErrors() {
		echo $this->color('red', implode("\n", $this->_errors)) . "\n";
	}

/**
 * Display success message
 *
 * @return void
 */
	public function displaySuccess() {
		echo $this->color('green', 'Success: ') .
			$this->exportPath . ' (' . $this->color('brown', $this->bytesToStr($this->exportSize)) . ")\n";
	}

/**
 * Convert a string to bytes
 *
 * @param string $size File size (example: 10MB)
 * @return integer Bytes
 */
	public function strToBytes($sizeStr) {
		$suffix = strtolower(preg_replace("/[^a-zA-Z]/", '', $sizeStr));
		$size = (float) preg_replace("/[^0-9.]/", '', $sizeStr);

		if ($size < 1) {
			return 0;
		}

		switch ($suffix) {
			case 'b':   return round($size);
			case 'kb':  return round($size * 1000);
			case 'kib': return round($size * 1024);
			case 'mb':  return round($size * 1000 * 1000);
			case 'mib': return round($size * 1024 * 1024);
			case 'gb':  return round($size * 1000 * 1000 * 1000);
			case 'gib': return round($size * 1024 * 1024 * 1024);
			default:    return 0;
		}
	}

/**
 * Convert bytes to a human-readable string
 *
 * @param integer $bytes Bytes
 * @return string Size (example: 10MB)
 */
	public function bytesToStr($bytes) {
		return sprintf('%s bytes', $this->round($bytes));
	}

/**
 * If a number is an integer, use no precision,
 * otherwise, round to a precision of 1-2 decimal places
 *
 * @param float $num Number
 * @return string Number
 */
	public function round($num) {
		return (intval($num) == $num) ? number_format($num, 0) : rtrim(number_format($num, 2), '0');
	}
}

$dummyFileCreator = new DummyFileCreator($argv);
if ($dummyFileCreator->createFile()) {
	$dummyFileCreator->displaySuccess();
} else {
	$dummyFileCreator->displayErrors();
}
