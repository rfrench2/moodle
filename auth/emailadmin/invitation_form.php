<?php
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
 * @package    local
 * @subpackage ebglms
 * @copyright  2018 Lenovo EBG Services Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *	
 * Lenovo history:
 *
 *	08/02/18 - Initial writing.
 * 01/08/19 - Switching references from "signup" to "invitation" (this is still part 2 of 2).
 * PTR2019Q403 - @01 - 03/26/20 - Add check for '@lenovo' in email address. If not true, hide PremierSupport checkbox.
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');

// 01/08/19 - Changed from signup_form to invitation_form.
class invitation_form extends moodleform {
    function definition() {
        global $USER, $CFG;

        $mform = $this->_form;
        
        // Lenovo *******************************************************************************
        // Add hidden element to hold the users token (passed by a set_data call in signup.php).
        // Lenovo *******************************************************************************
        $mform->addElement('hidden', 'token', '');
        $mform->setType('token', PARAM_RAW);
        
        $mform->addElement('header', 'createuserandpass', get_string('createuserandpass'), '');

        $mform->addElement('text', 'username', get_string('username'), 'maxlength="100" size="12" autocapitalize="none"');
        $mform->setType('username', PARAM_RAW);
        $mform->addRule('username', get_string('missingusername'), 'required', null, 'client');

        if (!empty($CFG->passwordpolicy)){
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }
        $mform->addElement('password', 'password', get_string('password'), 'maxlength="32" size="12"');
        $mform->setType('password', core_user::get_property_type('password'));
        $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');

        $mform->addElement('header', 'supplyinfo', get_string('supplyinfo'),'');

        // Lenovo - Set email field to read-only.
        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25" readonly="readonly"');
        $mform->setDefault('email', $this->_customdata['email']);
        $mform->setType('email', core_user::get_property_type('email'));

        // Lenovo - Set email2 field to read-only.
        $mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25" readonly="readonly"');
        $mform->setDefault('email2', $this->_customdata['email2']);
        $mform->setType('email2', core_user::get_property_type('email'));
        
        // @01 - Add check for '@lenovo' in email address. If not true, hide PremierSupport checkbox.
        $mform->addElement('advcheckbox', 'premiersupportuser', get_string('premiersupportuser', 'auth_emailadmin'));
        $mform->setDefault('premiersupportuser', 0);
        $mform->addHelpButton('premiersupportuser', 'premiersupportuser', 'auth_emailadmin');
        
        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
        }

        $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="20"');
        $mform->setType('city', core_user::get_property_type('city'));
        if (!empty($CFG->defaultcity)) {
            $mform->setDefault('city', $CFG->defaultcity);
        }

        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country = array_merge($default_country, $country);
        $mform->addElement('select', 'country', get_string('country'), $country);

        if( !empty($CFG->country) ){
            $mform->setDefault('country', $CFG->country);
        }else{
            $mform->setDefault('country', '');
        }

        profile_signup_fields($mform);
        
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
        $this->add_action_buttons(true, get_string('createaccount'));        // Lenovo

    }

    /** tweak the form - depending on existing data
     *	
     * Lenovo history:
     *
     * @01 - 03/26/20 - Added this header; add check for '@lenovo' in email address. If not true, hide PremierSupport checkbox.
     *
     */ 
    function definition_after_data() {
        parent::definition_after_data();
        
        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');         // Lenovo

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
        
        // @01 - Add check for '@lenovo' in email address. If not true, hide PremierSupport checkbox.
        if (stripos($mform->getElementValue('email'), '@lenovo') === false) {
            $mform->removeElement('premiersupportuser');
        }
    }

    /**
     * Validate user supplied data on the signup form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        global $CFG, $DB;             // Lenovo
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
        
        $errors += signup_validate_data($data, $files);

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
