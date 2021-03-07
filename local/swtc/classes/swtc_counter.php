<?php
/**
 * Version details
 *
 * @package    local
 * @subpackage swtc/classes/counter.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 02/26/21 - Initial writing.
 *
 **/

namespace local_swtc;

defined('MOODLE_INTERNAL') || die();

// use local_swtc\swtc_debug;

/**
 * Debug class that creates, increments, and shows the value of a counter. To use:
 *
 * 		use local_swtc\swtc_counter;
 *			OR
 *			use swtc_counter;
 *
 *			$counter = new swtc_ounter;
 *			$counter->incrementValue();
 *			print $counter->getValue();
 *
 *
 *
 * @package    local
 * @subpackage swtc/classes/swtc_counter.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 02/26/21 - Initial writing.
 *
 **/
class swtc_counter {
	protected $counter;

	public function __construct() {
		$this->counter = 0;
	}

	public function incrementValue() {
		$this->counter++;
	}

	public function getValue() {
		return $this->counter;
	}

}
