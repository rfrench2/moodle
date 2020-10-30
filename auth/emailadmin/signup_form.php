// SWTC<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * New user account invitation form.
 *
 * @package    auth
 * @subpackage emailadmin
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * SWTC history:
 *
 * 10/30/20 - Initial writing.
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');

// class auth_emailadmin_invitation_form extends moodleform {       // Not named correctly to override the main login_signup_form.
// 08/08/18 - RF - trying to change name again...
// class login_signup_form extends moodleform {     // SWTC - works!
// 01/08/19 - Changed from invitation_form to signup_form.
class signup_form extends moodleform {
    function definition() {
        global $USER, $CFG;

        $mform = $this->_form;

        // SWTC ********************************************************************************
        // Change section header (since password is no longer required).
        // Remove all fields except email and email2 since the user account will not be created at this time.
        // $mform->addElement('header', 'createuserandpass', get_string('createuserandpass'), '');          // SWTC
        // SWTC ********************************************************************************
         $mform->addElement('header', 'createuser', get_string('newuserinvitation', 'local_swtc'), '');       // SWTC

        $mform->addElement('static', 'newuserinvitation1', '', get_string('newuserinvitation_desc1a', 'local_swtc'));     // SWTC
        $mform->addElement('static', 'newuserinvitation2', '', get_string('newuserinvitation_desc2a', 'local_swtc'));     // SWTC
        $mform->addElement('static', 'newuserinvitation2', '', get_string('newuserinvitation_desc3a', 'local_swtc'));     // SWTC

        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
        $mform->setType('email', core_user::get_property_type('email'));
        $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('email');

        $mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
        $mform->setType('email2', core_user::get_property_type('email'));
        $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('email2');

        if (signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }

        // Add "Agree to sitepolicy" controls. By default it is a link to the policy text and a checkbox but
        // it can be implemented differently in custom sitepolicy handlers.
        $manager = new \core_privacy\local\sitepolicy\manager();
        $manager->signup_form($mform);

        // buttons
        // $this->add_action_buttons(true, get_string('createaccount'));        // SWTC
        $this->add_action_buttons(true, get_string('requestnewuserinvitation', 'local_swtc'));           // SWTC

    }

    function definition_after_data(){
        $mform = $this->_form;
        // $mform->applyFilter('username', 'trim');         // SWTC

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
    }

    /**
     * Validate user supplied data on the signup form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     *
     * SWTC history:
     *
     * 04/02/19 - Added this header; before validation, lower case the email address (Moodle requires it and the email
     *                      domain checks will not work).
     *
     */
    public function validation($data, $files) {
        global $CFG, $DB;             // SWTC
        $errors = parent::validation($data, $files);

        if (signup_captcha_enabled()) {
            $recaptchaelement = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptchaelement->verify($response)) {
                    $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }

        // $errors += signup_validate_data($data, $files);              // SWTC

        $data['email'] = \core_text::strtolower($data['email']);        // 04/02/19
        $data['email2'] = \core_text::strtolower($data['email2']);        // 04/02/19

        if (! validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');

        } else if ($DB->record_exists('user', array('email' => $data['email']))) {
            $errors['email'] = get_string('emailexists') . ' ' .
                    get_string('emailexistssignuphint', 'moodle',
                            html_writer::link(new moodle_url('/login/forgot_password.php'), get_string('emailexistshintlink')));
        }
        if (empty($data['email2'])) {
            $errors['email2'] = get_string('missingemail');

        } else if ($data['email2'] != $data['email']) {
            $errors['email2'] = get_string('invalidemail');
        }
        if (!isset($errors['email'])) {
            if ($err = email_is_not_allowed($data['email'])) {
                $errors['email'] = $err;
            }
        }

        return $errors;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        // $data->signupurl = $this->signupurl->out(false);
        // $this->signupurl = new moodle_url('/login/signup.php');
        $context = [
            'formhtml' => $formhtml
        ];
        return $context;
    }

}
